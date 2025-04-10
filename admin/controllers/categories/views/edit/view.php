<?php
defined('CMSPATH') or die; // prevent unauthorized access
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
		<?php $required_details_form->display_front_end(); ?>
	</div>
</div>

<?php if ($custom_fields_form):?>
	<div class='flex'>
		<?php $custom_fields_form->display_front_end(); ?>
	</div>
<?php endif; ?>

<hr>



<style>
	<?php echo file_get_contents(CMSPATH . "/admin/controllers/categories/views/edit/style.css"); ?>
</style>

<div class="fixed-control-bar">
	<button class='button is-primary' type='submit'>Save</button>
	<button class="button is-warning" type="button" onclick="window.history.back();">Cancel</button>
</div>
</form>

