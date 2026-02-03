<?php

namespace Karolina\Experiment;

use Carbon\Carbon;
use Respect\Validation\Validator as v;
use \Karolina\User\User;
use \Karolina\UUID;

Class Experiment {

    private $languageFields; use \Karolina\Language\LanguageTrait;
    private $customLanguageFields; use \Karolina\Language\CustomLanguageTrait;
    private $team; use \Karolina\Team\TeamTrait;
    private $images = array(); use \Karolina\Image\ImageCollectionTrait;
    private $settings;  use \Karolina\Setting\SettingTrait;

    private $id;
    private $uuid = NULL;
    private $stage = 0;
    private $createdAt;
    private $updatedAt;
    private $disabled = false;
    private $placesToShow = null;
    private $placesToShowPlacement = null;
    private $tags = array();
    private $fundingSources;
    private $fundingGoalAmount;
    private $fundingGoalCurrency;
    private $network;
    private $links = array();
    private $commentCount;
    private $restrictions;
    private $questions = array();
    private $attachments = array();


    public function __construct ($currency = 'EUR') {

    	$this->languageFields = new \Karolina\Language\LanguageFields();
    	$this->customLanguageFields = new \Karolina\Language\LanguageFields();
        $this->team = new \Karolina\Team\Team();
        $this->fundingSources = new FundingSources($currency);
        $this->fundingGoalCurrency = new \Karolina\Currency($currency);
        $this->network = new \Karolina\Network\Root(
            new \Karolina\Network\Node('experiments')
            );
        $this->setSettingsGroup(new \Karolina\Setting\SettingsGroup());

    }

    public function getVisibility () {

        return $this->setting('visibility', 'public');

    }

    public function setVisibility ($visibility) {

        $allowed = ['public', 'private'];

        if (!in_array($visibility, $allowed)) {
            throw new \Karolina\Exception('Visibility not allowed. Allowed visibility are: '.implode(', ', $allowed));
        }

    
        $this->setSetting('visibility', $visibility);
    }

    public function getStatus () {

        return $this->setting('status', 'published');

    }

    public function setStatus ($status) {

        $allowed = ['published', 'draft', 'archived', 'resolved'];

        if (!in_array($status, $allowed)) {
            throw new \Karolina\Exception('Status not allowed. Allowed status are: '.implode(', ', $allowed));
        }

    
        $this->setSetting('status', $status);
    }

    public function isJoinable () {

        if ($this->stage == 8) {
            return true;
        }

        return false;

    }

    public function usersWhoShouldGetEmailUpdates () {

        // Add experiment team members
        $teamIds = array_keys($this->getTeamMembers());

        // add also people who liked it
        $likersIds = array_keys( $this->getLikers());

        // add also people who want to help
        $helpersIds = array_keys( $this->getHelpers());

        // add also people who want updates
        $updatesIds = array_keys( $this->getUpdatesFollowers());

        $sendEmailUpdate = array_merge($teamIds, $likersIds, $helpersIds, $updatesIds);

        return $sendEmailUpdate;

    }

    public function addAttachment ($type, $data, $name = NULL) {
            
            $attachment['type'] = $type;
            $attachment['data'] = $data;
            $attachment['name'] = (string) substr($name, 0, 200);
            $attachment['id'] = (string) new UUID();

            $this->attachments[] = $attachment;
            return $attachment;
    
    }

    public function getAttachments () {

        return $this->attachments;

    }

    public function removeAttachment ($id) {

        foreach ($this->attachments as $key => $attachment) {

            if ($attachment['id'] == $id) {

               unset($this->attachments[$key]);

            }

        }

    }

    public function convertTranslationTags ($text) {

        // convert ${'not_allowing_new_registrations'|trans} to $trans('not_allowing_new_registrations') in $text
        
        $text = preg_replace_callback('/\$\{\'([a-z0-9_]+)\'\|trans\}/', function ($matches) {
            return '$trans(\''.$matches[1].'\')';
        }, $text);

    }

    public function setLastCommentTime () {

        $time = time();
        $this->setSetting('last_comment_time', $time);

    }

    public function getLastMajorUpdate () {

        if ($this->settingIsSet('last_comment_time')) {
            return $this->setting('last_comment_time');
        } 

        if ($this->createdAt) {

            return $this->createdAt->timestamp;
            
        }

        return time();

    }

    public function getRelevance () {


        $factors['comments'] = [
                'count' => $this->getCommentCount(),
                'weight' => 10,
                'exponent' => 0.8
        ];

        $factors['teamMembers'] = [
            'count' => $this->countTeamMembers(),
            'weight' => 10,
            'exponent' => 0.8
        ];

        $factors['likes'] = [
            'count' => $this->network->countFollowersByRelationship('like'),
            'weight' => 5,
            'exponent' => 0.8
        ];

        $factors['updates'] = [
            'count' => $this->network->countFollowersByRelationship('updates'),
            'weight' => 7,
            'exponent' => 0.8
        ];

        $factors['help'] = [
            'count' => $this->network->countFollowersByRelationship('help'),
            'weight' => 10,
            'exponent' => 0.8
        ];


        $factors['related'] = [
            'count' => $this->network->countFollowersByRelationship('related'),
            'weight' => 10,
            'exponent' => 0.5
        ];

        $factors['images'] = [
            'count' => count($this->images),
            'weight' => 5,
            'exponent' => 0.8
        ];

        $factors['text'] = [
            'count' => strlen($this->getCustomLanguage('long_description')),
            'weight' => 0.4,
            'exponent' => 0.5
        ];




        $totalScore = 0;

        foreach ($factors as $name => $factor) {

            $factors[$name]['score'] = pow(($factor['count'] * $factor['weight']), $factor['exponent']);

            $totalScore = $totalScore + $factors[$name]['score'];

        }


        return round($totalScore);

    }

    public function getRestrictions () {

        return $this->setting('restrictions');
    }

    public function setRestrictions (array $restrictions) {

        $this->setSetting('restrictions', $restrictions);

    }

    public function getTopicId () {

        return $this->getUUID();

    }

    public function setCommentCount (int $num) {

        $this->commentCount = $num;
    }

    public function getCommentCount () {

        return $this->commentCount;
    }

    public function setUUID ($uuid) {

        if ($uuid == NULL or $uuid == "") {
            $this->uuid = $this->getUUID();
        } else {
            $this->uuid = new UUID($uuid);
        }

    }

    public function getUUID () {

        if ($this->uuid == NULL)  {

            $this->uuid = new UUID();


        }

        return (string) $this->uuid;


    }

    public function moveToNextStage () {

        if ($this->stage == 5) {

            throw new \Karolina\Exception('You are already at the last stage. Your stage is: '.$this->getStage());

        }
        $nextStage = $this->stage + 1;

        // Current long description
        $orgLongDescField = $this->stage.'_long_description';
        $nextLongDescField = $nextStage.'_long_description';

        if ($this->getCustomLanguage($nextLongDescField) == "") {
            $this->customLanguageFields->duplicateFields($orgLongDescField, $nextLongDescField);
        }

        $this->setStage($nextStage);


    }

    public function moveToPreviousStage () {

        if ($this->stage == 0) {

            throw new \Karolina\Exception('You are already at the last stage. Your stage is: '.$this->getStage());

        }

        $previousStage = $this->stage - 1;

        $this->setStage($previousStage);

    }

    public function setGeographicLocation ($location) {

        $this->setSetting('geographic_location', $location);

    }

    public function getGeographicLocation () {

        if ($this->settingIsSet('geographic_location')) {
            return $this->setting('geographic_location');

        } else {
            return "";
        }

    }

    public function setLinks (array $links) {

        $newLinks = array();
        foreach ($links as $link) {

            $newLinks[] = new \Karolina\Content\Link($link['url'], $link['title']);

        }

        $this->links = $newLinks;

    }

    public function getLinks () {

        $linkDocs = array();
        foreach ($this->links as $link) {

            $doc = new \Karolina\Content\LinkDocument($link);
            $linkDocs[] = $doc->get();

        }
        return $linkDocs;

    }

    public function getId() {

        return $this->id;

    }

    public function setId ($id) {

        $this->id = $id;
    }


    public function getStage () {

        return $this->stage;
    }

    public function setStage ($stage) {

        $this->stage = (int) $stage;

    }

    public function getCreatedAt () {

        return (string)$this->createdAt;
    }

    public function setCreatedAt ($createdAt) {

        $this->createdAt = $createdAt;

    }

    public function getUpdatedAt () {

        return (string)$this->updatedAt;
    }

    public function setUpdatedAt ($updatedAt) {

        $this->updatedAt = $updatedAt;

    }

    /*
        Likes and networking

    */

    public function getNetworkType () {

        return "experiments";
    }

    public function setNetwork (\Karolina\Network\Root $root) {

        $this->network = $root;

    }

    public function getNetwork () {

        return $this->network;

    }

    public function addHelper ($helper) {

        $this->network->addFollower($helper, "help");
    }

    public function removeHelper ($helper) {

        $this->network->removeFollower($helper, "help");
    }

    public function addUpdatesFollower ($helper) {

        $this->network->addFollower($helper, "updates");
    }

    public function removeUpdatesFollower ($helper) {

        $this->network->removeFollower($helper, "updates");
    }

    public function addLike ($liker) {

        $this->network->addFollower($liker, "like");

    }


    public function removeLike ($liker) {

        $this->network->removeFollower($liker, "like");

    }

    public function countLikes () {

        return $this->network->countFollowersByRelationship('like');

    }

    public function getLikers () {

        return $this->network->getFollowerIdsByRelationship('like');

    }


    public function getHelpers () {

        return $this->network->getFollowerIdsByRelationship('help');

    }



    public function getUpdatesFollowers () {

        return $this->network->getFollowerIdsByRelationship('updates');

    }



    public function getUsersWhoLike () {

        $followers = $this->network->getFollowersByRelationship('like');

        $users = array();

        foreach ($followers as $relationship) {

            $users[] = $relationship->getFollower();

        }

        return $users;

    }



    /*
        Other experiments relationship

    */


    public function addExperimentRelationship (Experiment $experiment) {

        if ($experiment->getId() == $this->getId()) {
            throw new \Karolina\Exception("Can't add experiment into a relationship with itself");

        } else {
            $this->network->addFollower($experiment, "related");
        }

    }

    public function removeExperimentRelationship (Experiment $experiment) {

        $this->network->removeFollower($experiment, "related");

    }

    public function getRelatedExperiments () {

        return $this->network->getFollowerIdsByRelationship('related');

    }



    /*
        FUNDING
    */

    public function getFundingDocument () {

        $funding['sources'] = $this->fundingSources->getDocument();
        $funding['goal'] = $this->fundingGoalAmount;
        $funding['currency'] = $this->fundingGoalCurrency->getISO();

        return $funding;

    }

    public function setFundingGoalAmount ($amount) {

        if (!v::intVal()->not(v::negative())->validate($amount)) {

            throw new \Karolina\Exception("Amount for funding goal must be a non-negative natural number. Amount not allowed: ".htmlentities($amount));

        }

        $this->fundingGoalAmount = (int) $amount;

    }

    public function setFundingGoalCurrency ($currencyKey) {

        $this->fundingGoalCurrency = new \Karolina\Currency($currencyKey);

    }


    public function setFundingGoal ($amount, $currencyKey = "EUR") {

        if (!v::intVal()->not(v::negative())->validate($amount)) {

            throw new \Karolina\Exception("Amount for funding goal must be a non-negative natural number. Amount not allowed: ".htmlentities($amount));

        }

        $this->fundingGoalAmount = $amount;
        $this->fundingGoalCurrency = new \Karolina\Currency($currencyKey);

    }

    public function setStateFunding ($amount) {

        return $this->fundingSources->setFundingSource('state', $amount);
    }

    public function setOrganizationsFunding ($amount) {

        return $this->fundingSources->setFundingSource('organizations', $amount);

    }

    public function setCrowdFunding ($apiURL, $campaignId) {

        return $this->fundingSources->setExternalFundingSource('crowd', $apiURL, $campaignId);

    }

    public function setFundingFromDocument ($doc) {

        if (isset($doc['goal'])) {
            $this->setFundingGoal($doc['goal'], $doc['currency']);
        }

        if (isset($doc['sounces'])) {
            $this->fundingSources->setFromDocument($doc['sources']);

        }

        $this->fundingSources->setFromDocument($doc['sources']);

    }


    /*
        Tags
    */

    public function replaceTags ($tags) {

        $this->tags = array();

        foreach ($tags as $tag) {

            $this->addTag($tag);
        }

    }

    public function addTag(\Karolina\Tag\Tag $tag) {

        $this->tags[$tag->getId()] = $tag;
    }

    public function removeTag($tagId) {

        unset($this->tags[$tagId]);

    }

    public function getTags () {

        return $this->tags;

    }

    public function getTagsDocument () {

        $doc = array();
        foreach ($this->tags as $tag) {
            $doc[$tag->getId()] = $tag->getLabelDocument();
        }

        return $doc;

    }

    /*
        Questions and answers
    */


    public function getQuestions () {

        return $this->questions;

    }

    public function getQuestionsDocument () {

        $doc = array();

        if ($this->getQuestions()) {

            foreach ($this->getQuestions() as $question) {

                $answerDoc = $question->getAnswerDocument();
                $questionDoc = $question->getDocument();

                $doc[$question->getContext()][$question->getId()]['question'] = $questionDoc;
                $doc[$question->getContext()][$question->getId()]['answer'] = $answerDoc;
            }

        }
        return $doc;
    }

    public function addQuestion (\Karolina\Question\Question $question) {

        $question->setObjectType('experiment');
        $question->setObjectId($this->getId());
        $this->questions[$question->getContext()] = $question;
    }

    public function getQuestion ($context) {

        return $this->questions[$context];

    }


    /*
       Language

    */


    public function getLanguageFields () {

        $this->languageFields->setObjectId($this->getId());
        return $this->languageFields;

    }

    public function getCustomLanguageFields () {

        $this->customLanguageFields->setObjectId($this->getId());
        return $this->customLanguageFields;

    }


    /*
        Where to show (control where in layout or pages experiment is shown)
    */

    public function getPlacesToShow () {

        if ($this->placesToShow == NULL) {

            return new \stdClass();

        } else {

          return $this->placesToShow;

        }
    }

    public function setPlaceToShow ($placeName, $set) {

        if (!v::alnum('-_')->noWhitespace()->validate($placeName)) {
            throw new \Exception ('Place field can only have alpha numeric and _ -');
        }
        if (!v::boolType()->validate($set)) {

            throw new \Exception ('Setting for place to show must be true or false.');
        }

        $this->placesToShow[$placeName] = $set;

    }


    /*
        Where in a particular grid in a place to show experiment
    */

    public function getPlacesToShowPlacement () {

        if ($this->placesToShowPlacement == NULL) {

            return new \stdClass();

        } else {

            return $this->placesToShowPlacement;

        }
    }

    public function setPlaceToShowPlacement ($placeName, $placement) {

        if (!v::alnum('-_')->noWhitespace()->validate($placeName)) {
            throw new \Exception ('Place field can only have alpha numeric and _ -');
        }

        if ($placement == "" or $placement == false) {

            unset($this->placesToShowPlacement[$placeName]);

        } else {

            $placementAdd['x'] = (int) $placement['x'];
            $placementAdd['y'] = (int) $placement['y'];
            $this->placesToShowPlacement[$placeName] = $placementAdd;

        }


    }

    /*
        ACCESS CONTROL:
        Outside clients need to enforce access control by checking the below methods.
    */

    public function removeTeamMember (\Karolina\User\User $user) {

        $this->removeFromTeam($user);

    }


    public function addTeamMember (\Karolina\User\User $user) {

        $this->addToTeam($user, ['member']);

    }

    public function addCreator (\Karolina\User\User $user) {

        $this->addToTeam($user, ['creator']);

    }

    public function isInTeam (\Karolina\User\User $user) {

        return $this->team->isInTeam($user);

    }

    public function isInTeamOrIsAdmin ($user) {

        return $this->team->isInTeam($user) or $user->isAdmin();

    }

    public function canEditWhereToShow (User $user) {

        return $user->isAdmin();

    }

    public function canEditAbility (User $user) {

        return $this->team->isInTeam($user) or $user->isAdmin();

    }

    public function canEditSettings (User $user) {

        return $this->isInTeamOrIsAdmin($user);

    }

    public function canEditFunding (User $user) {

        return $user->isAdmin();

    }


    public function canEditContent (User $user) {

        return $this->isInTeamOrIsAdmin($user);

    }


    /*
        ENABLED / DISABLED:
        Disable is like soft delete. Hidden but still exists in DB.

    */

    public function disable () {

        $this->disabled = true;

    }


    public function isEnabled () {

        if ($this->disabled) {

            return false;

        } else {

            return true;
        }

    }

    public function enable () {

        $this->disabled = false;

    }

    public function __set ($name, $value) {

        $this->$name = $value;

    }

}
