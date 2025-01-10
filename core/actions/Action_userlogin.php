<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_userlogin extends Actions {

    public function display() {
        $userDetails = DB::fetch("SELECT * FROM users WHERE id=?", $this->options->user);

        $url = null;
        if($userDetails->state>0) {
            $url = "/admin/users/edit/" . $userDetails->id;
        }

        $this->render_row($url, "User " . Input::stringHtmlSafe($userDetails->username) . " ($userDetails->email) logged in");
    }
}