<?php
defined('CMSPATH') or die; // prevent unauthorized access

// get data for view/page combination
//CMS::pprint_r (CMS::Instance()->page);

// config for this view has name: menutag and a value
$view_config = CMS::Instance()->page->view_configuration_object;
$tag_id = Content::get_config_value ($view_config, 'blogtag');
if (CMS::Instance()->uri_segments) {
	// single blog entry
	$blog_alias = CMS::Instance()->uri_segments[0];
	$blog = new Content();
	$blog->load_from_alias($blog_alias);
	$blog_content_items = Content::get_all_content($order_by="id", 1, $blog->id); // order, type filter (1=basic article), specific id, tag id
	CMS::Instance()->page->title = $blog_content_items[0]->title; // set page title - NOT WORKING, HEAD IS RENDERED ALREADY BY THIS TIME - TODO: fix
	// TODO: add seo/OG fields to blog item and update page header for cms render_head function
}
else {
	// blog listing
	$blog_content_items = Content::get_all_content($order_by="start", 1, false, $tag_id); // order, type filter (1=basic article), specific id, tag id
}





