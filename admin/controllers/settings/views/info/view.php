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

function embedded_phpinfo()
{
    ob_start();
    phpinfo();
    $phpinfo = ob_get_contents();
    ob_end_clean();
    $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
    echo "
        <style type='text/css'>
            #phpinfo {}
            #phpinfo pre {margin: 0; font-family: monospace;}
            #phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
            #phpinfo a:hover {text-decoration: underline;}
            #phpinfo table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc; color: black;}
            #phpinfo .center {text-align: center;}
            #phpinfo .center table {margin: 1em auto; text-align: left;}
            #phpinfo .center th {text-align: center !important;}
            #phpinfo td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
            #phpinfo h1 {font-size: 150%;}
            #phpinfo h2 {font-size: 125%;}
            #phpinfo .p {text-align: left;}
            #phpinfo .e {background-color: #ccf; width: 300px; font-weight: bold;}
            #phpinfo .h {background-color: #99c; font-weight: bold;}
            #phpinfo .v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
            #phpinfo .v i {color: #999;}
            #phpinfo img {float: right; border: 0;}
            #phpinfo hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
        </style>
        <div id='phpinfo'>
            $phpinfo
        </div>
        ";
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
	show_message ('PHP fopen Allowed','fopen is available. This is required for automatic updates and Google reCAPTCHA.','is-success');
}
else {
	show_message ('PHP fopen Allowed','fopen is <em>not</em> available. This is required for automatic updates and Google reCAPTCHA.','is-warning');
}

if ($gd_available) {
	show_message ('GD Graphics Library Available','GD is available. This is required for image manipulation.','is-success');
}
else {
	show_message ('GD Graphics Library Available','GD is <em>not</em> available. This is required for image manipulation.','is-warning');
}

if ($virtual_available) {
	show_message ('Virtual Available','virtual is available. This enables fast file-serving in PHP on Apache.','is-success');
}
else {
	show_message ('Virtual Unavailable','virtual is <em>not</em> available. This enables fast file-serving in PHP on Apache, but is not required.','is-warning');
}

if ($mail_available) {
	show_message ('PHPMail','PHPMail is available. This is required for core email functionality.','is-success');
}
else {
	show_message ('PHPMail','PHPMail is <em>not</em> available. This is required for core email functionality.','is-warning');
}

if ($mysqldump_available) {
	show_message ('MySQL Dump Available','MySQL Dump is available. This is required for backups.','is-success');
}
else {
	show_message ('MySQL Dump Available','MySQL Dump is <em>not</em> available. This is required for backups.','is-warning');
}

if ($curl_available) {
	show_message ('CURL Available','CURL is available. ','is-success');
}
else {
	show_message ('CURL Available','CURL is <em>not</em> available.','is-warning');
}

?>

<hr>

<h2 class='title is-2'>PHP Info</h2>

<?php embedded_phpinfo(); ?>