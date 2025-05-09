<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

if(sizeof($segments) < 3 || !is_numeric($segments[2])) {
    CMS::Instance()->queue_message('Failed to load page for editing', 'danger',Config::uripath().'/admin/pages');
}

$pageInfo = DB::fetch("SELECT * FROM pages WHERE id=?", $segments[2]);
if($pageInfo->template==0) {
    $template = DB::fetch("SELECT * FROM templates WHERE is_default=1");
} else {
    $template = DB::fetch("SELECT * FROM templates WHERE id=?", $pageInfo->template);
}

$layout = json_decode(file_get_contents(CMSPATH . "/templates/{$template->folder}/positions.json"));