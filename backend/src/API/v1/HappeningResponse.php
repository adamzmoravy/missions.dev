<?php
namespace Karolina\API\v1;


use Karolina\Platform;
use Karolina\User\UserRepository;

Class HappeningResponse extends Response
{

    private $document;
    private $platform;
    private $langCode = 'EN';

    public function __construct($document, Platform $platform)
    {

        $this->document = $document;
        $this->platform = $platform;

    }


    function getPreview () {
        $doc = $this->document;

        $response['happening_id'] = (string) $doc['happening_id'];
        $response['name'] = (string) $doc['name'];
        $response['uuid'] = $doc['uuid'];
        $response['registrations_count'] = count($doc['registrations']);
        $response['registrations'] = array_keys($doc['registrations']);
        $response['config']['access'] = $doc['config']['access'];
        $response['config']['registrations'] = $doc['config']['registrations'];

        return $response;

    }

    function getLimited () {
        $doc = $this->document;
        $response['happening_id'] = (string) $doc['happening_id'];
        $response['name'] = (string) $doc['name'];
        $response['config'] = $doc['config'];
        $response['uuid'] = $doc['uuid'];
        $response['schedule'] = [];
        $response['custom_language'] = [];
        $response['registrations'] = [];

        return $response;

    }


    function get ($showPrivateInfo = false, UserRepository $userRepository) {

        $doc = $this->document;

        $response['happening_id'] = (string) $doc['happening_id'];
        $response['name'] = (string) $doc['name'];
        $response['config'] = $doc['config'];
        $response['registrations'] = [];
        
        if ($doc['schedule'] and $doc['schedule'] !== NULL) {
            $response['schedule'] = $doc['schedule'];
        } else {
            $response['schedule'] = [];
        }


        $response['custom_language'] = (new LanguageResponse($doc['custom_language']))->get($this->langCode);

        $response['custom_translations'] = $doc['custom_language'];

        $response['custom_translations_langkeys'] = (new LanguageResponse($doc['custom_language']))->getAllLanguagesResponse();

        $response['uuid'] = $doc['uuid'];
        foreach ($doc['registrations'] as $userId => $registration) {

            $registrationResponse['user_id'] = $userId;
            $registrationResponse['type'] = $registration['type'];

            $registrationResponse['info'] = NULL;
            if ($showPrivateInfo) {


                if ($userDoc = $userRepository->getDocumentById($userId, true)) {

                    $registrationResponse['user'] = (new UserPreviewResponse($userDoc, $this->platform->getImgStorageUrl()))->get(true);
                    if (isset($registration['info'])) {
                        $registrationResponse['info'] = $registration['info'];
                    } else {
                        $registrationResponse['info'] = NULL;
                    }

                }
            }

            $response['registrations'][] = $registrationResponse;

        }


        return $response;
    }

}