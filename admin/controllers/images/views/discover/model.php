<?php
defined('CMSPATH') or die; // prevent unauthorized access

if (CMS::Instance()->user->username=="guest") {
	CMS::Instance()->queue_message("Must be logged in to upload media",Config::$uripath.'/admin');
}

$directory = CMSPATH . '/images/upload';
$all_image_files = array_diff(scandir($directory), array('..', '.','index.html'));
$all_image_files = array_values($all_image_files); // re-index array from 0 if .. and . are removed above
//CMS::pprint_r ($all_image_files);

// first check to see if files have been submitted for processing from form

$titles_array = CMS::getvar('title','ARRAYOFSTRING');
$alts_array = CMS::getvar('alt','ARRAYOFSTRING');
$web_friendly_array = CMS::getvar('web_friendly','ARRAYOFINT');


function make_thumb($src, $dest, $desired_width, $file) {
	// TODO: move to Image class (sub-file class)
	/* read the source image */
	if ($file->mimetype=='image/jpeg') {
		$source_image = imagecreatefromjpeg($src);
	}
	else {
		$source_image = imagecreatefrompng($src);
	}
	$width = imagesx($source_image);
	$height = imagesy($source_image);
	/* find the "desired height" of this thumbnail, relative to the desired width  */
	$desired_height = floor($height * ($desired_width / $width));
	/* create a new, "virtual" image */
	$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
	/* copy source image at a resized size */
	imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
	/* create the physical thumbnail image to its destination */
	if ($file->mimetype=='image/jpeg') {
		imagejpeg($virtual_image, $dest);
	}
	else {
		imagepng($virtual_image, $dest);
	}
}


if ($titles_array) {

/* 	CMS::pprint_r ($titles_array);
	CMS::pprint_r ($alts_array);
	CMS::pprint_r ($web_friendly_array);
	exit(0); */

	$processed=[];
	$failed=[];
	$pdo = CMS::Instance()->pdo;
	for ($n=0; $n<sizeof($titles_array); $n++) {
		if ($titles_array[$n] && $alts_array[$n]) {
			if (image_in_db($all_image_files[$n]) ) {
				CMS::Instance()->queue_message('File ' . $all_image_files[$n] . ' already exists in DB. Deleting upload file.','warning');
			}
			else {
				$title = $titles_array[$n];
				$alt = $alts_array[$n];
				$web_friendly = $web_friendly_array[$n];
				// TODO: further filtering on title and alt just in case
				$file = new File(CMSPATH . '/images/upload/' . $all_image_files[$n]);
				if ($file->width > 1920 && $web_friendly[$n]) {
					$file->original_width = $file->width;
					$file->recalc_height(1920);
				}
				$query = "insert into media (width, height, title, alt, filename, mimetype) values (?,?,?,?,?,?)";
				$stmt = $pdo->prepare($query);
				$in_db_ok = $stmt->execute(array($file->width, $file->height, $title, $alt, $file->filename, $file->mimetype));
				if ($in_db_ok) {
					
					$src = CMSPATH . '/images/upload/' . $file->filename;
					$dest = CMSPATH . '/images/processed/' . $file->filename;
					// make web friendly if required
					if ($file->original_width > 1920 && $web_friendly[$n]) {
						make_thumb($src, $dest, 1920, $file);
						unlink($src);
					}
					else {
						// move original file from upload to processed
						rename ($src, $dest);
					}
					$processed[] = $all_image_files[$n];

				}
				else {
					$failed[] = $all_image_files[$n];
				}
			}
		}
		else {
			$failed[] = $all_image_files[$n];
		}
	}
	if ($failed) {
		CMS::Instance()->queue_message('Skipped ' . sizeof($failed) . ' FTP images - missing title/alt.','warning');
	}
	CMS::Instance()->queue_message('FTP Images Import Complete (' . sizeof($processed) . ' images)','success',Config::$uripath.'/admin/images/discover');
}



function image_in_db($filename) {
	$query = "select filename from media where filename=?";
	$stmt = CMS::Instance()->pdo->prepare($query);
	$stmt->execute(array($filename));
	$match = $stmt->fetch();
	//CMS::pprint_r ($match);
	//if (property_exists($match,'filename')) {
	if ($match) {
		return true;
	}
	else {
		return false;
	}
}



// TODO: add to file class - upload function
if(!function_exists('mime_content_type')) {
	CMS::Instance()->queue_message('Function "mime_content_type" is not available. Cannot upload files safely.','danger',Config::$uripath.'/admin');
}

