<?php
defined('CMSPATH') or die; // prevent unauthorized access
//CMS::pprint_r ($content);
?>

<?php if ($new_content):?>
	<h1 class='title'>New Redirect</h1>
<?php else:?>
	<?php echo "<script>var redirect_id=" . $content_id . "</script>"; ?>

	<h1 class='title'>Editing Redirect</h1>
	
<?php endif; ?>



<hr>

<form method="POST" action="" enctype="multipart/form-data">


<div class=' '>
	<div class='flex'>
		<?php $required_details_form->display_front_end(); ?>
	</div>
</div>


<hr>

<style>
div.flex {display:flex; flex-wrap:wrap;}
div.flex > * {padding-left:2rem; padding-bottom:2rem;}
/* div.flex > div:first-child {padding-left:0;} */
div.flex > * {min-width:2rem;}
</style>


<div class="fixed-control-bar">
	<button title='Save and exit' class="button is-primary" type="submit">Save</button>
	<button class="button is-warning" type="button" onclick="window.history.back();">Cancel</button>
</div>


</form>

