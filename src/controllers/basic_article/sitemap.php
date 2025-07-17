<?php

Use HoltBosse\Alba\Core\{CMS, Content, Hook, Page, Template};
Use HoltBosse\DB\DB;

$view = Content::get_view_location($page->content_view);

$template_folder = Template::get_default_template()->folder;
if($page->template!=0) {
    $template_folder = DB::fetch("SELECT * FROM templates WHERE id=?", $page->template)->folder;
}

$potential_override_sitemap = Template::getTemplatePath($template_folder) . "/overrides/" . $location . "/" . $view . "/sitemap.php";
if(file_exists($potential_override_sitemap)) {
    return require($potential_override_sitemap);
} elseif(file_exists(Template::getTemplatePath($template_folder) . "/controllers/$location/views/$view/sitemap.php")) {
    return require(Template::getTemplatePath($template_folder) . "/controllers/$location/views/$view/sitemap.php");
}

return [];