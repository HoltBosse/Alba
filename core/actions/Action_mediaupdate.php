<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_mediaupdate extends Actions {

    public function display() {
        $affectedMediaDetails = DB::fetch("SELECT * FROM media WHERE id=?", $this->options->affected_media);

        $this->render_row(null, "Updated Media: $affectedMediaDetails->title");
    }
}