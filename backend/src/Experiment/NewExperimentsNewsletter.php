<?php

namespace Karolina\Experiment;
use Communicator\SignedJwt as Message;

class NewExperimentsNewsletter
{

    private $experimentInteractor;
    private $notificationService;
    private $platform;
    private $experimentRepository;
    private $app;
    private $minRelevance;
    private $newerThan;
    
    public function __construct($app, $minRelevance = 40, $newerThan = 30)
    {

        $this->experimentInteractor = $app['experimentInteractor'];
        $this->notificationService = $app['notificationService'];
        $this->platform = $app['platform'];
        $this->userInteractor = $app['userInteractor'];

        $this->app = $app;
        $this->minRelevance = $minRelevance;
        $this->newerThan = $newerThan;

    }


    public function getNewExperiments () {

        $langCode = 'EN';
        /** @var \Karolina\Experiment\ExperimentInteractor $interactor */
        $interactor = $this->experimentInteractor;
    
        $filterArguments = array(
            'order_by' => 'most_relevant', 
            'limit' => 10, 
            'newer_than_days' => $this->newerThan, 
            'relevance_more_than' => $this->minRelevance, 
            'has_been_sent_in_newsletter' => false);
    
        $experimentsPreviews = $interactor->getPreviewDocuments(
            $langCode,
            $filterArguments
        );
    
        $data = array();

        foreach ($experimentsPreviews as $preview) {

            $data['previews'][]
                = (new \Karolina\API\v1\ExperimentResponse($preview, $langCode, $this->platform))->getPreview(false);
    
        }

        return $data;
    

    }

    private function getUsersWhoShouldReceiveNewsletter () {

        $filterArguments['unsubscribed_to_newsletter'] = false;
        $usersRepo = new \Karolina\User\UserRepository();
        $usersDocuments = $usersRepo->getAllDocuments($filterArguments);
        return $usersDocuments;
    }

    private function getUnsubscribeToken ($userId) {

        // Create an authorization
        $duration = 86400 * 180;
        $message = new Message($this->app['platform']->getSecretKey(), $duration); 
        $message->write('unsubscribe_user_id', $userId);
        $message->write('unsubscribe_to_topic', 'newsletter');
        $token = $message->getTokenString();
        return $token;

    }

    public function getTemplateData () {

        $data = $this->getNewExperiments();

        $data['base_url'] = $this->platform->getPlatformFrontendUrl();
        $data['site_title'] = $this->platform->getSiteTitle();
        $data['unsubscribe_token'] = "[TOKEN]";
        $data['first_name'] = "user";
        return $data;

    }

    public function send () {
        
        $data = $this->getTemplateData();

        $notification = $this->notificationService;

        $emails = array();
        $users = $this->getUsersWhoShouldReceiveNewsletter();

        foreach ($users as $user) {

            $data['unsubscribe_token'] = $this->getUnsubscribeToken($user['user_id']);

            $email = array();

            $data['first_name'] = $user['first_name'];

            $email['from_name'] = $this->platform->getPlatformName();
            $email['from_email'] = $this->platform->conf('email_noreply');
            $email['to_emails'] = array($user['email']);
            $email['cc_emails'] = array();
            $email['bcc_emails'] = array();
            $email['subject'] = 'Recent submission in '.$this->platform->getSiteLabel();

            $body = $notification->getBodyFromTemplate("newsletter", $data);

            $email['plaintext_body'] = strip_tags($body);
            $email['html_body'] = $body;
            $email['reply_to_addresses'] = array($this->platform->conf('email_noreply'));
            $email['return_email'] = $this->platform->conf('email_noreply');
            $emails[] = $email;

        }


        $notification->sendWithSESAsync($emails);

        $this->markExperimentsAsHavingBeenSent($data['previews']);


    }

    private function markExperimentsAsHavingBeenSent ($experimentsData) {

        $idsArray = array();

        foreach ($experimentsData as $exp) {
            $idsArray[] = $exp['experiment_id'];
        }


        $this->experimentInteractor->massUpdateSetting($idsArray, 'has_been_sent_in_newsletter', time());
        
    }





}