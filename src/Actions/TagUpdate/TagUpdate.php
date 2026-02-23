<?php
namespace HoltBosse\Alba\Actions\TagUpdate;

use HoltBosse\DB\DB;
use HoltBosse\Alba\Core\Actions;

class TagUpdate extends Actions {

    public function display(): void {
        $affectedTagDetails = DB::fetch("SELECT * FROM tags WHERE id=?", $this->options->affected_tag);

        $url = null;
        if($affectedTagDetails->state>0) {
            $url = "/admin/tags/edit/" . $affectedTagDetails->id;
        }

        $this->render_row($url, "Updated Tag: $affectedTagDetails->title");
    }
}