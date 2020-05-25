<?php
defined('CMSPATH') or die; // prevent unauthorized access

?>

<style>
	p.help {
		margin-left:2ch;
	}
	p.help.success { color:green;}
	p.help.default { color:blue;}
	p.help.warning { color:orange;}
	p.help.error { color:red;}
	h5 {
		margin-top:1em;
	}
	p {
		margin-top:0.5em;
	}
</style>

<form action='' method='post' name='check_fields' id='check_fields_form'>

	<h1 class='title is-1'>New Content Type</h1>

	<?php $new_content_type_form->display_front_end(); ?>

	<div class="fixed-control-bar">
		<button class="button is-primary" type="submit">Save</button>
		<button class="button is-warning" type="button" onclick="window.history.back();">Cancel</button>
	</div>

</form>

<script>
	
</script>