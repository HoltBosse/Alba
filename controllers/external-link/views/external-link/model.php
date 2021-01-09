<?php
defined('CMSPATH') or die; // prevent unauthorized access

$view_config = CMS::Instance()->page->view_configuration_object;
$content_id = Content::get_config_value ($view_config, 'content_id');

$content_item = Content::get_all_content(false, CMS::Instance()->page->content_type, $content_id)[0]; // false 1st param is ordering field
if ($content_item->state>0) {
	$markup = $content_item->f_markup;
}
else {
	$markup = "<h5 class='warning'>Content no longer available</h5>";
}

