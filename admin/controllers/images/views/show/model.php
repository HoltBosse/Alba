<?php
defined('CMSPATH') or die; // prevent unauthorized access

$query = "select * from media where mimetype in ('image/jpeg','image/png')";
$all_images = CMS::Instance()->pdo->query($query)->fetchAll();

$image_tags = Content::get_applicable_tags ("-1");

$filter = Input::getvar('filter','STRING');

$max_upload_size = File::get_max_upload_size();
$max_upload_size_bytes = File::get_max_upload_size_bytes();

