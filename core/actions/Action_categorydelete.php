<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_categorydelete extends Actions {

    public function display() {
        $affectedCategoryDetails = DB::fetch("SELECT * FROM categories WHERE id=?", $this->options->affected_category);

        $this->render_row(null, "Deleted Category: $affectedCategoryDetails->title");
    }
}