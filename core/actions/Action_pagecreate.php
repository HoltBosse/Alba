<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_pagecreate extends Actions {

    public function display() {
        $affectedPageDetails = DB::fetch("SELECT * FROM pages WHERE id=?", $this->options->affected_page);

        $this->render_row(null, "Created Page: $affectedPageDetails->title");
    }
}