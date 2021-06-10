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
/* $query = "show columns FROM `pages` LIKE 'page_options'";
$stmt = CMS::Instance()->pdo->prepare($query);
$stmt->execute(array());
$page_options_ok = $stmt->fetchAll(); */
$page_options_ok = DB::fetchall("show columns FROM `pages` LIKE 'page_options'");
if (!$page_options_ok) {
	// add column
	DB::exec("ALTER TABLE `pages` ADD `page_options` text NOT NULL COMMENT 'seo and og settings';");
	$fixed_ok = true;
}

$query = "SELECT * 
FROM information_schema.tables 
WHERE table_name = 'plugins'
LIMIT 1;";
$stmt = CMS::Instance()->pdo->prepare($query);
$stmt->execute(array());
$plugins_table_ok = $stmt->fetchAll();
if (!$plugins_table_ok) {
	DB::exec("DROP TABLE IF EXISTS `plugins`;");
	DB::exec("CREATE TABLE `plugins` (
	  `id` int(11) NOT NULL,
	  `state` tinyint(4) NOT NULL DEFAULT '0',
	  `title` varchar(255) NOT NULL,
	  `location` varchar(255) NOT NULL,
	  `options` text COMMENT 'options_json',
	  `description` mediumtext
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
	DB::exec("ALTER TABLE `plugins` ADD PRIMARY KEY (`id`);");
	DB::exec("ALTER TABLE `plugins` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
}

//plugins_table_ok
$query = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'tags' AND COLUMN_NAME = 'parent'";
$stmt = CMS::Instance()->pdo->prepare($query);
$stmt->execute(array());
$tags_table_ok = $stmt->fetchAll();

if (!$tags_table_ok) {
	DB::exec("ALTER TABLE tags ADD COLUMN `parent` int(11) DEFAULT NULL");
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
