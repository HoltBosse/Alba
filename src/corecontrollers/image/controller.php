<?php

Use HoltBosse\Alba\Core\{CMS, File, Image};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;
Use Respect\Validation\Validator as v;

// api style controller - end output
while(ob_get_level()>0) {
	ob_end_clean();
}
// router

$segments = CMS::Instance()->uri_segments;
$segsize = sizeof($segments);
$dbInstance = DB::getInstance();

// handle list api request
if ($segments[1]=='list_images') {
	header('Content-Type: application/json; charset=utf-8');
	$mimetypes = array_filter(explode(',',Input::getvar('mimetypes',v::StringVal(),''))); // array_filter ensures empty array if mimetypes is null
	$searchtext = Input::getvar('searchtext',v::StringVal(),'');
	$images_per_page = Input::getvar('images_per_page',v::IntVal(),50);
	$page = Input::getvar('page',v::IntVal(),1);
	$tags = Input::getvar("tags", v::StringVal(), null);
	if ($searchtext=='null') {
		$searchtext=null;
	}
	if ($searchtext) {
		$query = "SELECT * FROM `media` WHERE (`title` LIKE ? OR alt LIKE ?)";
		$params = ["%$searchtext%","%$searchtext%"];
		if ($mimetypes) {
			$query.=" AND mimetype IN (";
			$result = "'" . implode ( "', '", $mimetypes ) . "'";
			$query .= $result;
			$query.=")";
		}
		if($tags) {
			$explodedTags = explode(",", $tags);
			foreach($explodedTags as &$tag) {
				$tag = $dbInstance->getPdo()->quote($tag);
				unset($tag);
			}
			$wrappedTags = implode(",", $explodedTags);
			
			$query.= " AND id IN (SELECT content_id FROM tagged WHERE content_type_id=-1 AND tag_id IN ($wrappedTags)) "; 
		}
		$query .= " AND (domain=? OR domain IS NULL) ";
		$params[] = ($_SESSION["current_domain"] ?? CMS::getDomainIndex($_SERVER['HTTP_HOST']));

		$query.=" LIMIT " . $images_per_page . " OFFSET " . ($page-1)*$images_per_page;
		$list = DB::fetchAll($query, $params);
	}
	else {
		$query = "SELECT * FROM `media` WHERE id>0";
		$params = [];
		if ($mimetypes) {
			// TODO: ensure valid mimetypes from JSON?
			$query.=" AND mimetype in (";
			for ($n=0; $n < sizeof($mimetypes); $n++) {
				if ($n>0) {
					$query .= ",";
				}
				$query .= $dbInstance->getPdo()->quote($mimetypes[$n]);
			}
			$query.=") ";
		}
		if($tags && !$mimetypes) {
			$explodedTags = explode(",", $tags);
			foreach($explodedTags as &$tag) {
				$tag = $dbInstance->getPdo()->quote($tag);
				unset($tag);
			}
			$wrappedTags = implode(",", $explodedTags);
			
			$query.= " AND id IN (SELECT content_id FROM tagged WHERE content_type_id=-1 AND tag_id IN ($wrappedTags)) "; 
		}
		$query .= " AND (domain=? OR domain IS NULL) ";
		$params[] = ($_SESSION["current_domain"] ?? CMS::getDomainIndex($_SERVER['HTTP_HOST']));

		$query .= " ORDER BY id DESC LIMIT " . $images_per_page . " OFFSET " . ($page-1)*$images_per_page; // newest first, honor (safe) page limit
		$list = DB::fetchAll($query, $params);
	}
		
	//$list = $stmt->fetchAll();
	echo '{"success":1,"msg":"Images found ok","images":'.json_encode($list).', "tags": "' . ($tags ?? "none") . '", "query": "' . $query . '"}';
	exit(0);
} elseif($segments[1]=='gettags') {
	header('Content-Type: application/json; charset=utf-8');
	$tags = DB::fetchAll(
		"SELECT title as text, id as value
		FROM tags
		WHERE (
			(
				filter=2
				AND id IN (
					SELECT tag_id
					FROM tag_content_type
					WHERE content_type_id=?
				)
			) OR (
				filter=1
				AND id NOT IN (
					SELECT tag_id
					FROM tag_content_type
					WHERE content_type_id=?
				)
			)
		)
		AND title LIKE ?
		AND state >= 1",
		[-1, -1, "%" . urldecode(Input::getVar('searchterm', v::StringVal(), '')) . "%"]
	);
	echo json_encode((object) [
		"data"=>$tags
	]);
	exit(0);
}

