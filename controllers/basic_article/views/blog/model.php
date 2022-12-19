<?php
defined('CMSPATH') or die; // prevent unauthorized access

// get data for view/page combination
//CMS::pprint_r (CMS::Instance()->page);

// config for this view has name: menutag and a value
$view_config = CMS::Instance()->page->view_configuration_object;
$tag_id = Content::get_config_value ($view_config, 'blogtag');
$articles_per_page = Content::get_config_value ($view_config, 'articles_per_page') ?? 999;
$cur_page = Input::getvar('page') ? Input::getvar('page') : 1; // always make sure we get page number for blog

//work around for content_search limitation over old get_all_content
$basic_article_form = JSON::load_obj_from_file(CMSPATH . '/controllers/basic_article/custom_fields.json');
foreach($basic_article_form->fields as $field) {
	$list_fields[] = $field->name;
}


if (CMS::Instance()->uri_segments) {
	// could be a single blog or tag/bydate/etc... 
	if (CMS::Instance()->uri_segments[0]=='tag') {
		if (sizeof(CMS::Instance()->uri_segments)==2) {
			$filter_tag = DB::fetch('select * from tags where alias=?',[CMS::Instance()->uri_segments[1]]);
			if ($filter_tag) {
				$cs = new Content_Search();
				$cs->order_by = "start";
				$cs->type_filter = 1;
				$cs->tags = [$filter_tag->id];
				$cs->published_only = true;
				$cs->list_fields = $list_fields;
				$blog_content_items = $cs->exec(); // null is specific content id
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
		$blog_found = $blog->load_from_alias($blog_alias);
		if (!$blog_found || $blog->state<1) {
			CMS::raise_404();
		}
		$cs = new Content_Search();
		$cs->order_by = "start";
		$cs->type_filter = 1;
		$cs->filters = ["id" => $blog->id];
		$cs->published_only = true;
		$cs->list_fields = $list_fields;
		$blog_content_items = $cs->exec(); 
		// order, type filter (1=basic article), specific id, tag id, published_only, list_fields, ignore_fields
		
		if ($blog_content_items) {
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
	}
}
else {
	// all blog listing - ignore markup field - not needed for listing view, potentially save lots of data that
	// won't be shown in view anyway
	$cs = new Content_Search();
	$cs->order_by = "start";
	$cs->type_filter = 1;
	$cs->tags = [$tag_id];
	$cs->published_only = true;
	$cs->ignore_fields = ['markup'];
	$cs->page = $cur_page;
	$cs->$page_size = $articles_per_page;
	$cs->list_fields = $list_fields;
	$blog_content_items = $cs->exec();
	// order, type filter (1=basic article), specific id, tag id, published_only, list_fields, ignore_fields
	$show_next = false;
	$show_prev = false;
	if (sizeof($blog_content_items)==$articles_per_page) {
		// potentially more
		$show_next = true;
	}
	if ($cur_page>1) {
		$show_prev = true;
	}
}





