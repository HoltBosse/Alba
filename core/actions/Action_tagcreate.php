<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_tagcreate extends Actions {

    public function display() {
        $affectedTagDetails = DB::fetch("SELECT * FROM tags WHERE id=?", $this->options->affected_tag);

        $url = null;
        if($affectedTagDetails->state>0) {
            $url = "/admin/tags/edit/" . $affectedTagDetails->id;
        }

        $this->render_row($url, "Created Tag: $affectedTagDetails->title");
    }
}