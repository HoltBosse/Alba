<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<?php if ($new_tag):?>
	<h1 class='title'>New tag</h1>
<?php else:?>
	<h1 class='title'>Editing &ldquo;<?php echo $tag->title; ?>&rdquo; tag</h1>
<?php endif; ?>

<hr>

<form method="POST" action="">

<div class='flex'>
	<?php $required_details_form->display_front_end(); ?>
</div>

<hr>


<style>
	<?php echo require_once(CMSPATH . "/admin/controllers/images/views/edit/style.css"); ?>
</style>


<button class='button is-primary' type='submit'>Save</button>
</form>

