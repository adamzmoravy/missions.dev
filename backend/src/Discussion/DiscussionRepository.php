<?php

namespace Karolina\Discussion;

use Karolina\PushToFront;
use Karolina\User\User;
use Karolina\User\UserRepository;

Class DiscussionRepository {

    public function getTopic ($topicId, $limit = 99999, $offset = null, $commentId = null) {

        $topic = new Topic($topicId);

        $comments = $this->getLatestComments($topicId, $limit, $offset, $commentId);
        $topic->injectComments($comments);

        return $topic;

    }

    public function saveComment (Comment $comment) {


        $model = \Karolina\Database\Table\Comment::firstOrCreate(['comment_id' => $comment->getId()]);

        $model->topic_id = $comment->getTopicId();


        if ($comment->getFormat() == 'tiptap-json') {

            $model->value_json = $comment->getValue();

        } else {

            $model->value = $comment->getValue();

        }


        $model->format = $comment->getFormat();
        $model->topic_id = $comment->getTopicId();
        $model->active = $comment->getActive();
        $model->user_id = $comment->getUserId();
        $model->type = $comment->getType();
        $model->posted_time = $comment->getPostedTime();
        $model->parent_id = $comment->getParentId();
        $model->platform = getenv('platform_key');
        $model->images = json_encode($comment->getImages());
        $model->reactions = json_encode($comment->getReactions());
        $model->submissions = json_encode($comment->getSubmissions());

    /*
        if ($comment->getNetwork()) {
            $this->saveNetwork($comment->getNetwork());
        }
    */

        $model->save();

        $push = new PushToFront();
        $push->push('new-comment', ['topic' => $comment->getTopicId()], 'comments'.'-'.$comment->getTopicId());


        if ($comment->getParentId() != null) {

            $changedComment = $comment->getParentId();

        } else {
                
            $changedComment = $comment->getId();
        }

        $push->push('comment-changed', ['topic' => $comment->getTopicId(), 'comment_id' => $changedComment], 'comments'.'-'.$comment->getTopicId());
//        $push->push('comment-changed', ['topic' => $comment->getTopicId(), 'comment_id' => $changedComment]);

        return $comment;

    }

    public function countComments ($topicId) {

        $count = \Karolina\Database\Table\Comment::where('topic_id', $topicId)->count();
        return $count;
    }


    private function modelsToComments ($models) {

//        $userRepo = new UserRepository();

        $comments = [];

        foreach ($models as $model) {

            $newComment = new Comment($model);

            // Inject replies into comment
            foreach ($models as $reply) {

                if ($model->comment_id == $reply->parent_id) {

                    $newComment->addReply(new Comment($reply));
                }
            }

//            $newComment->setNetwork($this->getNetwork($newComment->getId()));

            $comments[] = $newComment;
        }

        return $comments;

    }

    public function getCommentsByUserId ($userId) {


        $models = \Karolina\Database\Table\Comment::where('user_id', $userId)->get();

        return $this->modelsToComments($models);

    }

    public function getComments ($topicId) {

        $models = \Karolina\Database\Table\Comment::where('topic_id', $topicId)->get();

        return $this->modelsToComments($models);

    }

    public function getCommentsByTopics ($topicIds) {

        $models = \Karolina\Database\Table\Comment::whereIn('topic_id', $topicIds)->get();

        return $this->modelsToComments($models);

    }


    public function getLatestCommentsByTopics($topicIds, $take = 20, $offset = null, $commentId = null) {

        if ($commentId) {

            $models = \Karolina\Database\Table\Comment::whereIn('topic_id', $topicIds)
            ->where('parent_id', null)
            ->where('comment_id', $commentId)
            ->get();

        } else {

            $models = \Karolina\Database\Table\Comment::whereIn('topic_id', $topicIds)
            ->where('parent_id', null)
            ->take($take)
            ->orderBy('posted_time', 'desc');

            if ($offset) {
                $models = $models->skip($offset);
            }

            $models = $models->get();

        }

        $comments = array();

        foreach ($models as $model) {

            $comments[] = $this->modelToComment($model);
        }
        return $comments;

    }


    public function getLatestComments ($topicId, $take = 20, $offset = null, $commentId = null) {

        if ($commentId) {

            $models = \Karolina\Database\Table\Comment::where('topic_id', $topicId)
            ->where('parent_id', null)
            ->where('comment_id', $commentId)
            ->get();

        } else {

            $models = \Karolina\Database\Table\Comment::where('topic_id', $topicId)
            ->where('parent_id', null)
            ->take($take)
            ->orderBy('posted_time', 'desc');

            if ($offset) {
                $models = $models->skip($offset);
            }

            $models = $models->get();

        }

        $comments = array();

        foreach ($models as $model) {

            $comments[] = $this->modelToComment($model);
        }
        return $comments;

    }

    private function modelToComment ($model) {


        $comment = new Comment($model);

        if ($model->parent_id == null) {

            foreach ($model->replies as $reply) {

                $comment->addReply($this->modelToComment($reply));
    
            }

        }

        return $comment;

    }

    public function getComment ($commentId, $withReplies = false) {

        $model = \Karolina\Database\Table\Comment::where('comment_id', $commentId)->firstOrFail();
        $userRepo = new UserRepository();
        $user = $userRepo->getById($model->user_id);
        $model->user = $user;
        $comment = new Comment($model);


        if ($withReplies) {

            $replies = \Karolina\Database\Table\Comment::where('parent_id', $commentId)->get();
            foreach ($replies as $reply) {

                $comment->addReply($this->modelToComment($reply));

            }

        }
//        $comment->setNetwork($this->getNetwork($comment->getId()));


        return $comment;

    }


	public function getStats ($scope = 'local') {

        $model = new \Karolina\Database\Table\Comment();

	    if ($scope == 'global') {
            $model = $model->withoutGlobalScope(\Karolina\Database\PlatformScope::class);
            
        }

        $stats = array();

        $stats['total_comments'] = $model->count();
        $stats['total_topics'] = $model->distinct()->count('topic_id');

        return $stats;


    }


    public function getAll () {

        $models = \Karolina\Database\Table\Tag::all();

        $tags = array();
        foreach ($models as $model) {

            $tag = new Tag();
            $tag->setLabelLanguageFields(json_decode($model->label_language, true));
            $tag->setId($model->tag_id);
            $tags[] = $tag;

        }

        return $tags;

    }



    public function getNetwork ($commentId) {

        $networkRepository = new \Karolina\Network\NetworkRepository();
        $network = $networkRepository->getNetwork('comments', $commentId);
        return $network;

    }

    public function saveNetwork ($network) {

        $networkRepository = new \Karolina\Network\NetworkRepository();
        $networkRepository->saveNetwork($network);


    }

}