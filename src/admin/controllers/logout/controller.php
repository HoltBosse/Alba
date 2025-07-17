<?php

ob_get_clean();
ob_get_clean();

session_start();
session_destroy();
$_SESSION = [];
//print_r ($_SESSION);
header('Location: ' . $_ENV["uripath"] . "/admin");