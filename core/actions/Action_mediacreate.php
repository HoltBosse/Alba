<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_mediacreate extends Actions {

    public function display() {
        $affectedMediaDetails = DB::fetch("SELECT * FROM media WHERE id=?", $this->options->affected_media);

        $this->render_row(null, "Created Media: $affectedMediaDetails->title");
    }
}