<?php
defined('CMSPATH') or die; // prevent unauthorized access

ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned
ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned

if (CMS::Instance()->user->username=="guest") {
	CMS::Instance()->queue_message("Must be logged in to upload media",Config::uripath() . '/admin');
}

header('Content-Type: application/json; charset=utf-8');

/*
	state 0: invalid
	state 1: valid+thumbnails
	state 2: valid+no thumbnails
*/
$image_types_data = File::$image_types;

$uploaded_files_array = $_FILES['file-upload'];
$alts = Input::getvar('alt','ARRAYOFSTRING');
$titles = Input::getvar('title','ARRAYOFSTRING');
$tags = Input::getvar('tags','ARRAYOFSTRING');
$web_friendly_array = Input::getvar('web_friendly','ARRAYOFINT');
$directory = CMSPATH . '/images/processed';
$uploaded_files = [];
$pdo = CMS::Instance()->pdo;

function make_thumb($src, $dest, $desired_width, $file) {
	// TODO: move to Image class (sub-file class)
	/* read the source image */
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
	if ($file->mimetype=='image/jpeg') {
		imagejpeg($virtual_image, $dest);
	}
	elseif ($file->mimetype=='image/webp') {
		imagewebp($virtual_image, $dest);
	}
	else {
		imagepng($virtual_image, $dest);
	}
}

function correctImageOrientation($filename) {
	if (function_exists('exif_read_data')) {
		$exif = exif_read_data($filename);
		if($exif && isset($exif['Orientation'])) {
			$orientation = $exif['Orientation'];
			/* if($orientation != 1 || true){ */
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

// upload images to processed directory - make web friendly if needed
$n=0;
$img_ids = [];
foreach ($_FILES["file-upload"]["error"] as $key => $error) {
    if ($error == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES["file-upload"]["tmp_name"][$key];
        // basename() may prevent filesystem traversal attacks;
        // further validation/sanitation of the filename may be appropriate
		// make filename unique-ish
		// TODO: per user images or per-date a la wordpress for unode / performance reasons?
        $name = uniqid() . "_" . basename($_FILES["file-upload"]["name"][$key]);
		$dest = $directory . "/" . $name;
		move_uploaded_file($tmp_name, $dest);
        correctImageOrientation($dest);
		$uploaded_files[] = $name;
		//  get file info and put in db
		$title = $titles[$n];
		$alt = $alts[$n];
		$web_friendly = $web_friendly_array[$n];
		// TODO: further filtering on title and alt just in case
		$file = new File($dest);
		if ($file->width > 1920 && $web_friendly[$n]) {
			$file->original_width = $file->width;
			$file->recalc_height(1920);
		}
		$in_db_ok = DB::exec("insert into media (width, height, title, alt, filename, mimetype) values (?,?,?,?,?,?)", [$file->width, $file->height, $title, $alt, $file->filename, $file->mimetype]);
		$img_ids[] = DB::getLastInsertedId();
		if ($in_db_ok) {
			$thumbdest = CMSPATH . '/images/processed/' . "web_" . $file->filename;
			// make web friendly if required
			if ($file->original_width > 1920 && $web_friendly[$n] && $image_types_data[$file->mimetype]==1) {
				make_thumb($dest, $thumbdest, 1920, $file);
				unlink($src);
			}
			$processed[] = $all_image_files[$n];

			foreach(json_decode($tags[$n]) as $tag_id) {
				DB::exec("insert into tagged (content_id, tag_id, content_type_id) values (?,?,-1)", [$img_ids[sizeof($img_ids)-1], $tag_id]);
			}
		}
		else {
			// TODO: handle db insert error - shouldn't happen, but need to return an appropriate JSON string to be handled
		}
    }
	$n++;
}
$uploaded_files = implode(",",$uploaded_files);

foreach($img_ids as $id) {
	Actions::add_action("mediacreate", (object) [
		"affected_media"=>$id,
	]);
}


// return json to javascript uploaded and
CMS::Instance()->queue_message('Images uploaded','success');
//echo '{"success":1,"msg":"Images uploaded","files":"'.$uploaded_files.'","ids":"'.implode(",",$img_ids).'"}';

echo json_encode((object) [
	"success"=>1,
	"msg"=>"Images uploaded",
	"files"=>$uploaded_files,
	"ids"=>implode(",",$img_ids),
	"tags"=>json_encode($tags),
	"urls"=>implode(",", array_map(function($c){ return Config::uripath() . "/image/$c"; }, $img_ids)),
]);
exit(0);
