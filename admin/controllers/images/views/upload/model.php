<?php
defined('CMSPATH') or die; // prevent unauthorized access

ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned
ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned

if (CMS::Instance()->user->username=="guest") {
	CMS::Instance()->queue_message("Must be logged in to upload media",Config::uripath() . '/admin');
}

$uploaded_files_array = $_FILES['file-upload'];
$directory = CMSPATH . '/images/upload';
$uploaded_files = [];

function correctImageOrientation($filename) {
	if (function_exists('exif_read_data')) {
		$exif = exif_read_data($filename);
		if($exif && isset($exif['Orientation'])) {
			$orientation = $exif['Orientation'];
			/* if($orientation != 1 || true) { */
				$img = imagecreatefromjpeg($filename);
				$deg = 0;
				switch ($orientation) {
					case 3:
						$deg = 180;
						break;
					case 6:
						$deg = 270;
						break;
					case 8:
						$deg = 90;
						break;
				}
				if ($deg) {
					$img = imagerotate($img, $deg, 0);        
				}
				// then rewrite the rotated image back to the disk as $filename 
				imagejpeg($img, $filename, 75);
			/* } */ // if there is some rotation necessary
		} // if have the exif orientation info
	} // if function exists  
}

// upload images to directory
foreach ($_FILES["file-upload"]["error"] as $key => $error) {
    if ($error == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES["file-upload"]["tmp_name"][$key];
        // basename() may prevent filesystem traversal attacks;
        // further validation/sanitation of the filename may be appropriate
        $name = basename($_FILES["file-upload"]["name"][$key]);
		move_uploaded_file($tmp_name, $directory . "/" . $name);
        correctImageOrientation($directory . "/" . $name);
		$uploaded_files[] = $name;
    }
}
$uploaded_files = implode(",",$uploaded_files);


// return json to javascript uploaded and
// on success go to /admin/images/discover for processing

echo '{"success":1,"msg":"Images uploaded","files","'.$uploaded_files.'"}';
exit(0);
