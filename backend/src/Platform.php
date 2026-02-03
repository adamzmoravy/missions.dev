<?php

namespace Karolina;
use Karolina\Database\Table\PlatformConfig;
use Noodlehaus\Config;
use Noodlehaus\Parser\Json;

use Respect\Validation\Validator as v;


Class Platform {

    public $conf;
    private $dbConf = [];
    public $Currency;
    private $eventPublisher;
    private $limiter;
    private $ci = NULL;
    private $databaseTable;
    private $key = NULL;

    private $settings;  use \Karolina\Setting\SettingTrait;


    public function __construct($configFile = false)
    {


        $this->databaseTable = new PlatformConfig();

        $this->setupConfig();
        $this->setupCurrency();
        $this->setupEvents();

        $this->limiter = new \Karolina\Event\RateLimiter();

    }

    public function visibleSubmissionStagesToUser ($user) {

        $accessControl = new \Karolina\AccessControl($this->getAccessControlRules());
        $stages = $this->conf('platform.ui.stages', [0, 1, 2, 3]);

        $visibleStages = [];

        foreach ($stages as $stage) {

            if ($accessControl->user($user)->on('submission-types::stage-'.$stage)->can('read')) {
                $visibleStages[] = $stage;
            }

        }

        return $visibleStages;

    }

    public function getAccessControlRules () {

        return $this->conf('platform.access_control_rules', [], null);

    }

    public function getPusherKey () {

        return getenv('pusher_api_key');

    }

    public function getConfigPath () {

        return '/../../config/';

    }

    public function setPlatformConfig ($platformConfig) {

        $this->databaseTable = $platformConfig;

    }


    public function setKey ($key) {

        $this->key = (string) $key;

    }

    public function getKey () {

        if ($this->key) {

            return $this->key;

        } else {
            return getenv('platform_key');
        }


    }

    public function getCurrencyISO () {

        return $this->Currency->getISO();
    }

    public function setCI ($ci) {

        $this->ci = $ci;

    }

    public function limit ($event, $limit, $timeframe) {

        $this->limiter->happen($event, $limit, $timeframe);

    }

    public function getStageHandles () {

        return $this->conf('platform.ui.stageHandles', [], null);

    }

    public function getAccountSubscriptionOfferings () {

        return $this->conf('platform.accounts_subscription_offerings', [], null);

    }

    public function getSecretKey () {

        return getenv('platform_secret_key');
    }

    private function setupEvents () {

        $this->eventPublisher = \Karolina\Event\Publisher::instance();

    }

    public function publishEvent ($event) {

        $this->eventPublisher->publish($event);
    }

    public function subscribeToEvents ($subscriber) {

        $this->eventPublisher->subscribe($subscriber);
    }

    private function setupCurrency () {

        $currencyCode = $this->conf->get('currency');
        $this->Currency = new Currency($currencyCode);
    }

    public function getCurrency () {

        return $this->Currency;
    }


    private function setupConfig () {


        $conf = Config::load(
            __DIR__.$this->getConfigPath(),
        );

        $this->conf = $conf;

    }

    public function getPlatformName () {

        return $this->getSiteTitle();
    }


    public function getPlatformFrontendUrl () {

        return getenv('protocol').'://'.$_SERVER['SERVER_NAME'];

    }


    public function getPasswordResetUrl () {

        

        return getenv('protocol').'://'.$_SERVER['SERVER_NAME']."/password-change/";

    }

    public function getPledgeConfirmUrl () {

        return getenv('protocol').'://'.$_SERVER['SERVER_NAME']."/auth/confirm_pledge/";

    }


    public function getImgStorageUrl () {

        return "https://s3-eu-west-1.amazonaws.com/idea-village-image-host/".$this->conf('key');

    }

    public function conf ($variable, $fallback = null) {

        if ($fallback != null) {
            return $this->conf->get($variable, $fallback);            
        }

        return $this->conf->get($variable);

    }

    public function getConfigRaw () {

        return $this->conf->all();

    }


    public function setHeaderElements ($elements) {

        $this->setConf('header_elements', $elements);

    }

    public function getHeaderElements () {

        $default = ['home', 'missions', 'activity', 'groups', 'meetings'];

        return $this->getConfWithDefault('header_elements', $default);

    }

    public function setPlatformLogo ($platformLogo) {

        $logo['url'] = $platformLogo;
        if ($logo['url'] == "") {
            $logo['url'] = null;
            return $this->setConf('platform_logo', $logo);
        }

        if (v::oneOf(v::extension('jpg'), v::extension('png'), v::extension('svg'), v::extension('jpeg'))->noWhitespace()->validate($platformLogo)) {

            return $this->setConf('platform_logo', $logo);

        } else {

            throw new Exception('Logo path is incorrect format: ', 'incorrect_format');
            
        }

    }

    public function getSiteRegistrationEnabled () {

        $default = false;

        if ($this->getSiteAccess() == 'public') {
            $default = true;
        }

        return $this->getConfWithDefault('site_registration_enabled', $default);

    }

    public function getSiteAccess () {

        return $this->getConfWithDefault('access', 'members_only', 'site');

    }

    public function setSiteAccess ($access) {

        $access = strtolower($access);

        if (!in_array($access, ['public', 'members_only'])) {
            throw new Exception('Platform access must be either public or members_only', 'incorrect_access');
        }

        $value['site'] = $access;
        $this->setConf('access', $value);

    }

    private function generateSecretAccessCode() {

        $secret = bin2hex(random_bytes(16));

        return $secret;

    }

    public function getSecretAccessCode () {

        if ($this->getConfWithDefault('secret_access_code', null, 'code') == null) {

            $newSecretKey = $this->generateSecretAccessCode();

            $this->setConf('secret_access_code', ['code' => $newSecretKey]);
            return $newSecretKey;

        }

        return $this->getConfWithDefault('secret_access_code', null, 'code');
        
    }

    public function setSecretAccessCodeActive ($active) {

        $value['active'] = (boolean) $active;
        $this->setConf('secret_access_code_active', $value);
        
    }

    public function getSecretAccessCodeActive () {

        return $this->getConfWithDefault('secret_access_code_active', false, 'active');
        
    }


    public function getPlatformLogo () {

        $default['url'] = null;

        return $this->getConfWithDefault('platform_logo', $default['url'], 'url');

    }

    public function getSiteLabel () {

        return $this->getConfWithDefault('site_label', getenv('site_label'), 'string');

    }

    public function getSiteTitle () {

        return $this->getConfWithDefault('site_title', getenv('site_title'),'string');

    }

    public function getLanguages () {

        return $this->getConfWithDefault('site_languages', ['en'], 'codes');

    }

    public function setLanguages (array $langArray) {

        if (count($langArray) == 0) {
            throw new Exception('You need to set at least one language');
        }

        $languages = [];

        foreach ($langArray as $langKey) {
            $languages[] = Keyname::validate($langKey, 'lang_key');
        }

        $value['codes'] = $languages;
        $this->setConf('site_languages', $value);

    }

    public function setSiteLabel ($label) {

        $label = substr($label, 0, 200);
        $this->setConf('site_label', ['string' => $label]);

    }

    public function setMissionStatement ($statement) {

        $statement = substr($statement, 0, 600);
        $this->setConf('mission_statement', $statement);
        
    }

    public function setSiteTitle ($label) {

        $label = substr($label, 0, 400);
        $this->setConf('site_title', ['string' => $label]);

    }

    public function getGeographicCenter () {
        $default = json_decode(getenv('geographic_center'));
        return $this->getConfWithDefault('geographic_center', $default,'coordinates');


    }

    function getUsersToEventsSubscriptions () {

        return $this->getConfWithDefault('users_to_events_subscriptions', [], null);

    }


    function getExternalToEventsSubscriptions () {

        return $this->getConfWithDefault('external_to_events_subscriptions', [], null);

    }


    public function setConfPublic ($variableName, $value) {

        $variableName = Keyname::validateConfigKey($variableName);

        $conf = $this->databaseTable->firstOrCreate([
            'name' => $variableName,
            'platform' => $this->getKey()
        ]);

        $conf->value = json_encode($value);
        $conf->save();

    }

    private function setConf ($variableName, $value) {

        $variableName = Keyname::validateConfigKey($variableName);

        $conf = $this->databaseTable->firstOrCreate([
            'name' => $variableName,
            'platform' => $this->getKey()
        ]);

        $conf->value = json_encode($value);
        $conf->save();

    }

    private function getConfRequired ($variableName, $field = NULL) {

        $conf = $this->getDbConf($variableName, $field);

        if ($conf) {
            return $conf;
        } else {
            throw new Exception('Required config variable '.$variableName.' not set on platform', 'missing_config');
        }

    }

    private function getConfWithDefault ($variableName, $default, $field = NULL) {

        $conf = $this->getDbConf($variableName, $field);

        if ($conf)
            return $conf;
        else
            return $default;

    }

    public function setDBConf ($variableName, $field) {

        $this->dbConf[$variableName] = $field;
        $this->conf->set('platform.'.$variableName, $field);

    }

    private function getDbConf ($variableName, $field = NULL) {

        if (!isset($this->dbConf[$variableName])) {
            return NULL;
        }

        $conf = $this->dbConf[$variableName];

        if ($field) {

            return $conf[$field];

        }

        return $conf;
    }

    public function getExperimentsSections () {

        $defaultSections = ['tags' => true, 'video_chat' => false, 'related_submissions' => true, 'questions' => false, 'further_info' => true, 'discussion' => true];

        $platformSet = $this->getConfWithDefault('experiments_sections', $defaultSections, 'sections');

        return array_merge($defaultSections, $platformSet);


    }


}
