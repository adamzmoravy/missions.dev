<?php

namespace Karolina\Discussion;

use Karolina\Event\Event;

Class CommentEvent implements Event

{

    private $comment;
    private $type;
    private $data;

    public function __construct (Comment $comment, $type = 'comment', $adittionalData = [])
    {

        $this->comment = $comment;
        $this->type = $type;
        $this->data = $adittionalData;

    }

    public function occurredOn()
    {
        return time();
    }

    public function getData()
    {

        $data = $this->data;
        $data['commenter']['user_id'] = $this->comment->getUserId();
        $data['comment_id'] = $this->comment->getId();
        $data['topic_id'] = $this->comment->getTopicId();
        $data['parent_id'] = $this->comment->getParentId();

        return $data;

    }

    public function getType()
    {

        return $this->type;

    }
}