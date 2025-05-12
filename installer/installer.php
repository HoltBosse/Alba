<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(php_sapi_name()!=="cli") {
    echo "NOT RUNNING IN CLI, QUITING!!!\n";
    die;
}

define ("CMSPATH", realpath(dirname(__file__) . "/../"));
define ("INSTALLERPATH", realpath(dirname(__file__)));

require_once(INSTALLERPATH . "/php/utilities.php");

if(!file_exists(CMSPATH . "/config.php")) {
    outputError("ERROR: Config file not found!!!");
}

require_once(CMSPATH . "/config.php");

if(!checkDbConnection()) {
    outputError("TODO: Implement config file updating!!!");

    /* outputLine(Config::$sitename);
    Config::$sitename = "lol";
    outputLine(Config::$sitename); */
} else {
    outputLine("SUCCESS: DB credentials configured");
}

loadDb();
outputLine("SUCCESS: Loaded DB");

$sqlFiles = glob(INSTALLERPATH . "/sql/mariadb/*.sql");
foreach($sqlFiles as $file) {
    $tableName = explode(".", basename($file))[0];

    $tableStatus = DB::fetch("SELECT count(*) AS c FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$tableName'")->c;

    if($tableStatus) {
        outputLine("NOTICE: table $tableName is already installed, skipping");
    } else {
        outputLine("NOTICE: installing $tableName");
        DB::exec(file_get_contents($file));
        outputLine("NOTICE: installed");
    }
}

if(DB::fetch("SELECT count(*) AS c FROM groups")->c == 0) {
    DB::exec("INSERT INTO `groups` (value, display) VALUES ('admin','Administrators')");
    DB::exec("INSERT INTO `groups` (value, display) VALUES ('editor','Contributors')");
    outputLine("NOTICE: groups installed");
} else {
    outputLine("NOTICE: groups already installed");
}

if(DB::fetch("SELECT count(*) AS c FROM templates")->c == 0) {
    DB::exec("INSERT INTO templates (is_default, title, folder, description) VALUES (1,'basic','basic','A very simple template to get you started.')");
    outputLine("NOTICE: templates installed");
} else {
    outputLine("NOTICE: templates already installed");
}

if(DB::fetch("SELECT count(*) AS c FROM content_types")->c == 0) {
    DB::exec("INSERT INTO content_types (title, controller_location, description, state) VALUES ('Basic Article','basic_article','A simple HTML content item with a WYSIWYG editor.',1)");
    outputLine("NOTICE: content_types installed");
} else {
    outputLine("NOTICE: content_types already installed");
}

if(DB::fetch("SELECT count(*) AS c FROM content_views")->c == 0) {
    DB::exec("INSERT INTO content_views (content_type_id, title, location) VALUES (1,'Single Article','single')");
    DB::exec("INSERT INTO content_views (content_type_id, title, location) VALUES (1,'Blog','blog')");
    outputLine("NOTICE: content_views installed");
} else {
    outputLine("NOTICE: content_views already installed");
}

outputLine("TODO: handle email, username, password - site name, uripath");

outputLine("NOTICE: cms installation finished");