<?php

Use HoltBosse\Alba\Core\{Content, Component};
Use HoltBosse\Form\Input;
Use HoltBosse\Alba\Components\Admin\ControlBar\ControlBar as AdminControlBar;
Use HoltBosse\Alba\Components\CssFile\CssFile;

//CMS::pprint_r ($cat);
?>

<?php if ($new_cat):?>
	<h1 class='title'>New &ldquo;<?php echo Content::get_content_type_title($cat->content_type);?>&rdquo; Category</h1>
<?php else:?>
	<h1 class='title'>Editing &ldquo;<?php echo Input::stringHtmlSafe($cat->title); ?>&rdquo; - A &ldquo;<?php echo Content::get_content_type_title($cat->content_type);?>&rdquo; Category</h1>
<?php endif; ?>

<hr>

<form method="POST" action="">


<div class=''>
	<div class='flex'>
		<?php $required_details_form->display(); ?>
	</div>
</div>

<?php if ($custom_fields_form):?>
	<div class='flex'>
		<?php $custom_fields_form->display(); ?>
	</div>
<?php endif; ?>

<hr>

<?php
	(new CssFile())->loadFromConfig((object)[
		"filePath"=>__DIR__ . "/style.css",
	])->display();

	(new AdminControlBar())->loadFromConfig((object)[])->display();
?>
</form>

