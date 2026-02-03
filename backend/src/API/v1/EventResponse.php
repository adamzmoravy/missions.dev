<?php
namespace Karolina\API\v1;
use Karolina\Platform;


Class EventResponse extends Response {

    private $document;
    private $platform;

    public function __construct ($document, Platform $platform) {

        $this->document = $document;
        $this->imagePath = $platform->getImgStorageUrl();
        $this->platform = $platform;
    }

    public function get () {

        $doc = $this->document;
        $imgStorageUrl = $this->imagePath;

        $response = array();

        // Put absolute urls for teams
        foreach ($doc as $event) {

            $eventResponse = [];
            $eventResponse['event_id'] = $event['event_id'];
            $eventResponse['event_type'] = $event['event_type'];
            $eventResponse['occurred_on'] = $event['occurred_on'];
            $eventResponse['occurred_on_timestamp'] = date('c', $event['occurred_on']);
            $eventResponse['data'] = $event['data'];
            $eventResponse['actor_id'] = $event['actor_id'];
            $response[] = $eventResponse;
/**
            if ($event['event_type'] == "new_experiment") {

                $eventResponse['experiment'] = $this->getExperimentResponse($event['experiment']);
                $response[] = $eventResponse;
            }

            if ($event['event_type'] == "comment") {

                $eventResponse['comment'] = $this->getCommentResponse($event['comment']);
                $eventResponse['experiment'] = $this->getExperimentResponse($event['experiment']);
                $response[] = $eventResponse;
            }

            if ($event['event_type'] == "new_user") {

                $eventResponse['user'] = $this->getUserResponse($event['user']);
                $response[] = $eventResponse;
            }
 */

        }

        return $response;

    }

    private function getUserResponse ($doc) {

        return (new UserPreviewResponse($doc, $this->platform->getImgStorageUrl()))->get();

    }

    private function getExperimentResponse($doc) {

        return (new ExperimentResponse($doc, 'EN', $this->platform))->getPreview();
    }

    private function getCommentResponse($comment) {

        $response = (new CommentsResponse([$comment], $this->platform->getImgStorageUrl()))->get();
        return $response[0];
    }

}
