<?php 
defined('CMSPATH') or die; 
// prevent unauthorized access 
require_once (CMSPATH . "/core/cms.php");
?>

<html>
<meta name="viewport" content="width=device-width, user-scalable=no" />
	<head>
		<?php
			require_once(CMSPATH . "/admin/templates/clean/headlibraries.php");
		?>
		
		<?php Hook::execute_hook_actions('add_to_head'); ?>
	</head>
	<body>

			<nav class="navbar container" role="navigation" aria-label="main navigation">
				<div class="navbar-brand">
					<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/">
					<?php 
					$logo_image_id = Configuration::get_configuration_value('general_options','admin_logo');
					if ($logo_image_id) {
						$logo_src = Config::uripath() . "/image/" . $logo_image_id;
					}
					else {
						$logo_src = Config::uripath() . "/admin/templates/clean/alba_logo.webp";
					}
					?>
					<img src="<?php echo $logo_src;?>" >
					</a>

					<a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
					<span aria-hidden="true"></span>
					<span aria-hidden="true"></span>
					<span aria-hidden="true"></span>
					</a>
				</div>

				<div id="navbarBasicExample" class="navbar-menu">
					<div class="navbar-start">
						<?php
							require_once(CMSPATH . "/admin/templates/clean/navigation.php");
							Component::render_admin_nav($navigation);
						?>
					
					</div>

					<div class="navbar-end">
					<div class="navbar-item">
						<div class="buttons">
						<a target="_blank" href="<?php echo Config::uripath();?>/" class="button is-default">
							Front-End
						</a>
						<a onclick='<?php Hook::execute_hook_actions('logout_onclick_js');?>' href="<?php echo Config::uripath();?>/admin/logout.php" class="button is-light">
							Log Out <?php echo CMS::Instance()->user->username; ?>
						</a>
						</div>
					</div>
					</div>
				</div>
			</nav>
		


    <section id="main">
      	<div class="container">

	  	<?php CMS::Instance()->display_messages();?>
       
		<?php CMS::Instance()->render_controller();?>

		<?php 
		if (Config::debugwarnings()) {
			echo "<h1>Debug FYI</h1>";
			CMS::Instance()->showinfo();
		} ?>
       
		

      </div>
    </section>
	<script src='<?php echo Config::uripath();?>/admin/templates/clean/js/script.js'></script>
</body>
</html>


