<?php
namespace HoltBosse\Alba\Actions\TagCreate;

use HoltBosse\DB\DB;
use HoltBosse\Alba\Core\Actions;

class TagCreate extends Actions {

    public function display(): void {
        $affectedTagDetails = DB::fetch("SELECT * FROM tags WHERE id=?", $this->options->affected_tag);

        $url = null;
        if($affectedTagDetails->state>0) {
            $url = "/admin/tags/edit/" . $affectedTagDetails->id;
        }

        $this->render_row($url, "Created Tag: $affectedTagDetails->title");
    }
}