<?php

namespace Karolina\Discussion;

use Karolina\Exception;
use Karolina\User\User;
use Karolina\UUID;

Class Topic {
	
	private $comments = array();
	private $id;

	public function __construct($id = NULL)
    {

        if ($id == NULL) {
            $this->id = $this->getId();
        } else {
            $this->id = (string) new UUID($id);
        }

    }

	public function getDocument () {

	    $doc['topic_id'] = (string) $this->getId();

	    foreach ($this->comments as $comment) {

	        $doc['comments'][(string) $comment->getId()] = $comment->getDocument();

        }

	    return $doc;
    }

    public function getTopicParticipantsIds ($exclude = []) {

        $idsArray = [];

        foreach ($this->comments as $comment) {

            $idsArray = array_merge($idsArray, $this->getCommentConversationParticipantsIds($comment->getId(), $exclude));
        }

        $filteredArray = [];

        foreach ($idsArray as $id) {

            if (!in_array($id, $exclude)) {
                $filteredArray[] = $id;
            }
        }

        return $filteredArray;

    }

    public function getCommentConversationParticipantsIds ($commentId, $exclude = []) {

	    $comment = $this->comments[$commentId];
	    $idsArray = [];
	    $idsArray[] = $comment->getUserId();

	    $replies = $comment->getReplies();
	    $uniqueRepliers = [];
	    foreach ($replies as $reply) {

	        $uniqueRepliers[$reply->getUserId()] = true;
        }

        foreach ($uniqueRepliers as $userId => $replier) {
	        $idsArray[] = $userId;
        }

	    $filteredArray = [];

	    foreach ($idsArray as $id) {

	        if (!in_array($id, $exclude)) {
	            $filteredArray[] = $id;
            }
        }

	    return $filteredArray;

    }

    public function getComments () {
	    return $this->comments;
    }

    public function getCommentsThreadNested () {

	    $rootComments = array();

        foreach ($this->comments as $comment) {
            if (!$comment->getParentId()) {
                $rootComments[$comment->getId()] = $comment;
            }
        }

        foreach ($this->comments as $comment) {
            if ($comment->getParentId()) {
                $rootComment = NULL;
                if (isset($rootComments[$comment->getParentId()])) {
                    $rootComment = $rootComments[$comment->getParentId()];
                }
                if ($rootComment) {
                    $rootComments[$comment->getParentId()]->addReply($comment);
                }
            }
        }

        return $rootComments;

    }

    public function injectComments ($comments) {
	    foreach ($comments as $comment) {


	        $this->comments[$comment->getId()] = $comment;
        }
    }

    public function getTopicId () {
	    return $this->id;
    }

    public function injectComment (Comment $comment) {

    }

    public function addNewReply ($content = 'default', User $user, $type = 'reply', $parentCommentId = false, $format = 'plaintext') {

	    return $this->addNewComment($content, $user, $type, $parentCommentId, null, $format);

    }

    public function tiptapReplaceMention ($data) {


        if ($data['type'] == 'mention') {

            $data['type'] = 'entityMention';
            $splitString = explode('-', $data['attrs']['id']);

            $data['attrs'] = array(
                'id' =>  $splitString[1], 
                'type' => $splitString[0],
                'label' => $data['attrs']['label']
            );
        }

        if (isset($data['content'])) {

            foreach ($data['content'] as $key => $content) {

                $data['content'][$key] = $this->tiptapReplaceMention($content);

            }

        }


        return $data;

    }

    public function addNewComment ($content = 'default', User $user, $type = NULL, $parentCommentId = false, $subject = null, $format = 'plaintext', $submissions = null) {

	    if ($parentCommentId) {
            if (!$this->commentExists($parentCommentId)) {
                throw new Exception("The comment you are trying to reply to does not exist any more");
            }
        }

        $data = new \stdClass();

        if ($format == 'tiptap-json') {
            $content = $this->tiptapReplaceMention($content);
            $data->value_json = $content;

        } else {
            $data->value = $content;
        }

        $data->format = $format;
        $data->topic_id = $this->getTopicId();
        $data->user = $user;
        $data->user_id = $user->getId();
        $data->type = $type;
        $data->submissions = $submissions;

        if ($parentCommentId) {
            $data->parent_id = $parentCommentId;
        }

        $newComment = new Comment($data);
	    $this->comments[(string) $newComment->getId()] = $newComment;
	    return $newComment;
    }

    private function commentExists ($commentId) {

	    if (isset($this->comments[$commentId])) {
	        return true;
        } else {
            return false;
        }

    }

    public function countComments () {

	    return count($this->comments);

    }

    public function getId () {

	    if (isset($this->id)) {

	        return $this->id;

        } else {

            $this->id = new UUID();
            return $this->id;
        }

    }

}