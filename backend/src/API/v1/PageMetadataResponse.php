<?php

namespace Karolina\API\v1;


Class PageMetadataResponse extends Response
{

    private $metadata;
    private $url;

    public function __construct(array $metadata, $url = null) {

        $this->metadata = $metadata;
        $this->url = $url;

    }

    private function existsOrNull ($key) {

        $value = $this->metadata[$key] ?? $this->metadata['og'][$key] ?? $this->metadata['meta'][$key] ?? null;

            if ($value !== null && strlen($value) > 500) {
                $value = substr($value, 0, 500);
            }

            if ($value === null or $value === '') {
                return null;
            }

            return (string) $value;

    }

    private function cleanAndSafeUrl ($url) {

        $url = $this->existsOrNull($url);

        if ($url === null) {
            return null;
        }

        $url = filter_var($url, FILTER_SANITIZE_URL);

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return $url;

    }

    public function inferredSiteName () {

        $hosts['facebook.com'] = 'Facebook';
        $hosts['x.com'] = 'Twitter';
        $hosts['instagram.com'] = 'Instagram';
        $hosts['linkedin.com'] = 'LinkedIn';
        $hosts['youtube.com'] = 'YouTube';
        $hosts['github.com'] = 'GitHub';
        $hosts['medium.com'] = 'Medium';
        $hosts['pinterest.com'] = 'Pinterest';
        $hosts['tiktok.com'] = 'TikTok';

        $hostanme = parse_url($this->url, PHP_URL_HOST);

        if ($hostanme !== null) {
            if (isset($hosts[$hostanme])) {
                return $hosts[$hostanme];
            }
        }

        if ($this->existsOrNull('site_name')) {
            return $this->existsOrNull('site_name');
        }

        return null;

    }


    public function get () {

        $response['title'] = $this->existsOrNull('title');
        $response['description'] = $this->existsOrNull('description');
        $response['image'] = $this->cleanAndSafeUrl('image');
        $response['site_name'] = $this->inferredSiteName();
        $response['canonical'] = $this->cleanAndSafeUrl('canonical');
        $response['type'] = $this->existsOrNull('type');
        $response['favicon'] = $this->cleanAndSafeUrl('favicon');
        $response['url'] = $this->cleanAndSafeUrl('url');
        
        return $response;

    }

}

