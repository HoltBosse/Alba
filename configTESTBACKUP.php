<?php

defined('CMSPATH') or die; // prevent unauthorized access 

 class Config {

static $dbhost = 'localhost';
static $dbname = 'dbname';
static $dbuser = 'dbuser';
static $dbpass = 'dbpass';
static $dbchar = 'utf8mb4';
static $sitename = "Alba";
static $uripath = "";
static $debug = false;
static $domain = 'auto';
static $user_core_controllers = NULL;
static $admintemplate = 'clean';
static $frontendlogin = false;
static $environment = "dev"; // dev/staging/live
static $channel = "stable"; // stable/alpha
static $updatedomain = "alba.holtbosse.com";
static $dev_banner = false;

public static function __callStatic($name, $args) {
    return property_exists('Config',$name) ? Config::$$name : null;
}
}
