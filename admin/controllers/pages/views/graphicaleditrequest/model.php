<?php
defined('CMSPATH') or die; // prevent unauthorized access
ob_get_clean();
ob_get_clean();

$segments = CMS::Instance()->uri_segments;
//CMS::pprint_r($segments);

if($segments[2]=="gettemplate" && sizeof($segments)==4) {
    require_once(CMSPATH . "/templates/{$segments[3]}/index.php");
    die;
}

die;