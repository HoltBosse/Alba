<?php
// define CMSPATH - used as test definition in ALL php files to determine
// if CMS is loaded or not	
define ("CMSPATH", realpath(dirname(__FILE__)));
define ("ADMINPATH",false);
define ("CURPATH",CMSPATH);

// bootstrap CMS
require_once (CMSPATH . "/core/cms.php");


















