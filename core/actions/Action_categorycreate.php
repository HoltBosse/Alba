<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_categorycreate extends Actions {

    public function display() {
        $affectedCategoryDetails = DB::fetch("SELECT * FROM categories WHERE id=?", $this->options->affected_category);

        $url = null;
        if($affectedCategoryDetails->state>0) {
            $url = "/admin/categories/edit/" . $affectedCategoryDetails->id;
        }

        $this->render_row($url, "Created Category: $affectedCategoryDetails->title");
    }
}