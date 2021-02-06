<?php
defined('CMSPATH') or die; // prevent unauthorized access

// api style controller - end output
ob_end_clean();


// router

$segments = CMS::Instance()->uri_segments;


function serve_file ($media_obj, $fullpath, $seconds_to_cache=31536000) {
	$seconds_to_cache = $seconds_to_cache;
	$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
	header("Expires: $ts");
	header("Pragma: cache");
	header("Cache-Control: max-age=$seconds_to_cache");
	// TODO: move to File class
	header("Content-type: " . $media_obj->mimetype);
	$fp = fopen($fullpath, 'rb');
	fpassthru($fp);
	exit(0);
}


function make_thumbOLD ($src, $dest, $desired_width, $quality=55) {
	// TODO: move to Image class (sub-file class)
	/* read the source image */
	$source_image = imagecreatefromjpeg($src);
	$width = imagesx($source_image);
	$height = imagesy($source_image);
	
	/* find the "desired height" of this thumbnail, relative to the desired width  */
	$desired_height = floor($height * ($desired_width / $width));
	
	/* create a new, "virtual" image */
	$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
	
	/* copy source image at a resized size */
	imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
	
	/* create the physical thumbnail image to its destination */
	imagejpeg($virtual_image, $dest, $quality);
}

function make_thumb ($src, $dest, $desired_width, $file, $quality=55) {
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
		imagejpeg($virtual_image, $dest, $quality);
	}
	else {
		imagepng($virtual_image, $dest);
	}
}

function get_image ($id) {
	// TODO: move to Image class (sub-file class)
	$stmt = CMS::Instance()->pdo->prepare('select * from media where id=?');
	$stmt->execute(array(CMS::Instance()->uri_segments[1])); // already tested to be number
	$stmt->execute();
	return $stmt->fetch();
}

if (sizeof($segments)==0) {
	//CMS::Instance()->queue_message('Unknown image id','danger',Config::$uripath.'/');
	echo "<h1>wtf - shouldn't get here :)</h1>";
}
if (sizeof($segments)==1) {
	//CMS::Instance()->queue_message('Unknown image id','danger',Config::$uripath.'/');
	//$view = 'show';
	
	echo "<h1>NO IMAGE GIVEN!</h1>";
	exit(0);
}
if (sizeof($segments)==2) {
	if (is_numeric($segments[1])) {
		CMS::log("call to Image Controller with single INT {$segments[1]}");
		// /images/INT
		$image = get_image ($segments[1]);
		if ($image) {
			$fullpath = CMSPATH . '/images/processed/' . $image->filename;
			serve_file ($image, $fullpath);
		}
		else {
			echo "<h1>No image found.</h1>";
		}
		exit(0);
	}
	exit(0);
}
if (sizeof($segments)==3) {
	if (is_numeric($segments[1])&&is_string($segments[2])) {
		$image = get_image ($segments[1]);
		if ($image) {
			$original_path = CMSPATH . "/images/processed/" . $image->filename;
			$param = $segments[2];
			$target_width = $image->width;
			$newsize_path = "";
			if ($param=="thumb") {	
				$newsize_path = CMSPATH . "/images/processed/thumb_" . $image->filename;
				if (!file_exists($newsize_path)) {
					CMS::log('Thumbnail generated for image ' . $image->filename);
					$target_width = 200;
					make_thumb($original_path, $newsize_path, $target_width, $image);
				}
				// serve existing file or new thumb if created above
				serve_file ($image, $newsize_path);
			}
			elseif ($param=="web") {	
				$original_path = CMSPATH . "/images/processed/" . $image->filename;
				$newsize_path = CMSPATH . "/images/processed/web_" . $image->filename;
				if ($image->width > 1920) {
					if (!file_exists($newsize_path)) {
						CMS::log('Web version generated for image ' . $image->filename);
						$target_width = 1920;
						make_thumb($original_path, $newsize_path, $target_width, $image);
					}
					// serve web friendly version
					serve_file ($image, $newsize_path); // exits script
				}
				else {
					// serve original, it's already web friendly
					serve_file ($image, $original_path); // exits script
				}
				// serve existing file or new thumb if created above
			}
			else {
				serve_file ($image, $original_path); // exits script
			}
		}
		else {
			echo "<h1>No image found.</h1>";
			exit(0);
		}
	}
}
exit(0);
//$tags_controller = new Controller(realpath(dirname(__FILE__)),$view);
//$tags_controller->load_view($view);

