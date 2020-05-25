<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

$all_content_types = Content::get_all_content_types();

$new_content_type_form = new Form(ADMINPATH . "/controllers/content/views/new/new_content_type_form.json");

function make_alias($string) {
	$string = filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
	$string = CMS::Instance()->stringURLSafe($string);
	return $string;
}

$new_content_type_form->set_from_submit();
if ($new_content_type_form->is_submitted()) {
	echo "<h1>got data!</h1>";
	if ($new_content_type_form->validate()) {
		
		$title = $new_content_type_form->get_field_by_name('title')->default;
		$description = $new_content_type_form->get_field_by_name('description')->default;
		$defaultviewtitle = $new_content_type_form->get_field_by_name('defaultviewtitle')->default;
		$defaultviewdescription = $new_content_type_form->get_field_by_name('defaultviewdescription')->default;
		$foldername = make_alias($title);
		$defaultviewfoldername = make_alias($defaultviewtitle);
		CMS::pprint_r ($title );
		CMS::pprint_r ($description );
		CMS::pprint_r ($defaultviewtitle);
		CMS::pprint_r ($defaultviewdescription);
		CMS::pprint_r ($foldername);
		CMS::pprint_r ($defaultviewfoldername);
		// TODO: check for folder existing with name $foldername in cmspath / controllers /
		// then create folder - if done
		// then create files, view folde, view files
		// then reload content page and allow auto-installer to do its thing :)
	}
	else {
		echo "<h2>It's INVALID!</h2>";
	}
}


