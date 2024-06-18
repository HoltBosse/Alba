<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Actions {
    public static function add_action($type, $action, $userid=0) {
        if ($userid==0) {$userid=CMS::Instance()->user->id;}
        if (!is_numeric($userid)) {$userid=0;} //triple check - cms can be dumb when a user is timed out

        DB::exec("INSERT INTO user_actions (userid, type, json) VALUES (?, ?, ?)", [$userid, $type, json_encode($action)]);
    }
}