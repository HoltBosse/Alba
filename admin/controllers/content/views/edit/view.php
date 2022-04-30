<?php
defined('CMSPATH') or die; // prevent unauthorized access
//CMS::pprint_r ($content);
?>

<?php if ($new_content):?>
	<h1 class='title'>New Content</h1>
<?php else:?>
	<?php echo "<script>var content_id=" . $content_id . "</script>"; ?>
	<?php if ($version_count>0):?>
		<a href='<?php echo Config::$uripath;?>/admin/content/versions/<?php echo $content_id;?>' class='btn button cta pull-right'>Versions</a>
	<?php endif; ?>
	<h1 class='title'>Editing &ldquo;<?php echo $content->title; ?>&rdquo; - <?php echo Content::get_content_type_title($content->content_type);?></h1>
	
<?php endif; ?>



<hr>

<form method="POST" action="">

<a href='#' class='toggle_siblings'>show/hide required fields</a>
<div class='toggle_wrap '>
	<div class='flex'>
		<?php $required_details_form->display_front_end(); ?>
	</div>
</div>

<hr>

<?php $content_form->display_front_end(); ?>

<hr>

<style>
div.flex {display:flex; flex-wrap:wrap;}
div.flex > * {padding-left:2rem; padding-bottom:2rem;}
/* div.flex > div:first-child {padding-left:0;} */
div.flex > * {min-width:2rem;}
</style>


<div class="fixed-control-bar">
	<button title='Save and exit' class="button is-primary" type="submit">Save</button>
	<button title='Save and keep working!' class="button is-info" name="quicksave" value="quicksave" type="submit">Quick Save</button>
	<button class="button is-warning" type="button" onclick="window.history.back();">Cancel</button>
</div>


</form>

