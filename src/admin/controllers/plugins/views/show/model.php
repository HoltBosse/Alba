<?php

Use HoltBosse\Alba\Core\{CMS, Plugin};


$segments = CMS::Instance()->uri_segments;
if(sizeof($segments) > 2) {
    CMS::raise_404();
}

$all_plugins = Plugin::get_all_plugins();

