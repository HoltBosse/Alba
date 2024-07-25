<?php
defined('CMSPATH') or die; // prevent unauthorized access

$actions = [
	"toggle"=>["contentupdate", "(CASE state WHEN 1 THEN 0 ELSE 1 END)", " toggled"],
	"togglestate"=>["contentupdate", "?", "state updated"],
	"publish"=>["contentupdate", 1, "published"],
	"unpublish"=>["contentupdate", 0, "unpublished"],
	"delete"=>["contentdelete", -1, "deleted"],
	"duplicate"=>["contentduplicate", "", "duplicated"],
];


$action = CMS::Instance()->uri_segments[2];
$id = Input::getvar('id','ARRAYOFINT');
$content_type = Input::getvar('content_type','INT',null);
$togglestate = Input::getvar('togglestate','ARRAYOFINT');

if (!$content_type) {
	CMS::Instance()->queue_message('Unknown content type','danger', $_SERVER['HTTP_REFERER']);
}
if (!$action) {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}
if (!$id && !$togglestate) {
	CMS::Instance()->queue_message('Unknown id and togglestate','danger', $_SERVER['HTTP_REFERER']);
}
if (!$actions[$action]) {
	CMS::Instance()->queue_message('Unknown action items','danger', $_SERVER['HTTP_REFERER']);
}


function exec_action($label, $state, $action_text, $ids) {
	$content_type = Input::getvar('content_type','INT',null);
	$table_name = Content::get_table_name_for_content_type($content_type);
	$togglestate = Input::getvar('togglestate','ARRAYOFINT');
	
	foreach($ids as $entry) {
		Actions::add_action($label, (object) [
			"content_id"=>$entry,
			"content_type"=>$content_type
		]);
	}
	
	$injectionString = $ids ? implode(",", array_map(function($input) { return "?"; }, $ids)) : "?";

	if ($action_text == "state updated") {
		$injectionString = "?";
		$result = DB::exec("UPDATE `{$table_name}` SET state = ? WHERE id = ?", [$togglestate[1], $togglestate[0]]);
	} else if ($action_text == "duplicated") {
		foreach ($ids as $id) {
			$orig = new Content();
			$orig->load($id, $content_type);
			$orig->duplicate();
		}
		$result = true;
	} else {
		$result = DB::exec("UPDATE `{$table_name}` SET state = $state WHERE id IN ($injectionString)", $ids);
	}
	
	if(!$result) { CMS::Instance()->queue_message('Failed to complete action','danger', $_SERVER['HTTP_REFERER']); }
	
	$content = DB::fetchall("SELECT * FROM `{$table_name}` WHERE id IN ($injectionString)", ($ids ? $ids : [$togglestate[0]]));
	$contentMsgString = implode(", ", array_map(function($input) { return "<a href='" . Config::uripath() . "/admin/content/edit/{$input->id}/{$input->content_type}'>{$input->title}</a>"; }, $content));
	CMS::Instance()->queue_message("Content " . ($label!="contentdelete" ? $contentMsgString : "") . " $action_text",'success', $_SERVER['HTTP_REFERER']);
	
}

$actionDetails = $actions[$action];
exec_action($actionDetails[0], $actionDetails[1], $actionDetails[2], $id);