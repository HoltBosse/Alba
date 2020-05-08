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
div.flex {display:flex; flex-wrap:wrap;}
div.flex > * {padding-left:2rem; padding-bottom:2rem;}
/* div.flex > div:first-child {padding-left:0;} */
div.flex > * {min-width:2rem;}
</style>


<button class='button is-primary' type='submit'>Save</button>
</form>

