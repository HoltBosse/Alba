<?php

Use HoltBosse\Alba\Core\{CMS, Template, JSON};
Use HoltBosse\Alba\Components\CssFile\CssFile;

// CMS::$edit_page_id set in /admin/controllers/pages/views/edit/model.php
// will be zero if editing new page
$edit_page_id = CMS::Instance()->edit_page_id;

//$template->positions = ['Header','Above Content','After Content','Sidebar','Footer'];
//$template->positions = json_decode(file_get_contents(__DIR__ . '/positions.json'));
$template->positions = JSON::load_obj_from_file(__DIR__ . '/positions.json');

(new CssFile())->loadFromConfig((object)[
	"filePath"=>__DIR__ . "/layout.css",
	"injectIntoHead"=>false,
])->display();

?>

<div id='hbcms_layout_wrap'>
	<section id="top nav">
		<?php $template->output_widget_admin('Top Nav', $edit_page_id);?>
	</section>
	<section id="header">
		<?php $template->output_widget_admin('Header', $edit_page_id);?>
	</section>
	<section id="main" class='multiple'>
		<div id="content">
			<?php $template->output_widget_admin('Above Content', $edit_page_id);?>
			<p>Loreum Ipsum</p>
			<p>End of Content</p>
			<?php $template->output_widget_admin('After Content', $edit_page_id);?>
		</div>
		<aside id="sidebar">
			<?php $template->output_widget_admin('Sidebar', $edit_page_id);?>
		</aside>
	</section>
	<section id="footer">
		<?php $template->output_widget_admin('Footer', $edit_page_id);?>
	</section>
</div>