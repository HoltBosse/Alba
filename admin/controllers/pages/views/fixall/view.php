<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<h1 class='is-1 title'>Fixing Pages</h1>

<?php foreach ($pages as $page) {
	//CMS::pprint_r ($page);
	//$content_view_configuration = json_decode($page->content_view_configuration);
	//$page_options = json_decode($page->page_options);
	//CMS::pprint_r ($content_view_configuration);
	//CMS::pprint_r ($page_options);
	$good_config = fix_bad_serialization($page->content_view_configuration);
	$good_options = fix_bad_serialization($page->page_options);
	DB::exec('update pages set content_view_configuration=?, page_options=? where id=?', [$good_config, $good_options, $page->id]);
	//CMS::pprint_r ($good_config);
	//CMS::pprint_r ($good_options);
	$saved = DB::fetch('select * from pages where id=?', $page->id);
	//CMS::pprint_r ($saved);
	//echo "<hr>";
}?>

<p>All page configs and options updated to better serialized JSON</p>
