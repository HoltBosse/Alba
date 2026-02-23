<?php
namespace HoltBosse\Alba\Actions\CategoryUpdate;

use HoltBosse\Alba\Core\Actions;
use HoltBosse\DB\DB;

class CategoryUpdate extends Actions {

    public function display(): void {
        $affectedCategoryDetails = DB::fetch("SELECT * FROM categories WHERE id=?", $this->options->affected_category);

        $url = null;
        if($affectedCategoryDetails->state>0) {
            $url = "/admin/categories/edit/" . $affectedCategoryDetails->id;
        }

        $this->render_row($url, "Updated Category: $affectedCategoryDetails->title");
    }
}