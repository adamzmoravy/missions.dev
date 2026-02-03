<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Ramsey\Uuid\Uuid;

$agitator = $app['controllers_factory'];




$agitator->post('/comments/{commentId}/upvote/', function ($commentId, Request $request) use ($app) {


    $currentUser = $app['currentUser'];
    $repository = new \Karolina\Discussion\DiscussionRepository();
    $comment = $repository->getComment($commentId);

    $comment->addUpvote($currentUser);

    $repository->saveComment($comment);
    $response['status'] = "success";
    return $app->json($response);

});


$agitator->post('/comments/{commentId}/reaction/', function ($commentId, Request $request) use ($app) {

    $currentUser = $app['currentUser'];
    $reaction = $request->request->get('reaction');

    $repository = new \Karolina\Discussion\DiscussionRepository();
    $comment = $repository->getComment($commentId);

    $comment->addReaction($reaction, $currentUser->getId());

    $repository->saveComment($comment);
    $response['status'] = "success";
    return $app->json($response);

})->before($authRequired);

$agitator->delete('/comments/{commentId}/reaction/', function ($commentId, Request $request) use ($app) {

    $currentUser = $app['currentUser'];
    $reaction = $request->request->get('reaction');

    $repository = new \Karolina\Discussion\DiscussionRepository();
    $comment = $repository->getComment($commentId);

    $comment->removeReaction($currentUser->getId());

    $repository->saveComment($comment);
    $response['status'] = "success";
    return $app->json($response);

})->before($authRequired);


$agitator->delete('/comments/{commentId}/upvote/', function ($commentId, Request $request) use ($app) {


    $currentUser = $app['currentUser'];
    $repository = new \Karolina\Discussion\DiscussionRepository();
    $comment = $repository->getComment($commentId);

    $comment->removeUpvote($currentUser);

    $repository->saveComment($comment);
    $response['status'] = "success";
    return $app->json($response);

});



$agitator->delete('/comments/{commentId}/', function ($commentId, Request $request) use ($app) {


    $currentUser = $app['currentUser'];
    $repository = new \Karolina\Discussion\DiscussionRepository();
    $comment = $repository->getComment($commentId);

    $comment->setActive(false);

    if ($currentUser->getId() != $comment->getUserId() and !$currentUser->isAdmin()) {

        throw new  \Karolina\Exception("You are not authorized to delete this comment", 'not_authorized');

    }

    $repository->saveComment($comment);
    $response['status'] = "success";
    return $app->json($response);

});


$agitator->post('/experiments/{experimentId}/comment/{commentId}/reply/', function ($experimentId, $commentId, Request $request) use ($app) {

    $currentUser = $app['currentUser'];

    if (!$currentUser) {
        throw new  \Karolina\Exception("Must be logged in to comment", 'not_logged_in');
    }

    $experimentRepo = new Karolina\Experiment\ExperimentRepository();
    $experiment = $experimentRepo->getById($experimentId);

    $value = $request->request->get('value');

    $repository = new \Karolina\Discussion\DiscussionRepository();
    $topic = $repository->getTopic($experiment->getTopicId());
    $format = $request->request->get('format');
    $reply = $topic->addNewReply($value, $currentUser, 'reply', $commentId, $format);
    $repository->saveComment($reply);
    $experimentRepo->save($experiment);
    $originalComment = $repository->getComment($commentId);
    $userRepo = new \Karolina\User\UserRepository();
    $originalCommenter = $userRepo->getById($originalComment->getUserId());

    $exclude = [$currentUser->getId()];
    $conversationParticipants = $topic->getCommentConversationParticipantsIds($commentId, $exclude);

    /* @var $notification \Karolina\Notification */
    $notification = $app['notificationService'];

    $data['topic_title'] = $experiment->getTitle();
    $data['body'] = $reply->getHTMLValue($app['platform']->getPlatformFrontendUrl());
    $data['commenter']['name'] = $currentUser->getFirstName().' '.$currentUser->getLastName();
    $data['original_body'] = $originalComment->getHTMLValue($frontEndUrl);
    $data['original_commenter']['name'] = $originalCommenter->getName();
    $data['experiment_id'] = $experiment->getId();
    $data['base_url'] = $app['platform']->getPlatformFrontendUrl();
    $data['commenter']['image'] = $app['platform']->getImgStorageUrl().'/'.$currentUser->getProfileImage();
    $data['commenter']['user_id'] = $currentUser->getId();

    $notification->setSubject("Reply from ".$currentUser->getFirstName()." on ".$experiment->getTitle())
        ->setBodyFromTemplate('comment_reply', $data)
        ->setToUserIds($conversationParticipants)
        ->addToEmailQueue();

    $mentionedUserIds = $comment->getMentionedUserIds();
    if (count($mentionedUserIds)) {
        $notification->setSubject($currentUser->getFirstName().' mentioned you in a reply on '.$experiment->getTitle())
            ->setToUserIds($mentionedUserIds)
            ->addToEmailQueue();    
    }

    // Add event
    Karolina\Event\Publisher::instance()->publish(new Karolina\Discussion\CommentEvent($reply, 'comment_reply'));

    // Update so that new comments are counted
    $experimentRepo->update($experimentId);

    $response['status'] = "success";

    return $app->json($response);

});


