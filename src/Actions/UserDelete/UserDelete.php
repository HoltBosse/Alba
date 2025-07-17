<?php
namespace HoltBosse\Alba\Actions\UserDelete;

use HoltBosse\DB\DB;
use HoltBosse\Alba\Core\Actions;
use HoltBosse\Form\Input;

class UserDelete extends Actions {

    public function display() {
        $affectedUserDetails = DB::fetch("SELECT * FROM users WHERE id=?", $this->options->affected_user);

        $this->render_row(null, "Deleted User " . Input::stringHtmlSafe($affectedUserDetails->username) . " ($affectedUserDetails->email)");
    }
}