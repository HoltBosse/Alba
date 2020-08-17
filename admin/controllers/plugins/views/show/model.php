<?php
defined('CMSPATH') or die; // prevent unauthorized access
if (sizeof(CMS::Instance()->uri_segments)==3) {
	$plugin_id = CMS::Instance()->uri_segments[2];
}
else {
	$plugin_id = false;
}

$plugin_title="";

if (is_numeric($plugin_id)) {
	$plugin = CMS::Instance()->pdo->query('select * from plugins where id=' . $plugin_id)->fetchAll();
}

$all_plugins = Plugin::get_all_plugins();

