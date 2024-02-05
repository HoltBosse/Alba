<?php
defined('CMSPATH') or die; // prevent unauthorized access

ob_end_clean(); // IMPORTANT - empty output buffer from template to ensure on JSON is returned

// TODO: endure logged in user is allowed to actually perform these tasks!

// TODO: remove front-end images api model????

header('Content-Type: application/json');

$action = Input::getvar('action','STRING');
$videos = new Videos();

if ($action=='list_videos') {
	// todo: pagination / search
	
	$searchtext = Input::getvar('searchtext','STRING');
	if ($searchtext=='null') {
		$searchtext=null;
	}
	if ($searchtext) {
		$allvideos = $videos->search_all_videos($searchtext);
	}
	else {
		$allvideos = $videos->get_all_videos();
	}

	echo '{"success":1,"msg":"Videos found ok","videos":'.json_encode($allvideos).'}';
	exit(0);
}

echo '{"success":0,"msg":"Unknown operation requested"}';
exit(0);

