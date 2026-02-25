<?php

Use HoltBosse\Alba\Core\{CMS, Content, Controller};
Use HoltBosse\DB\DB;

// get data for view/page combination
//CMS::pprint_r (CMS::Instance()->page);

// config for this view has name: menutag and a value
$view_config = CMS::Instance()->page->view_configuration_object;
$content_id = Content::get_config_value ($view_config, 'content_id');

$content_item = DB::fetch("SELECT * FROM controller_basic_html WHERE id=?", $content_id);
if ($content_item->state>0) {
	$markup = $content_item->markup;
}
else {
	$markup = "<h5 class='warning'>Content no longer available</h5>";
}

