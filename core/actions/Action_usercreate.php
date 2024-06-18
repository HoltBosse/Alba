<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_usercreate extends Actions {

    public function display() {
        $affectedUserDetails = DB::fetch("SELECT * FROM users WHERE id=?", $this->options->affected_user);

        $url = null;
        if($affectedUserDetails->state>0) {
            $url = "/admin/users/edit/" . $affectedUserDetails->id;
        }

        $this->render_row($url, "Created User $affectedUserDetails->username ($affectedUserDetails->email)", false);
    }
}