$agitator->post('/experiments/{experimentId}/topic/', function ($experimentId, Request $request) use ($app) {

    $experimentRepo = new Karolina\Experiment\ExperimentRepository();
    $experiment = $experimentRepo->getById($experimentId);
    $experiment->setLastCommentTime(time());

    $currentUser = $app['currentUser'];

    if (!$currentUser) {
        throw new  \Karolina\Exception("Must be logged in to comment", 'not_logged_in');
    }

    $action = new Karolina\Discussion\CommentToTopicAction($experiment->getTopicId(),  $app['platform']);

    $action->createCommentFromRequest($request, $currentUser);

    $submissionsPreviews = $action->getSubmissionsFromComment($action->getComment()->getSubmissions());

    $comment = $action->saveComment();
    $type = $request->request->get('type');

    $addittionalData['experiment_id'] = $experiment->getId();
    $action->publishEvent('comment_submission', $addittionalData);


    /* Send e-mail */
    $topic = new \Karolina\Discussion\Topic($experiment->getTopicId());

    // Get commenters
    $commentersIds = $topic->getTopicParticipantsIds();
    $usersWhoShouldGetEmailUpdates = $experiment->usersWhoShouldGetEmailUpdates();

    // Merge all ids to one array and remove duplicates
    $sendEmailUpdate = array_merge($commentersIds, $usersWhoShouldGetEmailUpdates);
    $sendEmailUpdate = array_unique($sendEmailUpdate);


    /* @var $notification \Karolina\Notification */
    $notification = $app['notificationService'];
    $frontEndUrl = $app['platform']->getPlatformFrontendUrl();
    $data['topic_title'] = $experiment->getTitle();
    $data['type'] = $type;
    $data['body'] = $comment->getHTMLValue($frontEndUrl);
    $data['commenter']['name'] = $currentUser->getFirstName().' '.$currentUser->getLastName();
    $data['base_url'] = $frontEndUrl;
    $data['experiment_id'] = $experimentId;
    $data['commenter']['image'] = $app['platform']->getImgStorageUrl().'/'.$currentUser->getProfileImage();
    $data['commenter']['user_id'] = $currentUser->getId();

    $data['submissions'] = false;
    if (count($submissionsPreviews)) {
        $data['submissions'] = $submissionsPreviews;
    } 



    $notification
        ->setSubject('Feedback on '.$experiment->getTitle().' from '.$data['commenter']['name'])
        ->setBodyFromTemplate('comment', $data)
        ->setToUserIds($sendEmailUpdate)
        ->addToEmailQueue();

    $mentionedUserIds = $comment->getMentionedUserIds();
    if (count($mentionedUserIds)) {
        $notification->setSubject($currentUser->getFirstName().' mentioned you in '.$experiment->getTitle())
            ->setToUserIds($mentionedUserIds)
            ->addToEmailQueue();    
    }        


    $response['status'] = "success";
    $response['comment']['comment_id'] = $comment->getId();
    return $app->json($response);

});


$agitator->get('/experiments/{experimentId}/topic/', function ($experimentId, Request $request) use ($app) {

    $limit = $request->query->get('limit');
    $offset = $request->query->get('offset');

    $repository = new \Karolina\Discussion\DiscussionRepository();

    $interactor = $app['experimentInteractor'];
    $experiment = $interactor->setExperimentById($experimentId)->getExperiment();

    $topicId = $experiment->getTopicId();

    $topic = $repository->getTopic($topicId, $limit, $offset);
    $imgStorageUrl = $app['platform']->getImgStorageUrl();


    $response['comments'] = (new Karolina\API\v1\CommentsResponse($topic->getComments(), $imgStorageUrl))->get();
    $response['status'] = "success";

    return $app->json($response);

});


return $agitator;
