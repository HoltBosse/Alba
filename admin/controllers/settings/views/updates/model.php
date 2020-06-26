<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$segments = CMS::Instance()->uri_segments;

$submitted = Input::getvar('update_please');

$latest = new stdClass();
$latest->version = null;
$latest_json = file_get_contents('https://cms.bobmitch.com/version.json');
if (Config::$debug) {
	CMS::pprint_r ($latest_json);
}
if ($latest_json) {
	$latest = json_decode($latest_json);
}

// Legacy DB Checks / Fixes

// check page_options column in pages table
$query = "show columns FROM `pages` LIKE 'page_options'";
$stmt = CMS::Instance()->pdo->prepare($query);
$stmt->execute(array());
$page_options_ok = $stmt->fetchAll();
if (!$page_options_ok) {
	// add column
	$query = "ALTER TABLE `pages` ADD `page_options` text NOT NULL COMMENT 'seo and og settings'";
	$stmt = CMS::Instance()->pdo->prepare($query);
	$fixed_ok = $stmt->execute();
	if (!$fixed_ok) {
		CMS::Instance()->queue_message('Unable to add page_options column in pages table.','danger',Config::$uripath."/admin");
	}
}

// Perform update if required

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
			CMS::Instance()->queue_message('System updated','success',Config::$uripath."/admin/settings/updates");
		}
		else {
			CMS::Instance()->queue_message('Error updating','danger',Config::$uripath."/admin/settings/updates");
		}
	}
	else {
		CMS::Instance()->queue_message('Error fetching latest update file (no fopen?)','danger',Config::$uripath."/admin/settings/updates");
	}
}
