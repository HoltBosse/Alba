<?php
defined('CMSPATH') or die; // prevent unauthorized access

// TODO: move this to admin function
function show_message ($heading, $text, $class) {
	echo "<article class=\"message $class\">
	<div class=\"message-header\">
		<p>$heading</p>
		<button class=\"delete\" aria-label=\"delete\"></button>
	</div>
	<div class=\"message-body\">
		$text
	</div>
</article>";
}
?>

<h1 class='title is-1'>System Information</h1>

<?php 


if ($native_zip) {
	show_message ('Native Zip','Native zip handling is available. This is required for automatic update installation.','is-success');
}
else {
	show_message ('Native Zip','Native zip handling is <em>not</em> available. This is required for automatic update installation.','is-warning');
}

if ($allow_fopen) {
	show_message ('PHP fopen Allowed','fopen is available. This is required for automatic update downloads.','is-success');
}
else {
	show_message ('PHP fopen Allowed','fopen is <em>not</em> available. This is required for automatic update downloads.','is-warning');
}

if ($gd_available) {
	show_message ('GD Graphics Library Available','GD is available. This is required for image manipulation.','is-success');
}
else {
	show_message ('GD Graphics Library Available','GD is <em>not</em> available. This is required for image manipulation.','is-warning');
}

?>

<hr>

<h2 class='title is-2'>PHP Info</h2>

<?php phpinfo(); ?>

