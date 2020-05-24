<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

$all_content_types = Content::get_all_content_types();

// handle custom optional listing on content specific 'all' view

$content_list_fields = [];
$custom_fields = false;


