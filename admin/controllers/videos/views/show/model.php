<?php
defined('CMSPATH') or die; // prevent unauthorized access

$videos = new Videos();

$searchtext = Input::getvar('searchtext','TEXT',null);
$page = Input::getvar('page','TEXT',null);

if ($searchtext) {
    $all_videos = $videos->search_all_videos($searchtext);
} elseif($page) {
    //using base64 because encoding and decoding search page url is producing different results
    $all_videos = $videos->get_page(urldecode(base64_decode($page)));
} else {
    $all_videos = $videos->get_all_videos();
    //CMS::pprint_r($all_videos->data[0]);
}

echo "<xmp>"; print_r($all_videos); echo "</xmp>";

$image_tags = Content::get_applicable_tags ("-1");

$filter = Input::getvar('filter','STRING');
$autoclose = Input::getvar('autoclose','STRING');

$max_upload_size = File::get_max_upload_size();
$max_upload_size_bytes = File::get_max_upload_size_bytes();

