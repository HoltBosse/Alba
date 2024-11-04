<?php

header("Content-type: text/xml");

echo '<?xml version="1.0" encoding="UTF-8"?>
    <urlset
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
                http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
';

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

    echo "<url>";
        echo "<loc>https://" . $_SERVER["SERVER_NAME"] . $path . "</loc>";
        echo "<lastmod>2024-08-23T12:02:33+00:00</lastmod>";
        echo "<priority>$priority</priority>";
    echo "</url>";
}

echo "</urlset>";