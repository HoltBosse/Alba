<?php

Use HoltBosse\Alba\Core\{CMS, Content, Actions, JSON};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

$action = CMS::Instance()->uri_segments[2];
if (!$action) {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}

$content_type = Input::getvar('content_type',v::IntVal(),null);
if (!$content_type) {
	CMS::Instance()->queue_message('Unknown content type','danger', $_SERVER['HTTP_REFERER']);
}

$location = Content::get_content_location($content_type);
$custom_fields = JSON::load_obj_from_file(Content::getContentControllerPath($location) . '/custom_fields.json');
$table_name = "controller_" . $custom_fields->id ;
if ($table_name=="controller_") {
	CMS::Instance()->queue_message('Unable to determine content table name','danger', $_SERVER['HTTP_REFERER']);
}



if ($action=='toggle') {
	$id = Input::getvar('id',v::arrayType()->each(v::intVal()));
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}

	foreach($id as $entry) {
		$beforeState = DB::fetch("SELECT state FROM `$table_name` WHERE id=?", [$entry])->state;

		$actionId = Actions::add_action("contentupdate", (object) [
			"content_id"=>$entry,
			"content_type"=>$content_type,
		]);

		$userActionDiff = [
			"state" => (object) [
				"before"=> $beforeState,
				"after"=> $beforeState == 1 ? 0 : 1, //dupe logic in sql below
			]
		];

		Actions::add_action_details($actionId, (object) $userActionDiff);
	}

	$result = DB::exec("update `$table_name` SET state = (CASE state WHEN 1 THEN 0 ELSE 1 END) where id=?", [$id[0]]); // id always array even with single id being passed
	if ($result) {
		$content = DB::fetch("SELECT * FROM `$table_name` WHERE id=?", [$id[0]]);
		$msg = "Content <a href='" . $_ENV["uripath"] . "/admin/content/edit/{$id[0]}/{$content->content_type}'>" . Input::stringHtmlSafe($content->title) . "</a> state toggled";
		CMS::Instance()->queue_message($msg,'success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to toggle state of content','danger', $_SERVER['HTTP_REFERER']);
	}
}

if ($action=='togglestate') {
	$togglestate = Input::getvar('togglestate',v::arrayType()->each(v::intVal()));
	if (!$togglestate) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}

	$beforeState = DB::fetch("SELECT state FROM `$table_name` WHERE id=?", [$togglestate[0]])->state;

	$actionId = Actions::add_action("contentupdate", (object) [
		"content_id"=>$togglestate[1],
		"content_type"=>$content_type,
	]);

	$userActionDiff = [
		"state" => (object) [
			"before"=> $beforeState,
			"after"=> $togglestate[1],
		]
	];

	Actions::add_action_details($actionId, (object) $userActionDiff);

	$result = DB::exec("update `$table_name` SET state = ? where id=?", [$togglestate[1], $togglestate[0]]); //first is id, second is state
	if ($result) {
		CMS::Instance()->queue_message('Updated state of content','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to update state of content','danger', $_SERVER['HTTP_REFERER']);
	}
}

elseif ($action=='publish') {
	$id = Input::getvar('id',v::arrayType()->each(v::intVal()));
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}

	foreach($id as $entry) {
		$beforeState = DB::fetch("SELECT state FROM `$table_name` WHERE id=?", [$entry])->state;

		$actionId = Actions::add_action("contentupdate", (object) [
			"content_id"=>$entry,
			"content_type"=>$content_type,
		]);

		$userActionDiff = [
			"state" => (object) [
				"before"=> $beforeState,
				"after"=> 1,
			]
		];

		Actions::add_action_details($actionId, (object) $userActionDiff);
	}

	$idlist = implode(',',$id);
	$result = DB::exec("update `$table_name` SET state = 1 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Published content','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to publish content','danger', $_SERVER['HTTP_REFERER']);
	}
}

elseif ($action=='unpublish') {
	$id = Input::getvar('id',v::arrayType()->each(v::intVal()));
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}

	foreach($id as $entry) {
		$beforeState = DB::fetch("SELECT state FROM `$table_name` WHERE id=?", [$entry])->state;

		$actionId = Actions::add_action("contentupdate", (object) [
			"content_id"=>$entry,
			"content_type"=>$content_type,
		]);

		$userActionDiff = [
			"state" => (object) [
				"before"=> $beforeState,
				"after"=> 0,
			]
		];

		Actions::add_action_details($actionId, (object) $userActionDiff);
	}

	$idlist = implode(',',$id);
	$result = DB::exec("update `$table_name` SET state = 0 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Unpublished content','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to unpublish content','danger', $_SERVER['HTTP_REFERER']);
	}
}

elseif ($action=='delete') {
	$id = Input::getvar('id',v::arrayType()->each(v::intVal()));
	if (!$id) {
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}

	foreach($id as $entry) {
		$beforeState = DB::fetch("SELECT state FROM `$table_name` WHERE id=?", [$entry])->state;

		$actionId = Actions::add_action("contentupdate", (object) [
			"content_id"=>$entry,
			"content_type"=>$content_type,
		]);

		$userActionDiff = [
			"state" => (object) [
				"before"=> $beforeState,
				"after"=> -1,
			]
		];

		Actions::add_action_details($actionId, (object) $userActionDiff);
	}

	$idlist = implode(',',$id);
	$result = DB::exec("update `$table_name` SET state = -1 where id in ({$idlist})"); 
	if ($result) {
		CMS::Instance()->queue_message('Deleted content','success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to delete content','danger', $_SERVER['HTTP_REFERER']);
	}
}

elseif ($action=='duplicate') {
	$ids = Input::getvar('id',v::arrayType()->each(v::intVal()));
	if (!$ids) {
		//CMS::pprint_r ($ids);exit(0);
		CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
	}
	
	foreach ($ids as $id) {
		$orig = new Content();
		$orig->load($id, Input::getvar('content_type',v::IntVal()));
		$orig->duplicate();
	}
	// todo: nicely report on good or bad duplicates
	CMS::Instance()->queue_message('Content duplicated','success', $_SERVER['HTTP_REFERER']);
}



else {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}

