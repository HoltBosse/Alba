<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(php_sapi_name()!=="cli") {
    echo "NOT RUNNING IN CLI, QUITING!!!\n";
    die;
}

//disables script when env var is provided in places such as github actions where we are doing automated installs and dont care about a full cms
if(getenv('alba_installer_skip_configuration')=="true") {
    echo "installer configuration disable by env var, terminating\n";
    die;
}

define ("CMSPATH", realpath(dirname(__file__) . "/../"));
define ("INSTALLERPATH", realpath(dirname(__file__)));

require_once(INSTALLERPATH . "/php/utilities.php");

if(!file_exists(CMSPATH . "/config.php")) {
    outputError("Config file not found!!!");
}

require_once(CMSPATH . "/config.php");

if(Config::sitename()=="Alba") {
    Config::$sitename = readline("Enter site name: ");
    updateConfigFile("sitename", Config::$sitename);
    
    Config::$uripath = readline("Enter uripath: ");
    updateConfigFile("uripath", Config::$uripath);
}

if(!checkDbConnection()) {
    $fields = [
        "dbhost"=>[],
        "dbname"=>[],
        "dbuser"=>[],
        "dbpass"=>[],
        "dbchar"=>[],
    ];

    $firstTime = true;

    while(checkDbConnection()==false) {
        if($firstTime) {
            $firstTime = false;
        } else {
            outputLine("issue with credentials", "ERROR");
        }

        $filledInFields = fillInFields($fields);

        Config::$dbhost = $filledInFields["dbhost"][0];
        Config::$dbname = $filledInFields["dbname"][0];
        Config::$dbuser = $filledInFields["dbuser"][0];
        Config::$dbpass = $filledInFields["dbpass"][0];
        Config::$dbchar = $filledInFields["dbchar"][0];
    }

    updateConfigFile("dbhost", Config::$dbhost);
    updateConfigFile("dbname", Config::$dbname);
    updateConfigFile("dbuser", Config::$dbuser);
    updateConfigFile("dbpass", Config::$dbpass);
    updateConfigFile("dbchar", Config::$dbchar);

    outputLine("DB credentials configured", "SUCCESS");
} else {
    outputLine("DB credentials configured");
}

loadDb();
outputLine("Loaded DB", "SUCCESS");

$sqlFiles = glob(INSTALLERPATH . "/sql/mariadb/*.sql");
foreach($sqlFiles as $file) {
    $tableName = explode(".", basename($file))[0];

    $tableStatus = DB::fetch("SELECT count(*) AS c FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$tableName, Config::dbname()])->c;

    if($tableStatus) {
        outputLine("table $tableName is already installed, skipping");
    } else {
        outputLine("installing $tableName");
        DB::exec(file_get_contents($file));
        outputLine("installed", "SUCCESS");
    }
}

if(DB::fetch("SELECT count(*) AS c FROM groups")->c == 0) {
    DB::exec("INSERT INTO `groups` (value, display) VALUES ('admin','Administrators')");
    DB::exec("INSERT INTO `groups` (value, display) VALUES ('editor','Contributors')");
    outputLine("groups installed");
} else {
    outputLine("groups already installed");
}

if(DB::fetch("SELECT count(*) AS c FROM templates")->c == 0) {
    DB::exec("INSERT INTO templates (is_default, title, folder, description) VALUES (1,'basic','basic','A very simple template to get you started.')");
    outputLine("templates installed");
} else {
    outputLine("templates already installed");
}

if(DB::fetch("SELECT count(*) AS c FROM content_types")->c == 0) {
    DB::exec("INSERT INTO content_types (title, controller_location, description, state) VALUES ('Basic Article','basic_article','A simple HTML content item with a WYSIWYG editor.',1)");
    outputLine("content_types installed");
} else {
    outputLine("content_types already installed");
}

if(DB::fetch("SELECT count(*) AS c FROM content_views")->c == 0) {
    DB::exec("INSERT INTO content_views (content_type_id, title, location) VALUES (1,'Single Article','single')");
    DB::exec("INSERT INTO content_views (content_type_id, title, location) VALUES (1,'Blog','blog')");
    outputLine("content_views installed");
} else {
    outputLine("content_views already installed");
}

if(DB::fetch("SELECT count(*) AS c FROM pages")->c == 0) {
    DB::exec(
        "INSERT INTO pages (title, alias, content_type, parent, template, page_options) VALUES (?,?,?,?,?,?)",
        [
            "My Home Page",
            "home",
            -1,
            -1,
            0,
            ""
        ]
    );

    outputLine("home page created");
} else {
    outputLine("pages already exist");
}

if(DB::fetch("SELECT count(*) as c FROM users")->c == 0) {
    $fields = [
        "username"=>[],
        "email"=>[],
        "password"=>[],
    ];

    $fields = fillInFields($fields);

    //CMS::pprint_r($fields);
    $hash = password_hash($fields["password"][0], PASSWORD_DEFAULT);
	DB::exec(
        "INSERT INTO users (username, email, password, state) VALUES (?,?,?,1)",
        [
            $fields["username"][0],
            $fields["email"][0],
            $hash,
        ]
    );

    $userId = DB::get_last_insert_id();
    $adminGroupId = DB::fetch("SELECT id FROM `groups` WHERE value='admin'")->id;

    DB::exec(
        "INSERT INTO user_groups (user_id, group_id) VALUES (?,?)",
        [
            $userId,
            $adminGroupId,
        ]
    );

    outputLine("inserted user");
} else {
    outputLine("user already added to database");
}

outputLine("cms installation finished");