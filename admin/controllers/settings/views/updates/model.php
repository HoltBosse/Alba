<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$segments = CMS::Instance()->uri_segments;

$submitted = CMS::Instance()->getvar('update_please');

$latest = new stdClass();
$latest->version = null;
$latest_json = file_get_contents('https://cms.bobmitch.com/version.json');
if (Config::$debug) {
	CMS::pprint_r ($latest_json);
}
if ($latest_json) {
	$latest = json_decode($latest_json);
}


if ($submitted) { 
	// DO UPDATE
	$saved = true;
	// get latest.zip
	$got_file = @file_put_contents(CMSPATH . "/latest.zip", fopen("https://cms.bobmitch.com/latest.zip", 'r'));
	if ($got_file) {
		$zip = new ZipArchive();
		$ok = $zip->open(CMSPATH . "/latest.zip", ZipArchive::CREATE);
		$saved = $zip->extractTo(CMSPATH);
		$zip->close();
		if ($saved && $ok) {
			CMS::Instance()->queue_message('System updated','success',Config::$uripath."/admin");
		}
		else {
			CMS::Instance()->queue_message('Error updating','danger',Config::$uripath."/admin/settings/updates");
		}
	}
	else {
		CMS::Instance()->queue_message('Error fetching latest update file (no fopen?)','danger',Config::$uripath."/admin/settings/updates");
	}
}
