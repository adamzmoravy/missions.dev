<?php


namespace Karolina;

use Karolina\Database\Table\Cache;


Class CacheRepository {

    public function store (string $key, string $stash, int $forSeconds) {

        $storeUntil = time() + $forSeconds;

        $storeUntilDateTime = new \DateTime();
        $storeUntilDateTime->setTimestamp($storeUntil);

        try {
            Cache::updateOrCreate(
                ['key' => $key],
                [
                    'stash' => $stash,
                    'store_until' => $storeUntilDateTime
                ] 
            );
        } catch (\Exception $e) {
            throw new \Karolina\Exception("Problem storing cache for key: " . $key, $e->getCode());
        }

        $this->purgeAllExpired();

    
    }

    public function purge (string $key) {

        try {
            Cache::where('key', $key)->delete();
        } catch (\Exception $e) {
            throw new \Karolina\Exception("Problem purging cache for key: " . $key, $e->getCode());
        }

    }

    public function purgeAll () {

        try {
            Cache::truncate();
        } catch (\Exception $e) {
            throw new \Karolina\Exception("Problem purging all cache entries", $e->getCode());
        }

    }

    public function purgeAllExpired () {

        $currentDateTime = new \DateTime();

        try {
            Cache::where('store_until', '<=', $currentDateTime)->delete();
        } catch (\Exception $e) {
            throw new \Karolina\Exception("Problem purging expired cache entries", $e->getCode());
        }

    }

    public function get (string $key) {

        $currentDateTime = new \DateTime();

        $cacheEntry = Cache::where('key', $key)
                            ->where('store_until', '>', $currentDateTime)
                            ->first();

        if ($cacheEntry) {
            return $cacheEntry->stash;
        }

        return null;
    }

}