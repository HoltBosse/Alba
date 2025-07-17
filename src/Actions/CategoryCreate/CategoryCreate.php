<?php
namespace HoltBosse\Alba\Actions\CategoryCreate;

Use HoltBosse\Alba\Core\Actions;
Use HoltBosse\DB\DB;

class CategoryCreate extends Actions {

    public function display() {
        $affectedCategoryDetails = DB::fetch("SELECT * FROM categories WHERE id=?", $this->options->affected_category);

        $url = null;
        if($affectedCategoryDetails->state>0) {
            $url = "/admin/categories/edit/" . $affectedCategoryDetails->id;
        }

        $this->render_row($url, "Created Category: $affectedCategoryDetails->title");
    }
}