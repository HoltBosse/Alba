<?php
defined('CMSPATH') or die; // prevent unauthorized access

$sitemap_data = [];

$viewOptions = json_decode($page->content_view_configuration);
$normalizedOptions = array_combine(array_column($viewOptions, 'name'), array_column($viewOptions, 'value'));

$query = "  SELECT cbh.alias, cbh.start, ua.date
            FROM controller_basic_html cbh
            LEFT JOIN (
                SELECT id, userid, MAX(date) AS date, type, json
                FROM user_actions
                WHERE (type='contentcreate' OR type='contentupdate' OR type='contentdelete')
                AND JSON_EXTRACT(json, '$.content_type')=1
                GROUP BY JSON_EXTRACT(json, '$.content_id')
            ) ua ON JSON_EXTRACT(ua.json, '$.content_id')=cbh.id
            WHERE 1";
$params = [];

if($normalizedOptions["blogtag"] && is_numeric($normalizedOptions["blogtag"])) {
    $query .= " AND cbh.id IN (SELECT content_id FROM tagged WHERE content_type_id=1 AND tag_id=?)";
    $params[] = $normalizedOptions["blogtag"];
}


$articles = DB::fetchAll($query, $params);
//CMS::pprint_r($articles);

foreach($articles as $item) {
    //$maxpriority

    $sitemap_data[] = [
        "loc"=>$path . "/" . $item->alias,
        "lastmod"=>Date("Y-m-d\Th:i:s+00:00", strtotime($item->date ?? $item->start)),
        "priority"=>$maxpriority,
    ];
}

return $sitemap_data;