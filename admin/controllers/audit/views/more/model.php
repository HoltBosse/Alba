<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

$viewmore = DB::fetch("SELECT * FROM user_actions_details WHERE id=?", ($segments[2] ?? 0));

if(!$viewmore) {
    CMS::show_error("Details not found", 404);
}

$actionDetails = DB::fetch("SELECT * FROM user_actions WHERE id=?", $viewmore->action_id);
$actionClassName = "Action_" . $actionDetails->type;
$actionInstance = new $actionClassName($actionDetails);

//CMS::pprint_r($actionInstance);
//CMS::pprint_r($viewmore);

/* foreach(json_decode($viewmore->json) as $item) {
    CMS::pprint_r($item);
} */