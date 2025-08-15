<?php

Use HoltBosse\Alba\Core\{CMS, Content, Controller, Hook};

// get data for view/page combination
//CMS::pprint_r (CMS::Instance()->page);

// config for this view has name: menutag and a value
$view_config = CMS::Instance()->page->view_configuration_object;
$content_id = Content::get_config_value ($view_config, 'content_id');

$content_item = Content::get_all_content(false, CMS::Instance()->page->content_type, $content_id)[0]; // false 1st param is ordering field
if ($content_item->state>0) {
	$markup = $content_item->markup;
	Hook::execute_hook_actions('before_content_item_render', CMS::Instance()->page->content_type, $content_id, $content_item->title);
}
else {
	$markup = "<h5 class='warning'>Content no longer available</h5>";
}

