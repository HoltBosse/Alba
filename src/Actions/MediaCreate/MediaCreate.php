<?php
namespace HoltBosse\Alba\Actions\MediaCreate;

use HoltBosse\Alba\Core\Actions;
use HoltBosse\DB\DB;

class MediaCreate extends Actions {

    public function display(): void {
        $affectedMediaDetails = DB::fetch("SELECT * FROM media WHERE id=?", $this->options->affected_media);

        $this->render_row(null, "Created Media: $affectedMediaDetails->title");
    }
}