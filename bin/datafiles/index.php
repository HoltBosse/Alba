<?php

//handle composer dependencies
include_once(__DIR__ . '/vendor/autoload.php');

Use HoltBosse\Alba\Core\{CMS, Template, Content, Widget, Plugin};
Use HoltBosse\DB\DB;
Use Symfony\Component\Dotenv\Dotenv;
Use \Exception;

//we try catch this, since the cms debug code needs env vars to run
try {
	$dotenv = new Dotenv();
	$dotenv->load(__DIR__.'/../.env');
} catch (Exception $e) {
	http_response_code(500);
	echo $e->getMessage();
	die;
}

CMS::configureDebug();

DB::createInstance(
	"mysql:host=" . $_ENV["dbhost"] .";dbname=" . $_ENV["dbname"] .";charset=" . $_ENV["dbchar"],
	$_ENV["dbuser"],
	$_ENV["dbpass"]
);

$_ENV["images_directory"] = __DIR__ . "/images";
$_ENV["backup_directory"] = __DIR__;
$_ENV["root_path_to_forms"] = __DIR__;
$_ENV["cms_log_file_path"] = __DIR__ . "/cmslog.txt";
$_ENV["cache_root"] = __DIR__;

CMS::registerCoreControllerDir(__DIR__ . '/src/corecontrollers');
Template::registerTemplateDir(__DIR__ . '/src/templates');
Content::registerContentControllerDir(__DIR__ . "/src/controllers");
Widget::registerWidgetDir(__DIR__ . '/src/Widgets', "Workaj\\Albatest\\Widgets\\");
Plugin::registerPluginDir(__DIR__ . '/src/Plugins', "Workaj\\Albatest\\Plugins\\");

CMS::Instance()->render();