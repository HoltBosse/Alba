<?php
namespace HoltBosse\Alba\Actions\ContentUpdate;

use HoltBosse\Alba\Core\Actions;
use HoltBosse\Alba\Core\Content;
use HoltBosse\DB\DB;
use HoltBosse\Alba\Actions\ContentCreate\ContentCreate;

class ContentUpdate extends ContentCreate {

    public function display() {
        $contentTableName = Content::get_table_name_for_content_type($this->options->content_type);
        $contentDetails = DB::fetch("SELECT * FROM `{$contentTableName}` WHERE id=?", $this->options->content_id);

        $url = null;
        if($contentDetails->state>0) {
            $url = "/admin/content/edit/" . $contentDetails->id . "/" . $contentDetails->content_type;
        }

        $contentTypeLabel = DB::fetch("SELECT * FROM content_types WHERE id=?", $contentDetails->content_type);

        $this->render_row($url, "Updated \"$contentTypeLabel->title\" Content: " . $contentDetails->title);
    }
}