<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_tagdelete extends Actions {

    public function display() {
        $affectedTagDetails = DB::fetch("SELECT * FROM tags WHERE id=?", $this->options->affected_tag);

        $this->render_row(null, "Deleted Tag: $affectedTagDetails->title");
    }
}