// end list api

// get width
if ($segsize>=3) {
	$req_width = Input::filter(($segments[2] ?? null), v::in(array_keys(Image::$image_sizes)), null);
} else {
	$req_width = Input::getVar("w", v::numericVal(), null);
}
// get format
$fmtValidator =  v::in(array_map(
	function($input) {
		return explode("/", $input)[1];
	},
	array_keys(array_filter(
		File::$image_types,
		function($value) {
			return $value==1;
		}
	))
));
if ($segsize>=4) {
	$req_format = Input::filter($segments[3], $fmtValidator, null);
}
else {
	$req_format = Input::getVar("fmt", $fmtValidator, null);
}
// quality fixed for url param version
$req_quality = Input::getVar("q", v::numericVal(), 75);

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

function get_image ($id) {
	return DB::fetch('SELECT * FROM media WHERE id=? AND (domain=? OR domain IS NULL)', [$id, ($_SESSION["current_domain"] ?? CMS::getDomainIndex($_SERVER['HTTP_HOST']))]);
}

if ($segsize<2 || !is_numeric($segments[1]) ) {
	http_response_code(406); // not acceptable
	exit(0);
}

if ($segsize==2 && !$req_width && !$req_format && $req_quality==75) {
	// just image id and no get params of note
	// serve original uploaded image
	$image = get_image ($segments[1]);
	if ($image) {
		$fullpath = $_ENV["images_directory"] . '/processed/' . $image->filename;
		serve_file ($image, $fullpath);
	}
	else {
		http_response_code(404); // was h1 echo before. not great.
	}
	exit(0);
}

// reach here, got either segments for size or we have get 1 or more params
// we already have $req_width or $req_format due to the if handling them them missing above

	$image = get_image ($segments[1]);
	if ($image) {
		$original_path = $_ENV["images_directory"] . "/processed/" . $image->filename;
		// if no width param found - set to default image width
		if (!$req_width) {
			$req_width = $image->width;
		}
		//even if a specific version of these types of files is requested,
		//return the native image due to lack of php handling at this time
		if(File::$image_types[$image->mimetype]==2) {
			serve_file ($image, $original_path);
		}
		// check to see if format is og
		// assume format is original mimetype
		if ($req_format) {
			$mimetype = File::get_mimetype_by_format($req_format);
		}
		else {
			$mimetype = $image->mimetype;
		}
		if ($mimetype) {
			// get size
			if (!is_numeric($req_width)) {
				// get size from array lookup (web/thumb) - if fails, assume 1920
				$size = Image::$image_sizes[$req_width] ?? 1920;
				if (!$size) {
					http_response_code(406); // unknown size
					exit(0);
				}
			}
			else {
				$size = $req_width;
			}
			// got int size
			// NO UPSCALING - preserves quality
			if ($image->width <= $size) {
				$size = $image->width;
			}
			// if format shifted, add additional suffix to processed filename
			$newsize_path_suffix = ($mimetype!=$image->mimetype) ? "." . $req_format : "";
			// create unique path based on format/quality/size
			$newsize_path = $_ENV["images_directory"] . "/processed/q_" . $req_quality . "_" . $size . "w_" . $image->filename . $newsize_path_suffix;
			//echo "<h5>Path: " . $newsize_path . "</h5>"; CMS::pprint_r ($mimetype); exit(0);
			if (!file_exists($newsize_path)) {
				Image::MakeResizedImage($original_path, $newsize_path, $size, $image, $req_quality, $mimetype); 
			}
			// set mimetype in image object to match requested mimetype (might already be same...)
			// this makes sure header is correct 
			$image->mimetype = $mimetype;
			// serve existing file or new thumb if created above
			serve_file ($image, $newsize_path); 
		}
		else {
			http_response_code(406); // not acceptable mimetype
			exit(0);
		}
	}
	else {
		http_response_code(404); // was h1 echo before. not great.
		exit(0);
	}

exit(0);

