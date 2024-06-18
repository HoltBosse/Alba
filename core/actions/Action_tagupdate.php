<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_tagupdate extends Actions {

    public function display() {
        $affectedTagDetails = DB::fetch("SELECT * FROM tags WHERE id=?", $this->options->affected_tag);

        $url = null;
        if($affectedTagDetails->state>0) {
            $url = "/admin/tags/edit/" . $affectedTagDetails->id;
        }

        $this->render_row($url, "Updated Tag: $affectedTagDetails->title");
    }
}