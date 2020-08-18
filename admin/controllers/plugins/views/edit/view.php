<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>


<h1 class='title'>Editing &ldquo;<?php echo $plugin->title; ?>&rdquo; Plugin</h1>
<p class='help'><?php echo $plugin->description;?></p>


<hr>

<form method="POST" action="">

<h5 class='is-5 title'>Plugin Options</h5>

<?php 
$plugin_options_form->display_front_end();

?>
<style>
div.flex {display:flex;}
div.flex > * {padding-left:2rem;}
div.flex > div:first-child {padding-left:0;}
</style>

<div class='fixed-control-bar'>
		<button class='button is-primary' type='submit'>Save</button>
		<button class='button is-warning' type='button' onclick="window.history.back();">Cancel</button>
	</div>
</form>

