<?php
defined('CMSPATH') or die; // prevent unauthorized access
//CMS::pprint_r ($cat);
?>

<?php if ($new_cat):?>
	<h1 class='title'>New &ldquo;<?php echo Content::get_content_type_title($cat->content_type);?>&rdquo; Category</h1>
<?php else:?>
	<?php echo "<script>var content_id=" . $content_id . "</script>"; ?>
	<h1 class='title'>Editing &ldquo;<?php echo $cat->title; ?>&rdquo; - A &ldquo;<?php echo Content::get_content_type_title($cat->content_type);?>&rdquo; Category</h1>
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
div.flex {display:flex; flex-wrap:wrap;}
div.flex > * {padding-left:2rem; padding-bottom:2rem;}
/* div.flex > div:first-child {padding-left:0;} */
div.flex > * {min-width:2rem;}
</style>


<button class='button is-primary' type='submit'>Save</button>
</form>

