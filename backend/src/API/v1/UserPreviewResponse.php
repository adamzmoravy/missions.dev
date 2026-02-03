<?php
/**
 * Created by PhpStorm.
 * User: arnarfjodur
 * Date: 25/07/17
 * Time: 19:57
 */

namespace Karolina\API\v1;


use Karolina\User\UserPreview;

class UserPreviewResponse
{

    private $doc;
    private $imgStorageUrl;

    public function __construct($doc, $imgStorageUrl)
    {

        $this->doc = $doc;
        $this->imgStorageUrl = $imgStorageUrl;

    }

    public function get ($showPrivateInfo = false) {

        $usersDocument = $this->doc;

        $userField['image'] = NULL;
        foreach ($usersDocument['image'] as $contentKey => $imageData) {
            $userField['image'] = $this->imgStorageUrl."/".$imageData['filename'];
        }

        $userField['user_id'] = $usersDocument['user_id'];
        $userField['first_name'] = $usersDocument['first_name'];
        $userField['last_name'] = $usersDocument['last_name'];
        $userField['email'] = NULL;
        $userField['meta'] = NULL;

        $userField['geographic_location'] = NULL;

        if (isset($usersDocument['settings']['geographic_location'])) {
            
            $userField['geographic_location']['vicinity'] = $usersDocument['settings']['geographic_location']['vicinity'];
            $userField['geographic_location']['country'] = $usersDocument['settings']['geographic_location']['country'];
            $userField['geographic_location']['lat'] = $usersDocument['settings']['geographic_location']['location']['lat'];
            $userField['geographic_location']['lng'] = $usersDocument['settings']['geographic_location']['location']['lng'];

        }
        
        if ($showPrivateInfo) {
            if (isset($usersDocument['email'])) {
                $userField['email'] = $usersDocument['email'];
            }
            if (isset($usersDocument['profile_meta'])) {
                $userField['meta'] = $usersDocument['profile_meta'];
            }
        }

        $userField['short_description'] = $usersDocument['short_description'];
        $userField['links'] = $usersDocument['links'];

        $userField['skills'] = [];
        if (isset($usersDocument['skills'])) {
            foreach ($usersDocument['skills'] as $skill) {
                $userField['skills'][] = (string) $skill;
            }
        }


        $tagResponse = new \Karolina\API\v1\TagsResponse($usersDocument['tags'], 'EN');
        $userField['tags'] = $tagResponse->get();

        $userField['tags_with_type'] = [];
        if (isset($usersDocument['tags_with_type']))  {
            $userField['tags_with_type'] = $usersDocument['tags_with_type'];

        }

        $userField['comments_count'] = count($usersDocument['comments']);
        $userField['comment_types'] = $this->countCommentsByType($usersDocument['comments']);

        return $userField;
    }

    private function countCommentsByType ($comments) {

        $types = [];
        foreach ($comments as $comment) {

            if (!isset($types[$comment['type']])) {
                $types[$comment['type']] = 0;
            }

            $types[$comment['type']]++;

        }

        return $types;

    }

}