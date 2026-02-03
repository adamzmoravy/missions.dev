<?php

namespace Karolina;

Class PushToFront {

    private $pusherData;
    private $platformKey;

    public function __construct ()

    {
        $pusherData['pusher_api_key'] = getenv('pusher_api_key');
        $pusherData['pusher_secret'] = getenv('pusher_secret');
        $pusherData['pusher_app_id'] = getenv('pusher_app_id');
    
        $this->pusherData = $pusherData;
        $this->platformKey = getenv('platform_key');

    }

    public function push ($event, $data = [], $channel = null) {
        
        if (!$channel) {
            $channel = $this->platformKey;
        } else {
            $channel = $this->platformKey.'-'.$channel;
        }

        $options = array(
            'cluster' => 'eu',
            'useTLS' => true
        );
        $pusher = new \Pusher\Pusher(
            $this->pusherData['pusher_api_key'],
            $this->pusherData['pusher_secret'],
            $this->pusherData['pusher_app_id'],
            $options
        );

        $pusher->trigger($channel, $event, $data);

    }


}