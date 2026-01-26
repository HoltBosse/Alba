<?php
namespace HoltBosse\Alba\Actions\PageUpdate;

use HoltBosse\DB\DB;
use HoltBosse\Alba\Core\Actions;
use HoltBosse\Form\Input;

class PageUpdate extends Actions {

    public function display() {
        $affectedPageDetails = DB::fetch("SELECT * FROM pages WHERE id=?", $this->options->affected_page);

        $this->render_row(null, "Updated Page: " . Input::stringHtmlSafe($affectedPageDetails->title));
    }
}