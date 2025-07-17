<?php

Use HoltBosse\Alba\Core\{CMS, Plugin, Component};

?>


<h1 class='title'>Editing &ldquo;<?php echo $plugin->title; ?>&rdquo; Plugin</h1>
<p class='help'><?php echo $plugin->description;?></p>


<hr>

<form method="POST" action="">

<h5 class='is-5 title'>Plugin Options</h5>

<?php 
$plugin_options_form->display();

?>
<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>
</style>

<?php Component::create_fixed_control_bar(); ?>
</form>

