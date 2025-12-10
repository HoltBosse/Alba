<?php

Use HoltBosse\Alba\Core\{CMS, Actions, File};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;
Use Respect\Validation\Validator as v;

ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned
ob_end_clean(); // do it again, we may be inside another buffer due to reasons

// TODO: endure logged in user is allowed to actually perform these tasks!

// TODO: remove front-end images api model????

$action = Input::getvar('action',v::StringVal());
$mimetypes = array_filter(explode(',',Input::getvar('mimetypes',v::StringVal(),''))); // array_filter ensures empty array if mimetypes is null
// TODO sanitize mimetypes against whitelist

if ($action=='tag_media') {
	$image_ids_string = Input::getvar('id_list',v::StringVal(),'');
	$image_ids = explode(",", $image_ids_string);
	$tag_id = Input::getvar('tag_id',v::IntVal());
	$image_ids_tagged=[];
	$image_ids_failed=[];
	foreach ($image_ids as $image_id) {
		Actions::add_action("mediaupdate", (object) [
			"affected_media"=>$image_id,
		]);
		// check if already tagged
		$c = DB::fetch("select count(tag_id) as c from tagged where content_id=? and content_type_id=-1 and tag_id=?", [$image_id, $tag_id])->c;
		if (!$c) {
			// not tagged, insert
			$ok = DB::exec("insert into tagged (content_id, tag_id, content_type_id) values (?,?,-1)", [$image_id, $tag_id]);
			if ($ok) {
				$image_ids_tagged[] = $image_id;
			}
			else {
				$image_ids_failed[] = $image_id;
			}
		}
		else {
			$image_ids_failed[] = $image_id;
		}
	}
	echo '{"success":1,"message":"Tagging finished","tagged":'.json_encode($image_ids_tagged).',"failed":'.json_encode($image_ids_failed).'}';
	exit(0);
}

if ($action=='cleartags_media') {
	$image_ids_string = Input::getvar('id_list',v::StringVal(),'');
	$image_ids = explode(",", $image_ids_string);
	$image_ids_tagged=[];
	$image_ids_failed=[];
	foreach ($image_ids as $image_id) {
			Actions::add_action("mediaupdate", (object) [
				"affected_media"=>$image_id,
			]);
			$ok = DB::exec("delete from tagged where content_id=? and content_type_id=-1", [$image_id]);
			if ($ok) {
				$image_ids_tagged[] = $image_id;
			}
			else {
				$image_ids_failed[] = $image_id;
			}
	}
	echo '{"success":1,"message":"Untagging finished","untagged":'.json_encode($image_ids_tagged).',"failed":'.json_encode($image_ids_failed).'}';
	exit(0);
}

if ($action=='delete_media') {
	$image_ids_string = Input::getvar('id_list',v::StringVal(),'');
	$image_ids = explode(",", $image_ids_string);
	$image_ids_tagged=[];
	$image_ids_failed=[];
	foreach ($image_ids as $image_id) {
		Actions::add_action("mediadelete", (object) [
			"affected_media"=>$image_id,
		]);
		$filename_response = DB::fetch('select filename from media where id=?',$image_id);

		// clear tags
		$ok = DB::exec("delete from tagged where content_id=? and content_type_id=-1", [$image_id]);
		// clear media table
		$ok = DB::exec("delete from media where id=?", [$image_id]);
		
		// TODO: remove file(s) from /processed or any other thumbnail/resolution cache created in future
		if ($filename_response) {
			$filename = $filename_response->filename;
			foreach (glob($_ENV["images_directory"] . "/processed/*".$filename) as $delfile) {
				unlink($delfile);
			}
		}
		
		if ($ok) {
			$image_ids_tagged[] = $image_id;
		}
		else {
			$image_ids_failed[] = $image_id;
		}
	}
	echo '{"success":1,"message":"Deleting finished","untagged":'.json_encode($image_ids_tagged).',"failed":'.json_encode($image_ids_failed).'}';
	exit(0);
}

if ($action=='untag_media') {
	// clear single tag from media list
	$image_ids_string = Input::getvar('id_list',v::StringVal(),'');
	$image_ids = explode(",", $image_ids_string);
	$image_ids_untagged=[];
	$image_ids_failed=[];
	$tag_id = Input::getvar('tag_id',v::IntVal());
	foreach ($image_ids as $image_id) {
		Actions::add_action("mediaupdate", (object) [
			"affected_media"=>$image_id,
		]);
		// clear tags
		$ok = DB::exec("delete from tagged where content_id=? and content_type_id=-1 and tag_id=?", [$image_id, $tag_id]);
		if ($ok) {
			$image_ids_untagged[] = $image_id;
		}
		else {
			$image_ids_failed[] = $image_id;
		}
	}
	echo '{"success":1,"message":"Untagging finished","untagged":'.json_encode($image_ids_untagged).',"failed":'.json_encode($image_ids_failed).'}';
	exit(0);
}

if ($action=='delete') {
	$idlist = implode(',',$id);
	
	$result = DB::exec("DELETE FROM media where id in ({$idlist})"); 
	if ($result) {
		//CMS::Instance()->queue_message('Deleted tags','success', $_SERVER['HTTP_REFERER']);
		echo '{"success":1,"msg":"Image(s) deleted"}';
		exit(0);
	}
	else {
		//CMS::Instance()->queue_message('Failed to delete tags','danger', $_SERVER['HTTP_REFERER']);
		echo '{"success":0,"msg":"Unable to remove image(s) from database"}';
		exit(0);
	}
}

if ($action=='rename_image') {
	$title = Input::getvar('title',v::StringVal(),'');
	$alt = Input::getvar('alt',v::StringVal(),'');
	$image_id = Input::getvar('image_id',v::IntVal());

	Actions::add_action("mediaupdate", (object) [
		"affected_media"=>$image_id,
	]);

	$result = DB::exec("update media set title=?, alt=? where id=?", [$title, $alt, $image_id]);
	if ($result) {
		echo '{"success":1,"msg":"Image renamed"}';
		exit(0);
	}
	else {
		echo '{"success":0,"msg":"Problem renaming image"}';
		exit(0);
	}
}

echo '{"success":0,"msg":"Unknown operation requested"}';
exit(0);

