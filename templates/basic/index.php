<?php
	defined('CMSPATH') or die; // prevent unauthorized access
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<!--CMSHEAD-->
	
	<style>
		<?php echo file_get_contents(CMSPATH . "/templates/basic/style.css"); ?>
	</style>
</head>
<body>
	<nav>
		<?php CMS::Instance()->render_widgets('Top Nav');?>
	</nav>
	<?php //CMS::pprint_r (CMS::Instance()->page);?>
	<aside id="sidebar">
		<?php CMS::Instance()->render_widgets('Sidebar');?>
	</aside>

	<div id='messages'>
	<?php CMS::Instance()->display_messages();?>
	</div>

	<?php CMS::Instance()->render_widgets('Header');?>

	<?php CMS::Instance()->render_widgets('Above Content');?>
	
	<?php CMS::Instance()->render_controller(); ?>

	<?php CMS::Instance()->render_widgets('After Content');?>
	
	<footer>
		<?php CMS::Instance()->render_widgets('Footer');?>
	</footer>
	<?php if (Config::debugwarnings()) { CMS::Instance()->showinfo();} ?>
</body>
</html>