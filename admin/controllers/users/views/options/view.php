<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>
<h1 class='title is-1'>User Options</h1>

<form method="POST">
	<?php
	//CMS::pprint_r ($user_options_form);
	$user_options_form->display_front_end();
	?>
	<br><br>
	<button class='btn button is-success' type='submit'>Save</input>
</form>
