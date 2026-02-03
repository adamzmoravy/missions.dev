<?php

namespace Karolina\Stats;

use Karolina\Database\Table\Stat as Stat;

Class Record {

    public function getPing ($day) {

        $stat = Stat::where('day', $day)->first();

        if (!$stat || !$stat->ping) {
            return [];
        }

        $ping = json_decode($stat->ping, true);
        if (!is_array($ping)) {
            return [];
        } 

        return $ping;
    }

    public function addPing ($userId = null) {

        $today = date('Y-m-d');

        $stat = Stat::firstOrNew([
            'day' => $today
        ]);

        if (!$stat->ping) {

            $stat->ping = "{}";
        }

        if ($userId === null) {
            return;
        }

        $ping = json_decode($stat->ping, true);
        if (!is_array($ping)) {
            $ping = [];
        } 

        $key = (string) $userId;
        $ping[$key] = (isset($ping[$key]) ? (int) $ping[$key] + 1 : 1);

        $stat->ping = json_encode($ping);


        $stat->save();
    }

}