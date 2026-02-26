<?php

Use HoltBosse\Alba\Core\{CMS, Widget, Component, File};
Use HoltBosse\Form\Form;
Use HoltBosse\Form\Input;
Use HoltBosse\Alba\Components\Admin\ControlBar\ControlBar as AdminControlBar;
Use HoltBosse\Alba\Components\CssFile\CssFile;

?>

<?php if ($new_widget):?>
	<h1 class='title'>New &ldquo;<?php echo $widget->type->title;?>&rdquo; Widget</h1>
	<p class='help'><?php echo $widget->type->description;?></p>
<?php else:?>
	<h1 class='title'>Editing &ldquo;<?php echo $widget->title; ?>&rdquo; Widget</h1>
	<p class='help'><?php echo $widget->type->description;?></p>
<?php endif; ?>

<hr>

<form method="POST" action="">
<input type="hidden" name="http_referer_form" value="<?php echo $_SERVER['HTTP_REFERER'];?>">
<div class='toggle_wrap'>
	<div class='flex'>
		<?php $required_details_form->display(); ?>
	</div>
</div>

<hr>
<h5 class='title'>Options</h5>

<?php 
	$widget_options_form->display();
	$widget->render_custom_backend();

	(new CssFile())->loadFromConfig((object)[
		"filePath"=>__DIR__ . "/style.css",
	])->display();

?>

<hr>
<h6 class='title'>Position Options</h6>
<p class='help'>Choose a default template position and pages where this widget will appear.</p>
<br>
<div class='flex'>
	<?php $position_options_form->display(); ?>
</div>

<script>
	<?php echo File::getContents(__DIR__ . "/script.js"); ?>
</script>

<hr>
<?php (new AdminControlBar())->loadFromConfig((object)[])->display(); ?>
</form>

