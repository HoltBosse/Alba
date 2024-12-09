<?php
defined('CMSPATH') or die; // prevent unauthorized access

header("Content-type: text/xml");

$sitemap_data = [];
$pages = Page::get_all_pages_by_depth();
foreach($pages as $page) {
    if($page->state<=0) {
        continue;
    }

    $pageObj = new Page();
    $pageObj->load_from_id($page->id);
    $path = $pageObj->get_url();

    $priority = 1;
    if($path != "/") {
        $priority = $priority + (substr_count($path, '/') * -0.1);
    }

    $sitemap_data[] = [
        "loc"=>$_SERVER["SERVER_NAME"] . $path,
        "lastmod"=>"2024-08-23T12:02:33+00:00",
        "priority"=>$priority,
    ];
}

//TODO: add hook here so more can be injected

echo '<?xml version="1.0" encoding="UTF-8"?>
<urlset
xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
';

foreach($sitemap_data as $page_item) {
    echo "<url>";
        foreach($page_item as $element=>$item) {
            echo "<{$element}>{$item}</{$element}>";
        }
    echo "</url>";
}

echo "</urlset>";