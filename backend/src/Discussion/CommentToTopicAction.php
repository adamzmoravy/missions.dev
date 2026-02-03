<?php

namespace Karolina\Discussion;

use Karolina\Experiment\ExperimentRepository;

class CommentToTopicAction
{

    private $topic;
    private $comment;
    private $repository;
    private $notification;
    private $platform;

    public function __construct($topicUUD, $platform)
    {

        $this->topic = new Topic($topicUUD);
        $this->repository = new DiscussionRepository();
        $this->platform = $platform;
    }

    public function getComment () {

        return $this->comment;

    }

    public function createCommentFromRequest ($request, $currentUser) {

        $type = $request->request->get('type');
        $value = $request->request->get('value');
        $subject = $request->request->get('subject');
        $format = $request->request->get('format');

        $submissions = null;

        if ($request->request->get('submissions')) {
            $submissions = json_encode($request->request->get('submissions'));
        }

        $this->comment = $this->topic->addNewComment($value, $currentUser, $type, false, $subject, $format, $submissions);

        // Todo: If and when we add some sort of ACL, we need to check if these images exist already
        if ($images = $request->request->get('images')) {
            
            $this->comment->addImages($images);
        }

    }

    public function getSubmissionsFromComment ($submissions) {

        $repo = new ExperimentRepository();

        $submissionsPreviews = [];

        foreach ($submissions as $submissionId) {
            
            $document = $repo->getDocument($submissionId);

            $submissionsPreviews[] = (new \Karolina\API\v1\ExperimentResponse($document, 'en', $this->platform))->getPreview(false);


        }

        return $submissionsPreviews;

    }

    public function saveComment () {

        return $this->repository->saveComment($this->comment);


    }

    public function createNotification ($notification, $frontEndUrl, $imgStorageUrl, $currentUser, $happening, $type, $subject, $includeCalendarInvitation, $calendarInvite, $submissionsPreviews = null) {

        $data['base_url'] = $frontEndUrl;
        $data['topic_title'] = $happening->getName();
        $data['subject'] = $subject;
        $data['type'] = $type;
        $data['body'] = $this->comment->getHTMLValue($frontEndUrl);

        $data['image'] = null;
        if ($this->comment->getImages()) {
            $data['image'] = $imgStorageUrl.'/'.$this->comment->getImages()[0]['filename'];
        }
        $data['images_num'] = count($this->comment->getImages());
        $data['commenter']['name'] = $currentUser->getFirstName().' '.$currentUser->getLastName();
        $data['happening_id'] = $happening->getId();
        $data['commenter']['image'] = $imgStorageUrl.'/'.$currentUser->getProfileImage();
        $data['commenter']['user_id'] = $currentUser->getId();
        $data['submissions'] = false;
        if ($submissionsPreviews) {
            $data['submissions'] = $submissionsPreviews;
        }

        if ($includeCalendarInvitation) {
            $notification = $notification->addCalendarEvent($happening->getName(), $subject, $this->comment->getPlainTextValue(), $calendarInvite['location'], $calendarInvite['start_date'].' '.$calendarInvite['start_time'], $calendarInvite['end_date'].' '.$calendarInvite['end_time']);
        }
        
        if ($subject) {
            $notification = $notification->setSubject($subject);
        } else {
            $notification = $notification->setSubject('Announcement for '.$happening->getName().' from '.$data['commenter']['name']);
        }

        $notification = $notification->setBodyFromTemplate('happening_comment', $data);

        return $notification;        

    }

    public function sendEmail ($happening, $notification) {

        $registered = $happening->getRegisteredUsers();
        $userIdArray = [];
        foreach ($registered as $id => $type) {
            $userIdArray[] = $id;
        }

        $notification
            ->setToUserIds($userIdArray)
            ->addToEmailQueue();

    }


    public function publishEvent ($eventName, $addittionalData) {

        \Karolina\Event\Publisher::instance()->publish(new CommentEvent($this->comment, $eventName, $addittionalData));

    }



}