<?php

Use HoltBosse\Alba\Core\{CMS, Configuration, Actions};
Use HoltBosse\Form\Form;
Use HoltBosse\DB\DB;

$segments = CMS::Instance()->uri_segments;
if(sizeof($segments)>3) {
    CMS::raise_404();
}

$viewmore = DB::fetch("SELECT * FROM user_actions_details WHERE id=?", ($segments[2] ?? 0));

if(!$viewmore) {
    CMS::show_error("Details not found", 404);
}

$actionDetails = DB::fetch("SELECT * FROM user_actions WHERE id=?", $viewmore->action_id);
$actionClassName = Actions::getActionClass($actionDetails->type);
$actionInstance = new $actionClassName($actionDetails);

//CMS::pprint_r($actionInstance);
//CMS::pprint_r($viewmore);

/* foreach(json_decode($viewmore->json) as $item) {
    CMS::pprint_r($item);
} */