<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_userdelete extends Actions {

    public function display() {
        $affectedUserDetails = DB::fetch("SELECT * FROM users WHERE id=?", $this->options->affected_user);

        $this->render_row(null, "Deleted User $affectedUserDetails->username ($affectedUserDetails->email)", false);
    }
}