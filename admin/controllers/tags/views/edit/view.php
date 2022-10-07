<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<?php if ($new_tag):?>
	<h1 class='title'>New tag</h1>
<?php else:?>
	<h1 class='title'>Editing &ldquo;<?php echo $tag->title; ?>&rdquo; tag</h1>
<?php endif; ?>

<hr>
<?php //CMS::pprint_r ($required_details_form); ?>
<form method="POST" action="">

<div class='flex'>
	<?php $required_details_form->display_front_end(); ?>
	
</div>

<?php if ($custom_fields_form):?>
	<div class='flex'>
		<?php $custom_fields_form->display_front_end(); ?>
	</div>
<?php endif; ?>

<hr>


<style>
div.flex {display:flex; flex-wrap:wrap;}
div.flex > * {padding-left:2rem; padding-bottom:2rem;}
/* div.flex > div:first-child {padding-left:0;} */
div.flex > * {min-width:2rem;}
</style>

<div class='fixed-control-bar'>
	<button class='button is-primary' type='submit'>Save</button>
	<button class='button is-warning' type='button' onclick="window.history.back();">Cancel</button>
</div>
</form>

