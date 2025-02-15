<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

if (sizeof($segments)!==3) {
    CMS::show_error('Cannot determine content type to order');
}

$content_type = $segments[2];
if (!is_numeric($content_type)) {
    CMS::show_error('Invalid content type');
}

$content_table = Content::get_table_name_for_content_type($content_type);
$all_content = DB::fetchAll("SELECT * FROM $content_table ORDER BY ordering ASC, id ASC");