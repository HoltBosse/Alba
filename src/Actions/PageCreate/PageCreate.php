<?php
namespace HoltBosse\Alba\Actions\PageCreate;

use HoltBosse\Alba\Core\Actions;
use HoltBosse\DB\DB;

class PageCreate extends Actions {

    public function display() {
        $affectedPageDetails = DB::fetch("SELECT * FROM pages WHERE id=?", $this->options->affected_page);

        $this->render_row(null, "Created Page: $affectedPageDetails->title");
    }
}