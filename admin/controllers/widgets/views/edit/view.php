<?php
defined('CMSPATH') or die; // prevent unauthorized access
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
<a href='#' class='toggle_siblings'>show/hide required fields</a>
<div class='toggle_wrap <?php if (!$new_widget) { echo " hidden ";}?>'>
	<div class='flex'>
		<?php $required_details_form->display_front_end(); ?>
	</div>
</div>

<hr>
<h5 class='title'>Main Widget Setup</h5>

<?php 
$widget_options_form->display_front_end();
$widget->render_custom_backend();
?>
<style>
div.flex {display:flex;}
div.flex > * {padding-left:2rem;}
div.flex > div:first-child {padding-left:0;}
</style>

<hr>
<h6 class='title'>Position Options</h6>
<p class='help'>Choose a default template position and pages where this widget will appear.</p>
<br>
<div class='flex'>
	<?php $position_options_form->display_front_end(); ?>
</div>

<hr>
<div class='fixed-control-bar'>
		<button class='button is-primary' type='submit'>Save</button>
		<button class='button is-warning' type='button' onclick="window.history.back();">Cancel</button>
	</div>
</form>

