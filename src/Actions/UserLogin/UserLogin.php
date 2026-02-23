<?php
namespace HoltBosse\Alba\Actions\UserLogin;

use HoltBosse\DB\DB;
use HoltBosse\Alba\Core\Actions;
use HoltBosse\Form\Input;

class UserLogin extends Actions {

    public function display(): void {
        $userDetails = DB::fetch("SELECT * FROM users WHERE id=?", $this->options->user);

        $url = null;
        if($userDetails->state>0) {
            $url = "/admin/users/edit/" . $userDetails->id;
        }

        $this->render_row($url, "User " . Input::stringHtmlSafe($userDetails->username) . " ($userDetails->email) logged in");
    }
}