<?php
namespace HoltBosse\Alba\Actions\TagDelete;

use HoltBosse\DB\DB;
use HoltBosse\Alba\Core\Actions;

class TagDelete extends Actions {

    public function display(): void {
        $affectedTagDetails = DB::fetch("SELECT * FROM tags WHERE id=?", $this->options->affected_tag);

        $this->render_row(null, "Deleted Tag: $affectedTagDetails->title");
    }
}