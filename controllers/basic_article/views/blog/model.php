<?php
defined('CMSPATH') or die; // prevent unauthorized access

// get data for view/page combination
//CMS::pprint_r (CMS::Instance()->page);

// config for this view has name: menutag and a value
$view_config = CMS::Instance()->page->view_configuration_object;
$tag_id = Content::get_config_value ($view_config, 'blogtag');
if (CMS::Instance()->uri_segments) {
	// could be a single blog or tag/bydate/etc... 
	if (CMS::Instance()->uri_segments[0]=='tag') {
		if (sizeof(CMS::Instance()->uri_segments)==2) {
			$filter_tag = DB::fetch('select * from tags where alias=?',[CMS::Instance()->uri_segments[1]]);
			if ($filter_tag) {
				$blog_content_items = Content::get_all_content($order_by="id", 1, null, $filter_tag->id); // null is specific content id
			}
			else {
				CMS::show_error('Tag alias not found');
			}
		}
		else {
			CMS::show_error('No tag id passed to controller');
		}
	}
	else {
		// assume single blog entry for now - future work could include by dates etc
		// depending on url parameter
		$blog_alias = CMS::Instance()->uri_segments[0];
		$blog = new Content();
		$blog->load_from_alias($blog_alias);
		$blog_content_items = Content::get_all_content($order_by="id", 1, $blog->id); // order, type filter (1=basic article), specific id, tag id
		$single_blog_content_item = $blog_content_items[0];
		CMS::Instance()->page->title = $blog_content_item->title; // set page title - this is working now
		// TODO: add seo/OG fields to blog item and update page header for cms render_head function
		// override page->form options values with og data from this content item
		//CMS::pprint_r ($blog_content_item);
		if (isset ($single_blog_content_item->f_og_title)) {
			CMS::Instance()->page->set_page_option_value('og_title', $single_blog_content_item->f_og_title);
		}
		if (isset ($single_blog_content_item->f_og_image)) {
			CMS::Instance()->page->set_page_option_value('og_image', $single_blog_content_item->f_og_image);
		}
		if (isset ($single_blog_content_item->f_og_description)) {
			CMS::Instance()->page->set_page_option_value('og_description', $single_blog_content_item->f_og_description);
		}
		if (isset ($single_blog_content_item->f_og_keywords)) {
			CMS::Instance()->page->set_page_option_value('og_keywords', $single_blog_content_item->f_og_keywords);
		}
	}
}
else {
	// all blog listing
	$blog_content_items = Content::get_all_content($order_by="start", 1, false, $tag_id); // order, type filter (1=basic article), specific id, tag id
}





