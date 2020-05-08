<?php
// TODO: make this a controller so we have access to config to make secure redirect
session_start();
session_destroy();
$_SESSION = array();
//print_r ($_SESSION);
header('Location: ' . "/admin");