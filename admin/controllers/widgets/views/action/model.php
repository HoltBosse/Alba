<?php
defined('CMSPATH') or die; // prevent unauthorized access

$actions = [
	"toggle" => ["(CASE state WHEN 1 THEN 0 ELSE 1 END)", "toggled"],
	"publish" => [1, "published"],
	"unpublish" => [0, "unpublished"],
	"delete" => [-1, "deleted"],
];

$action = CMS::Instance()->uri_segments[2];
$id = Input::getvar('id','ARRAYOFINT');
if (!$action || !$id || !$actions[$action]) {
	CMS::Instance()->queue_message('Unknown action or items','danger', $_SERVER['HTTP_REFERER']);
}

function exec_action ($state, $action_text, $ids) {
	$injectionString = $ids ? implode(",", array_map(function($input) { return "?"; }, $ids)) : "?";
	$result = DB::exec("UPDATE widgets SET state = $state WHERE id IN ($injectionString)", $ids);

	if(!$result) { CMS::Instance()->queue_message('Failed to complete action','danger', $_SERVER['HTTP_REFERER']); }

	$widgets = DB::fetchall("SELECT * FROM widgets WHERE id in ($injectionString)", $ids);
	$widgetsMsgString = implode(", ", array_map(function($input) { return "<a target='_blank' href='" . Config::uripath() . "/admin/widgets/edit/$input->id'>$input->title</a>"; }, $widgets));

	CMS::Instance()->queue_message("Widget(s) " . ($state!=-1 ? $widgetsMsgString : "") . " $action_text",'success', $_SERVER['HTTP_REFERER']);

}

$actionDetails = $actions[$action];
exec_action($actionDetails[0], $actionDetails[1], $id);

