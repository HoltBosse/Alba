<?php
defined('CMSPATH') or die; // prevent unauthorized access

ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned

if (CMS::Instance()->user->username=="guest") {
	CMS::Instance()->queue_message("Must be logged in to upload media",Config::$uripath . '/admin');
}

$uploaded_files_array = $_FILES['file-upload'];
$directory = CMSPATH . '/images/upload';
$uploaded_files = [];

// upload images to directory
foreach ($_FILES["file-upload"]["error"] as $key => $error) {
    if ($error == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES["file-upload"]["tmp_name"][$key];
        // basename() may prevent filesystem traversal attacks;
        // further validation/sanitation of the filename may be appropriate
        $name = basename($_FILES["file-upload"]["name"][$key]);
		move_uploaded_file($tmp_name, $directory . "/" . $name);
		$uploaded_files[] = $name;
    }
}
$uploaded_files = implode(",",$uploaded_files);


// return json to javascript uploaded and
// on success go to /admin/images/discover for processing

echo '{"success":1,"msg":"Images uploaded","files","'.$uploaded_files.'"}';
exit(0);
