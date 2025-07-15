<?php
namespace HoltBosse\Alba\Actions\MediaDelete;

use HoltBosse\Alba\Core\Actions;
use HoltBosse\DB\DB;

class MediaDelete extends Actions {

    public function display() {
        $affectedMediaDetails = DB::fetch("SELECT * FROM media WHERE id=?", $this->options->affected_media);

        $this->render_row(null, "Deleted Media: $affectedMediaDetails->title");
    }
}