<?php
defined('CMSPATH') or die; // prevent unauthorized access

$cur_page = $_GET["page"] ?? 1;
$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size') ?? 10;

$actionClasses = glob(CMSPATH . "/core/actions/*.php");
$actionTypes = [];
foreach($actionClasses as $class) {
    $file = basename($class);
    $actionTypes[] = explode(".", (explode("_", $file)[1]))[0];
}
$actionTypesString = "'" . implode("','", $actionTypes) . "'";

$results = DB::fetchall("SELECT * FROM user_actions WHERE `type` IN ({$actionTypesString}) ORDER BY date DESC LIMIT {$pagination_size} OFFSET " . (($cur_page-1)*$pagination_size));

//CMS::pprint_r($results);