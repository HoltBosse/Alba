<?php

Use HoltBosse\Alba\Core\{Content, Component};
Use HoltBosse\Form\Input;

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



<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>
</style>

<?php Component::create_fixed_control_bar(); ?>
</form>

