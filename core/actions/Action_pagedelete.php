<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_pagedelete extends Actions {

    public function display() {
        $affectedPageDetails = DB::fetch("SELECT * FROM pages WHERE id=?", $this->options->affected_page);

        $this->render_row(null, "Deleted Page: $affectedPageDetails->title");
    }
}