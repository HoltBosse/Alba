<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_contentdelete extends Actions {

    public function display() {
        $contantTableName = Content::get_table_name_for_content_type($this->options->content_type);
        $contentDetails = DB::fetch("SELECT * FROM `{$contantTableName}` WHERE id=?", $this->options->content_id);

        $contentTypeLabel = DB::fetch("SELECT * FROM content_types WHERE id=?", $contentDetails->content_type);

        $this->render_row(null, "Deleted \"$contentTypeLabel->title\" Content: " . $contentDetails->title, false);
    }
}