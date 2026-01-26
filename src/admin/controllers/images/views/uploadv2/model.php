<?php

Use HoltBosse\Alba\Core\{CMS, File, Actions, Image};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;
Use Respect\Validation\Validator as v;

ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned
ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned

if (CMS::Instance()->user->username=="guest") {
	CMS::Instance()->queue_message("Must be logged in to upload media",$_ENV["uripath"] . '/admin');
}

header('Content-Type: application/json; charset=utf-8');

$uploaded_files_array = $_FILES['file-upload'];
$alts = Input::getvar('alt',v::arrayType()->each(v::StringVal()));
$titles = Input::getvar('title',v::arrayType()->each(v::StringVal()));
$tags = Input::getvar('tags',v::arrayType()->each(v::StringVal()));
$web_friendly_array = Input::getvar('web_friendly',v::arrayType()->each(v::intVal()));
$directory = $_ENV["images_directory"] . '/processed';

$result = Image::processUploadedFiles(
	$uploaded_files_array,
	$alts,
	$titles,
	$tags,
	$web_friendly_array,
	$directory
);

CMS::Instance()->queue_message('Images uploaded','success');

echo json_encode($result);
exit(0);
