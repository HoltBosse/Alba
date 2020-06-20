<?php
	defined('CMSPATH') or die; // prevent unauthorized access
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title><?php echo $this->page->title;?> | <?php echo Config::$sitename; ?></title>
	<?php if (Configuration::get_configuration_value ('general_options', 'og_enabled')):?>
		<?php 
		$og_title = $this->page->get_page_option_value("og_title") ? $this->page->get_page_option_value("og_title") : $this->page->title; 
		?>
		<meta property="og:title" content="<?php echo $og_title; ?>" />
	<?php endif; ?>
	<style>
		.menu_items_by_tag_wrap {
			display: flex;
			flex-wrap: wrap;
			/* justify-content: space-evenly; */
			justify-content:center;
		}
		.menu_item_wrap {
			width: 15em;
			margin: 1em;
			background: rgba(0,0,0,0.2);
			padding: 1em;
		}
		.menu_item_wrap.wider {
			width:30em;
		}
		.menu_item_wrap h1 {
			margin-top:0;
			line-height:0.9;
		}
		.menu_item_wrap p {
			margin-bottom:0;
		}
		
		footer {margin:1rem; padding:1rem; background:#eee; border:1px solid black; }
	</style>
</head>
<body>
	<nav>
		<?php CMS::Instance()->render_widgets('Top Nav');?>
	</nav>
	
	<aside id="sidebar">
		<?php CMS::Instance()->render_widgets('Sidebar');?>
	</aside>

	<?php CMS::Instance()->render_widgets('Above Content');?>
	
	<?php CMS::Instance()->render_controller(); ?>
	
	<footer>
		<?php CMS::Instance()->render_widgets('Footer');?>
	</footer>
	<?php if (Config::$debug) { CMS::Instance()->showinfo();} ?>
</body>
</html>