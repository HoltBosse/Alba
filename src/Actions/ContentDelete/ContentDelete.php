<?php
namespace HoltBosse\Alba\Actions\ContentDelete;

use HoltBosse\Alba\Core\Actions;
use HoltBosse\Alba\Core\Content;
use HoltBosse\DB\DB;

class ContentDelete extends Actions {

    public function display() {
        $contentTableName = Content::get_table_name_for_content_type($this->options->content_type);
        $contentDetails = DB::fetch("SELECT * FROM `{$contentTableName}` WHERE id=?", $this->options->content_id);

        $contentTypeLabel = DB::fetch("SELECT * FROM content_types WHERE id=?", $contentDetails->content_type);

        $this->render_row(null, "Deleted \"$contentTypeLabel->title\" Content: " . $contentDetails->title);
    }
}