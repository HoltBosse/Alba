<?php

	Use HoltBosse\Alba\Core\{CMS, Plugin, Component};
	Use HoltBosse\Alba\Components\Admin\ControlBar\ControlBar as AdminControlBar;
	Use HoltBosse\Alba\Components\CssFile\CssFile;

?>


<h1 class='title'>Editing &ldquo;<?php echo $plugin->title; ?>&rdquo; Plugin</h1>
<p class='help'><?php echo $plugin->description;?></p>


<hr>

<form method="POST" action="">

<h5 class='is-5 title'>Plugin Options</h5>

<?php 
	$plugin_options_form->display();

	(new CssFile())->loadFromConfig((object)[
		"filePath"=>__DIR__ . "/style.css",
	])->display();

	(new AdminControlBar())->loadFromConfig((object)[])->display();
?>
</form>

