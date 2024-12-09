<?php
defined('CMSPATH') or die; // prevent unauthorized access

$sitemap_data = [];

$viewOptions = json_decode($page->content_view_configuration);
$normalizedOptions = array_combine(array_column($viewOptions, 'name'), array_column($viewOptions, 'value'));

$query = "  SELECT cbh.alias
            FROM controller_basic_html cbh
            WHERE 1";
$params = [];

if($normalizedOptions["blogtag"] && is_numeric($normalizedOptions["blogtag"])) {
    $query .= " AND cbh.id IN (SELECT content_id FROM tagged WHERE content_type_id=1 AND tag_id=?)";
    $params[] = $normalizedOptions["blogtag"];
}

$articles = DB::fetchall($query, $params);
foreach($articles as $item) {
    //$maxpriority

    $sitemap_data[] = [
        "loc"=>$path . "/" . $item->alias,
        "lastmod"=>"2024-08-23T12:02:33+00:00",
        "priority"=>$maxpriority,
    ];
}

return $sitemap_data;