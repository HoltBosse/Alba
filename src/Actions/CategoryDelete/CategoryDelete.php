<?php
namespace HoltBosse\Alba\Actions\CategoryDelete;

Use HoltBosse\Alba\Core\Actions;
Use HoltBosse\DB\DB;

class CategoryDelete extends Actions {

    public function display(): void {
        $affectedCategoryDetails = DB::fetch("SELECT * FROM categories WHERE id=?", $this->options->affected_category);

        $this->render_row(null, "Deleted Category: $affectedCategoryDetails->title");
    }
}