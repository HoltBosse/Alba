<?php
defined('CMSPATH') or die; // prevent unauthorized access

$query = "select * from media where mimetype in ('image/jpeg','image/png')";
$all_images = CMS::Instance()->pdo->query($query)->fetchAll();

$image_tags = Content::get_applicable_tags ("-1");

$filter = Input::getvar('filter','STRING');

