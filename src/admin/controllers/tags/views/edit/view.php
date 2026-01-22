<?php

	Use HoltBosse\Alba\Core\{CMS, Tag, Component, Content};
	Use HoltBosse\Form\{Input, Form};
	Use HoltBosse\DB\DB;
	Use HoltBosse\Alba\Components\Admin\ControlBar\ControlBar as AdminControlBar;
	Use HoltBosse\Alba\Components\CssFile\CssFile;

	//terrible hack to hide inaccessible content types from the content type selector, but keep them there so that they get saved properly
	echo "<style>";
		$contentTypes = DB::fetchAll("SELECT * FROM content_types");
		foreach($contentTypes as $ct) {
			if(Content::isAccessibleOnDomain($ct->id, $_SESSION["current_domain"])) {
				continue;
			}

			echo "
				label.checkbox[data-contenttype-id='$ct->id'] {
					display: none;
				}
			";
		}
	echo "</style>";

?>

<?php if ($new_tag):?>
	<h1 class='title'>New tag</h1>
<?php else:?>
	<h1 class='title'>Editing &ldquo;<?php echo Input::stringHtmlSafe($tag->title); ?>&rdquo; tag</h1>
<?php endif; ?>

<hr>
<?php //CMS::pprint_r ($required_details_form); ?>
<form method="POST" action="">

<div class='flex'>
	<?php $required_details_form->display(); ?>
	
</div>

<?php if ($custom_fields_form):?>
	<div class='flex'>
		<?php $custom_fields_form->display(); ?>
	</div>
<?php endif; ?>

<hr>


<?php
	(new CssFile())->loadFromConfig((object)[
		"filePath"=>__DIR__ . "/style.css",
	])->display();
?>

<?php (new AdminControlBar())->loadFromConfig((object)[])->display(); ?>
</form>

