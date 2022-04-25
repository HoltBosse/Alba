<?php

defined('CMSPATH') or die; // prevent unauthorized access 

 class Config {

static $dbhost = 'localhost';
static $dbname = 'dbname';
static $dbuser = 'dbuser';
static $dbpass = 'dbpass';
static $dbchar = 'utf8mb4';
static $sitename = "SeamlessCMS";
static $uripath = "";
static $debug = false;
static $domain = 'auto';
static $user_core_controllers = NULL;
static $admintemplate = 'clean';
static $frontendlogin = false;
static $environment = "dev"; // dev/staging/live
}