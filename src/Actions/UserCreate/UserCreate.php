<?php
namespace HoltBosse\Alba\Actions\UserCreate;

use HoltBosse\DB\DB;
use HoltBosse\Alba\Core\Actions;
use HoltBosse\Form\Input;

class UserCreate extends Actions {

    public function display(): void {
        $affectedUserDetails = DB::fetch("SELECT * FROM users WHERE id=?", $this->options->affected_user);

        $url = null;
        if($affectedUserDetails->state>0) {
            $url = "/admin/users/edit/" . $affectedUserDetails->id;
        }

        $this->render_row($url, "Created User " . Input::stringHtmlSafe($affectedUserDetails->username) . " ($affectedUserDetails->email)");
    }
}