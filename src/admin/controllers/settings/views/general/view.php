<?php

Use HoltBosse\Alba\Core\{CMS, Configuration, Component};
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;

(new TitleHeader())->loadFromConfig((object)[
	"header"=>"General Options",
])->display();
?>

<?php 
//$debug = Configuration::get_configuration_value('general_options','debug'); CMS::pprint_r ($debug);
?>

<form method="POST">
	<?php
	//CMS::pprint_r ($general_options_form);
	$general_options_form->display();
	?>
	<button class='btn button is-success' type='submit'>Save</button>
</form>

<br><br><hr><br><br>

<?php

(new TitleHeader())->loadFromConfig((object)[
	"header"=>"Test Email Settings",
])->display();

echo "<form><button type='submit' class='btn button is-info' name='send_test_email' value='true'>Send Test Email</button></form>";