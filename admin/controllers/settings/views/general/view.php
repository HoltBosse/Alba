<?php
defined('CMSPATH') or die; // prevent unauthorized access

Component::addon_page_title("General Options");
?>

<?php 
//$debug = Configuration::get_configuration_value('general_options','debug'); CMS::pprint_r ($debug);
?>

<form method="POST">
	<?php
	//CMS::pprint_r ($general_options_form);
	$general_options_form->display_front_end();
	?>
	<br><br>
	<button class='btn button is-success' type='submit'>Save</button>
</form>
