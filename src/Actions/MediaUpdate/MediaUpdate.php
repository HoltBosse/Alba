<?php
namespace HoltBosse\Alba\Actions\MediaUpdate;

use HoltBosse\Alba\Core\Actions;
use HoltBosse\DB\DB;

class MediaUpdate extends Actions {

    public function display(): void {
        $affectedMediaDetails = DB::fetch("SELECT * FROM media WHERE id=?", $this->options->affected_media);

        $this->render_row(null, "Updated Media: $affectedMediaDetails->title");
    }
}