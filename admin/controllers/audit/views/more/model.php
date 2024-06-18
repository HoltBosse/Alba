<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

$viewmore = DB::fetch("SELECT * FROM user_actions_details WHERE id=?", ($segments[2] ?? 0));

if(!$viewmore) {
    CMS::show_error("Details not found", 404);
}

CMS::pprint_r($viewmore);