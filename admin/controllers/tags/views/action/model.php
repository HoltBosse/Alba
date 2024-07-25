<?php
defined('CMSPATH') or die; // prevent unauthorized access

$actions = [
	"toggle"=>["tagupdate", "(CASE state WHEN 1 THEN 0 ELSE 1 END)", "toggled"],
	"publish"=>["tagupdate", 1, "published"],
	"unpublish"=>["tagupdate", 0, "unpublished"],
	"delete"=>["tagdelete", -1, "deleted"],
];

$action = CMS::Instance()->uri_segments[2];
$id = Input::getvar('id','ARRAYOFINT');
if (!$action || !$id || !$actions[$action]) {
	CMS::Instance()->queue_message('Unknown action or items','danger', $_SERVER['HTTP_REFERER']);
}

function exec_action($label, $state, $action_text, $ids) {
	foreach($ids as $tag) {
		Actions::add_action($label, (object) [
			"affected_tag"=>$tag,
		]);
	}

	$injectionString = implode(",", array_map(function($input) { return "?"; }, $ids));
	$result = DB::exec("UPDATE tags SET state = $state WHERE id IN ($injectionString)", $ids);

	if(!$result) { CMS::Instance()->queue_message('Failed to complete action','danger', $_SERVER['HTTP_REFERER']); }

	$tags = DB::fetchall("SELECT * FROM tags WHERE id in ($injectionString)", $ids);
	$tagsMsgString = implode(", ", array_map(function($input) { return "<a target='_blank' href='" . Config::uripath() . "/admin/tags/edit/$input->id'>$input->title</a>"; }, $tags));

	CMS::Instance()->queue_message("Tag(s) " . ($state!=-1 ? $tagsMsgString : "") . " $action_text",'success', $_SERVER['HTTP_REFERER']);
}

$actionDetails = $actions[$action];
exec_action($actionDetails[0], $actionDetails[1], $actionDetails[2], $id);

