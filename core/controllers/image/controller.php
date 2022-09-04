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
	header("Content-type: " . $media_obj->mimetype);

	// virtual 
	if (function_exists('virtual')) {
		virtual($fullpath);
	}
	else {
		readfile($fullpath);
	}
	exit(0);

}


function make_thumb ($src, $dest, $desired_width, $file, $quality=65, $newmimetype=false) {
	if (!$newmimetype) {
		// no new format requested, simply use existing mimetype
		$newmimetype = $image->mimetype;
	}
	if ($file->mimetype=='image/jpeg') {
		$source_image = imagecreatefromjpeg($src);
	}
	elseif ($file->mimetype=='image/webp') {
		$source_image = imagecreatefromwebp($src);
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
	if ($newmimetype=='image/jpeg') {
		imagejpeg($virtual_image, $dest, $quality);
	}
	elseif ($newmimetype=='image/webp') {
		imagewebp($virtual_image, $dest, $quality);
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

$segsize = sizeof($segments);

if ($segsize==0) {
	//CMS::Instance()->queue_message('Unknown image id','danger',Config::$uripath.'/');
	echo "<h1>wtf - shouldn't get here :)</h1>";
}
if ($segsize==1) {
	//CMS::Instance()->queue_message('Unknown image id','danger',Config::$uripath.'/');
	//$view = 'show';
	
	echo "<h1>NO IMAGE GIVEN!</h1>";
	exit(0);
}
if ($segsize==2) {
	if (is_numeric($segments[1])) {
		//CMS::log("call to Image Controller with single INT {$segments[1]}");
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

if ($segsize==3||$segsize==4) {
	// [2] = size, [3] = format
	if (is_numeric($segments[1])) {
		$image = get_image ($segments[1]);
		if ($image) {
			$original_path = CMSPATH . "/images/processed/" . $image->filename;
			$param = $segments[2];
			$target_width = $image->width;
			//even if a specific version of these types of files is requested,
			//return the native image due to lack of php handling at this time
			if(File::$image_types[$image->mimetype]==2) {
				serve_file ($image, $original_path);
			}
			// check to see if format is og
			// assume format is original mimetype
			$mimetype = $image->mimetype;
			if ($segsize==4) {
				$format = $segments[3];
				$mimetype = File::get_mimetype_by_format($format);
			}
			if ($mimetype) {
				// get size
				if (!is_numeric($param)) {
					// get size from array lookup (web/thumb) - if fails, assume 1920
					$size = File::$image_sizes[$param] ?? 1920;
				}
				else {
					$size = $param;
				}
				// got int size
				// if original image size is less than requested, just serve original 
				// NO UPSCALING - preserves quality
				if ($image->width <= $size) {
					if ($mimetype==$image->mimetype) {
						serve_file ($image, $original_path); // exits script
					}
					else {
						// need og size version of original
						$newsize_path = CMSPATH . "/images/processed/" . $image->width . "w_" . $image->filename . "." . $format;
						if (!file_exists($newsize_path)) {
							make_thumb($original_path, $newsize_path, $size, $image, 80, $mimetype); // default to 80 for q
						}
						$image->mimetype = $mimetype; // switch native image mimetype to match requested
						// serve existing file or new thumb if created above
						serve_file ($image, $newsize_path); // exist script
					}
				}
				else {
					// need none-og size
					if ($mimetype==$image->mimetype) {
						// serve original format file
						$newsize_path = CMSPATH . "/images/processed/" . $size . "w_" . $image->filename;
						if (!file_exists($newsize_path)) {
							make_thumb($original_path, $newsize_path, $size, $image, 80); // default to 80 for q
						}
						// serve existing file or new thumb if created above
						serve_file ($image, $newsize_path); // exist script
					}
					else {
						// need format shift
						$newsize_path = CMSPATH . "/images/processed/" . $size . "w_" . $image->filename . "." . $format;
						if (!file_exists($newsize_path)) {
							make_thumb($original_path, $newsize_path, $size, $image, 80, $mimetype); // default to 80 for q
						}
						$image->mimetype = $mimetype; // switch native image mimetype to match requested
						// serve existing file or new thumb if created above
						serve_file ($image, $newsize_path); // exist script
					}
				}
			}
			else {
				http_response_code(406); // not acceptable
				exit(0);
			}
		}
		else {
			http_response_code(404); // was h1 echo before. not great.
			exit(0);
		}
	}
}
exit(0);

