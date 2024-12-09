<?php
defined('CMSPATH') or die; // prevent unauthorized access

$view = Content::get_view_location($page->content_view);

$template_folder = Template::get_default_template()->folder;
if($page->template!=0) {
    $template_folder = DB::fetch("SELECT * templates WHERE id=?", $page->template)->folder;
}

$potential_override_sitemap = CMSPATH . "/templates/" . $template_folder . "/overrides/" . $location . "/" . $view . "/sitemap.php";
if(file_exists($potential_override_sitemap)) {
    return require($potential_override_sitemap);
} elseif(file_exists(CMSPATH . "/controllers/$location/views/$view/sitemap.php")) {
    return require(CMSPATH . "/controllers/$location/views/$view/sitemap.php");
}

return [];