<?php
namespace HoltBosse\Alba\Actions\PageDelete;

use HoltBosse\Alba\Core\Actions;
use HoltBosse\DB\DB;

class PageDelete extends Actions {

    public function display() {
        $affectedPageDetails = DB::fetch("SELECT * FROM pages WHERE id=?", $this->options->affected_page);

        $this->render_row(null, "Deleted Page: $affectedPageDetails->title");
    }
}