<?php
// TODO: make this a controller so we have access to config to make secure redirect
define ("CMSPATH", $_SERVER['DOCUMENT_ROOT']); //faking this for now
require_once("../config.php");
session_start();
session_destroy();
$_SESSION = [];
//print_r ($_SESSION);
header('Location: ' . Config::uripath() . "/admin");