<?php

namespace Karolina\API\v1;

Class PlatformResponse extends Response {

    private $platform;


    public function __construct ($platform) {

        $this->platform = $platform;

    }

    public function get () {

        $response['site_label'] = (string) $this->platform->getSiteLabel();
        $response['site_title'] = (string) $this->platform->getSiteTitle();
        $response['mission_statement'] = $this->platform->conf('platform.mission_statement', "This is a place to co-create solutions to important challenges, by sharing ideas and connecting around projects.");

        $response['platform_key'] = (string) $this->platform->getKey();
        $response['pusher_key'] = (string) $this->platform->getPusherKey();

        $response['site_languages'] = (array) $this->platform->getLanguages();
        $response['experiments_sections'] = (array) $this->platform->getExperimentsSections();
        $response['geographic_center'] = $this->platform->getGeographicCenter();
        $response['header_elements'] = $this->platform->getHeaderElements();
        $response['platform_logo'] = $this->platform->getPlatformLogo();
        $response['secret_access_code_active'] = $this->platform->getSecretAccessCodeActive();
        $response['img_storage_url'] = $this->platform->getImgStorageUrl();
        $response['groups']['default'] = $this->platform->conf('platform.groups.default');
        $response['site_intro'] = $this->platform->conf('platform.site_intro', 'Missions.dev');
        $response['site_intro_image'] = $this->platform->conf('platform.site_intro_image');
        $response['access']['site'] = $this->platform->getSiteAccess();

        $response['site_registration_enabled'] = $this->platform->getSiteRegistrationEnabled();

        $response['platform_meta_definitions'] = $this->platform->conf('platform.platform_meta_definitions', null);
        $response['geo_provider_key'] = strrev($this->platform->conf('platform.geo_provider_key', getenv('geo_provider_key')));


        $response['join']['ongoing_groups'] = $this->platform->conf('platform.join.ongoing_groups', []);

        return $response;

    }

    public function getConfigRaw () {

        $configRaw = $this->platform->getConfigRaw();

        $response['ui'] = $configRaw['platform']['ui'];
        unset($configRaw['platform']['ui']);
        $response['platform'] = $configRaw['platform'];        

        return $response;
    }

    public function getUi () {

        $response = $this->platform->conf('platform.ui');

        return $response;

    }


    public function getAdmin () {

        $public = $this->get();

        $private = array();

        $private['secret_access_code'] = $this->platform->getSecretAccessCode();
        $private['account_subscription_offerings'] = $this->platform->getAccountSubscriptionOfferings();


        // merge $private and $public arrays
        $response = array_merge($public, $private);

        return $response;


    }

}