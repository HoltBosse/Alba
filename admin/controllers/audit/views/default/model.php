<?php
defined('CMSPATH') or die; // prevent unauthorized access

//TODO: glob /core/actions, and limit this query to the support actions type. also add pagination
$results = DB::fetchall("SELECT * FROM user_actions ORDER BY date DESC");

//CMS::pprint_r($results);