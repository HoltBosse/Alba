<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_contentcreate extends Actions {

    public function display() {
        $contantTableName = Content::get_table_name_for_content_type($this->options->content_type);
        $contentDetails = DB::fetch("SELECT * FROM `{$contantTableName}` WHERE id=?", $this->options->content_id);

        $url = null;
        if($contentDetails->state>0) {
            $url = "/admin/content/edit/" . $contentDetails->id . "/" . $contentDetails->content_type;
        }

        $contentTypeLabel = DB::fetch("SELECT * FROM content_types WHERE id=?", $contentDetails->content_type);

        $this->render_row($url, "Created \"$contentTypeLabel->title\" Content: " . $contentDetails->title, false);
    }
}