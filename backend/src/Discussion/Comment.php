<?php

namespace Karolina\Discussion;

use Karolina\Exception;
use Karolina\User\User;
use Karolina\UUID;

Class Comment {

    private $id;
    private $value;
    /* @var User $user */
    private $userId;
    private $topicId;
    private $parentId;
    private $active = true;
    private $type = NULL;
    private $replies = [];
    private $postedTime;
    private $network;
    private $subject = 'My subject is this!';
    private $images = [];
    private $attachments = [];
    private $reactions = [];
    private $format = 'plaintext';
    private $mentionedUserIds = [];
    private $submissions = [];

	public function __construct ($data)
    {

        if (isset($data->id)) {
            $this->setId($data->comment_id);
        } else {
            $this->id = (string) new UUID();
        }

        if (isset($data->images)) {
                
                $this->images = json_decode($data->images, true);
        }
        if (isset($data->reactions)) {
                
            $this->reactions = json_decode($data->reactions, true);
        }


        if (isset($data->attachments)) {
                
            $this->attachments = json_decode($data->attachments, true);
        }

        if (isset($data->submissions)) {
                
            $submissions = json_decode($data->submissions, true);

            foreach ($submissions as $submission) {

                $this->submissions[] = (string) $submission;

            }
        }

        if (isset($data->posted_time)) {

            $this->postedTime = $data->posted_time;

        } else {

            $this->postedTime = time();
        }

        if (isset($data->type)) {
            $this->setType($data->type);
        }
        
        $this->userId = $data->user_id;
        $this->setTopicId($data->topic_id);

        if (isset($data->parent_id)) {

            $this->setParentId($data->parent_id);

        } else {

            $this->parentId = NULL;

        }

        if (isset($data->format) and $data->format != "") {

            $this->format = $data->format;

        }

        if ($this->getFormat() == 'tiptap-json') {


            $this->value = $data->value_json;


        } else {

            $this->value = $data->value;

        }

    }

    public function setFormat ($format) {

        $this->format = $format;

    }

    public function getFormat () {

        return $this->format;

    }

    public function getAttachments () {

        return $this->attachments;

    }

    public function setSingleSubmission ($submission) {
        
        $this->submissions = [$submission];
    }

    public function getSubmissions () {

        return $this->submissions;

    }

    public function getImages () {

        return $this->images;

    }

    public function addImages ($imagesData) {

        $images = array();
        foreach ($imagesData as $imageData) {

            $images[] = new \Karolina\Image\Image($imageData['filename'], 'comments');

        }

        foreach ($images as $image) {

            $this->images[]['filename'] = $image->getFilename();

        }

    }

    /*
        Upvotes and networking

    */

    public function getNetworkType () {

        return "comments";
    }

    public function setNetwork (\Karolina\Network\Root $root) {

        $this->network = $root;

    }

    public function getNetwork () {

        return $this->network;

    }

    public function addUpvote ($upvote) {

        $this->network->addFollower($upvote, "upvote");
    }

    public function removeUpvote ($upvote) {

        $this->network->removeFollower($upvote, "upvote");
    }

    public function userHasUpvoted ($userId) {

        $ids = $this->network->getFollowerIdsByRelationship('upvote');

        foreach ($ids as $id => $type) {
            if ($id == $userId) {
                return true;
            }
        }

        return false;

    }


    public function getUpvoters () {

        if ($this->network) {
            $followers = $this->network->getFollowerIdsByRelationship('upvote');

            $ids = [];
            foreach ($followers as $id => $type) {
                $ids[] = $id;
            }
            return $ids;

        }

        return [];

    }


    public function countUpvotes () {

        if ($this->network) {
            return $this->network->countFollowersByRelationship('upvote');
        }

        return 0;

    }

    public function getDocument () {

        $doc['comment_id'] = (string) $this->getId();
        $doc['value'] = $this->getValue();
        $doc['topic_id'] = (string) $this->getTopicId();
        $doc['parent_id'] = (string) $this->getParentId();
        $doc['user_id'] = (string) $this->getUserId();
        $doc['type'] = (string) $this->getType();
        $doc['subject'] = $this->getSubject();
        $doc['submissions'] = (array) $this->getSubmissions();

        return $doc;

    }


    public function getSubject () {
        return $this->subject;
    }

    public function addReply (Comment $comment) {
        $this->replies[$comment->getId()] = $comment;
    }

    public function getReplies () {
        return $this->replies;
    }

    public function getType()
    {
        return $this->type;
    }

    private function setType($type)
    {
        $this->type = $type;
    }


    public function getPostedTime () {

        return $this->postedTime;
    }

    public function getFirstName () {

        throw new Exception('Depricated method getFirstName');

    }

    public function getLastName () {

        throw new Exception('Depricated method getLastName');

    }

    public function getProfileImage () {

        throw new Exception('Depricated method getProfileImage');


    }

    function setTopicId ($topicId) {

        $this->topicId = $topicId;
    }

    function setValue ($value) {

        if ($value == "" or $value == NULL) {

            throw new Exception("Comment can't be empty","empty_comment");
        }

        $this->value = $value;

    }


    public function getUserId()
    {
        return $this->userId;
    }

    private function setUserId($userId)
    {
        $this->userId = $userId;
    }



    public function getParentId()
    {
        return $this->parentId;
    }

    public function setParentId ($parentId) {

        $this->parentId = $parentId;

    }

    private function replaceRelativeUrls ($value, $absoluteUrl) {

        $value = str_replace('href="/', 'href="'.$absoluteUrl.'/', $value);

        return $value;

    }

    public function getPlainTextValue () {

        return strip_tags($this->getHTMLValue());

    }

    private function findMentionedUserIds ($data) {

        if ($data['type'] == 'entityMention') {


            $this->mentionedUserIds[] = $data['attrs']['id'];

        }

        if (isset($data['content'])) {

            foreach ($data['content'] as $key => $content) {

                $this->findMentionedUserIds($content);

            }

        }

    }

    public function getMentionedUserIds () {

        if ($this->format !== 'tiptap-json') {
            return [];
        }

        $this->findMentionedUserIds($this->value);
        
        return $this->mentionedUserIds;

    }

    public function getHTMLValue ($absoluteUrl = 'https://localhost') {

        if ($this->value == "" or $this->value == NULL) {

        }

        if ($this->format == 'plaintext') {

            return nl2br(htmlspecialchars($this->getSanitizedValue()));

        } else if ($this->format == 'tiptap-json') {

            $tiptap = new \Tiptap\Editor(
                [
                    'extensions' => [
                        new \Tiptap\Extensions\StarterKit(),        
                        new \Karolina\Content\TiptapEntityMentionExtension(),
                    ],
                ]                
            );

            $html = $tiptap
            ->setContent($this->getSanitizedValue())
            ->getHTML();

            return $this->replaceRelativeUrls($html, $absoluteUrl);
        }

    }

    public function getSanitizedValue () {


        if ($this->format == 'plaintext') {

            return strip_tags($this->getValue()); 

        } else if ($this->format == 'tiptap-json') {

            if ($this->getValue() == null) {
                return [];
            }

            return (new \Tiptap\Editor)
            ->sanitize($this->getValue());
        }

    }

    public function getValue () {

	    return $this->value;
    }

    public function getTopicId () {

	    return $this->topicId;
    }


    public function setActive ($active) {

        $this->active = $active;
    }

    public function getActive () {

        return $this->active;
    }

    private function setId ($id) {

        $this->id = (string) new UUID($id);

    }

    public function getId () {

        if (isset($this->id)) {

            return $this->id;

        } else {

            $this->id = (string) new UUID();
            return $this->id;
        }

    }

    function addReaction ($type, $userId) {

        $userId = (string) $userId;
        $this->reactions[$userId] = $type;

    }

    function removeReaction ($userId) {

        unset($this->reactions[$userId]);

    }

    function getReactions () {

        return $this->reactions;

    }

    function getReactionsByType () {

        $reactionsByType = [];

        foreach ($this->reactions as $userId => $reaction) {
            
            $reactionsByType[$reaction][] = (string) $userId;

        }

        return $reactionsByType;

    }

}