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
	$blog_content_item = $blog_content_items[0];
	CMS::Instance()->page->title = $blog_content_item->title; // set page title - this is working now
	// TODO: add seo/OG fields to blog item and update page header for cms render_head function
	// override page->form options values with og data from this content item
	//CMS::pprint_r ($blog_content_item);
	if (isset ($blog_content_item->f_og_title)) {
		CMS::Instance()->page->set_page_option_value('og_title', $blog_content_item->f_og_title);
	}
	if (isset ($blog_content_item->f_og_image)) {
		CMS::Instance()->page->set_page_option_value('og_image', $blog_content_item->f_og_image);
	}
	if (isset ($blog_content_item->f_og_description)) {
		CMS::Instance()->page->set_page_option_value('og_description', $blog_content_item->f_og_description);
	}
	if (isset ($blog_content_item->f_og_keywords)) {
		CMS::Instance()->page->set_page_option_value('og_keywords', $blog_content_item->f_og_keywords);
	}
}
else {
	// blog listing
	$blog_content_items = Content::get_all_content($order_by="start", 1, false, $tag_id); // order, type filter (1=basic article), specific id, tag id
}





