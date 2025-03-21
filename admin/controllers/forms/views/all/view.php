<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<style>
	<?php echo file_get_contents(CMSPATH . "/admin/controllers/forms/views/all/style.css"); ?>
</style>

<h1 class="title is-1">All Forms
	<a class="is-primary pull-right button btn" href="/admin/forms/edit/new">New Form</a>
	<span class="unimportant subheading">Create Forms</span>
	<?php Component::addon_button_group("content_operations", "forms", ["publish"=>"primary","unpublish"=>"warning","duplicate"=>"info","delete"=>"danger"]); ?>
</h1>

<form>
	<?php
		$searchForm->display_front_end();
	?>
</form>

<?php
	CMS::pprint_r($formItems);
?>