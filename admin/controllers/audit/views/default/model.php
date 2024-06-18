<?php
defined('CMSPATH') or die; // prevent unauthorized access

$results = DB::fetchall("SELECT * FROM user_actions ORDER BY date DESC");

CMS::pprint_r($results);