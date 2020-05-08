<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;

$view = $segments[1];

// load model + view

$user_controller = new Controller(realpath(dirname(__FILE__)),$view);
$user_controller->load_view($view);


