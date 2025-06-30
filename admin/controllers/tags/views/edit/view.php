<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<?php if ($new_tag):?>
	<h1 class='title'>New tag</h1>
<?php else:?>
	<h1 class='title'>Editing &ldquo;<?php echo Input::stringHtmlSafe($tag->title); ?>&rdquo; tag</h1>
<?php endif; ?>

<hr>
<?php //CMS::pprint_r ($required_details_form); ?>
<form method="POST" action="">

<div class='flex'>
	<?php $required_details_form->display(); ?>
	
</div>

<?php if ($custom_fields_form):?>
	<div class='flex'>
		<?php $custom_fields_form->display(); ?>
	</div>
<?php endif; ?>

<hr>


<style>
	<?php echo file_get_contents(CMSPATH . "/admin/controllers/tags/views/edit/style.css"); ?>
</style>

<?php Component::create_fixed_control_bar(); ?>
</form>

