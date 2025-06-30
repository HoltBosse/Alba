<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<style>
	<?php echo file_get_contents(CMSPATH . "/admin/controllers/content/views/edit/style.css"); ?>
</style>

<?php if ($new_content):?>
	<h1 class='title'>New Content</h1>
<?php else:?>
	<?php echo "<script>var content_id=" . $content_id . "</script>"; ?>
	<?php if ($version_count>0):?>
		<a href='<?php echo Config::uripath();?>/admin/content/versions/<?php echo $content_id;?>' class='btn button cta pull-right'>Versions</a>
	<?php endif; ?>
	<h1 class='title'>Editing &ldquo;<?php echo Input::stringHtmlSafe($content->title); ?>&rdquo; - <?php echo Content::get_content_type_title($content->content_type);?></h1>
	
<?php endif; ?>

<hr>

<form method="POST" action="" enctype="multipart/form-data">
<input type="hidden" name="http_referer_form" value="<?php echo $_SERVER['HTTP_REFERER'];?>">

<a href='#' class='toggle_siblings'>show/hide required fields</a>
<div class='toggle_wrap '>
	<div class='flex'>
		<?php $required_details_form->display(); ?>
	</div>
</div>

<hr>

<?php $content_form->display(); ?>

<hr>

<?php
	$otherButton = '<button title=\'Save and keep working!\' class="button is-info" name="quicksave" value="quicksave" type="submit">Quick Save</button>';
	Component::create_fixed_control_bar($otherButton);
?>


</form>

