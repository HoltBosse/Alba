<?php
// define CMSPATH - used as test definition in ALL php files to determine
// if CMS is loaded or not	
define ("CMSPATH", realpath(dirname(__FILE__)));
define ("CURPATH",CMSPATH);

//handle composer dependencies
include_once(CMSPATH . '/vendor/autoload.php');

// bootstrap CMS
require_once (CMSPATH . "/core/cms.php");


















