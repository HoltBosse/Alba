<?php

use HoltBosse\Alba\Core\{CMS, Content, Hook, Page};

header("Content-type: text/xml");

//this exists to limit the scope of what is accessible to the file
function get_page_details(string $location, object $page, float $maxpriority, string $path, string $contentControllerPath): mixed {
    return require($contentControllerPath . "/sitemap.php");
}

$sitemap_data = [];
$pages = Page::get_all_pages_by_depth();

$pages = array_filter($pages, function($page) {
    return $page->domain == CMS::getDomainIndex($_SERVER["HTTP_HOST"]);
});

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

    $path = "https://" . $_SERVER["SERVER_NAME"] . $path;
    $sitemap_data[] = [
        "loc"=>$path,
        "lastmod"=>Date("Y-m-d\Th:i:s+00:00", strtotime($page->updated)),
        "priority"=>$priority,
    ];

    if($page->content_type!=-1) {
        $location = Content::get_content_location($page->content_type);
        if(file_exists(Content::getContentControllerPath($location) . "/sitemap.php")) {
            //CMS::pprint_r($page);

            $page_contents = get_page_details($location, $page, ($priority-0.1), $path, Content::getContentControllerPath($location));
            $sitemap_data = array_merge($sitemap_data, $page_contents);
        }
    }
}

//CMS::pprint_r($sitemap_data); die;

$sitemap_data = Hook::execute_hook_filters('render_sitemap', $sitemap_data);

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