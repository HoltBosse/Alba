<?php 

Use HoltBosse\Alba\Core\{CMS, Hook, Configuration, Component};
Use HoltBosse\Form\Input;

$segments = CMS::Instance()->uri_segments;
if(sizeof($segments)>0 && !CMS::isAdminController($segments[0])) {
	CMS::raise_404();
}
?>

<html>
<meta name="viewport" content="width=device-width, user-scalable=no" />
	<head>
		<?php
			require_once("headlibraries.php");
		?>

		<!--CMSHEAD-->
		
		<?php Hook::execute_hook_actions('add_to_head'); ?>
	</head>
	<body>

			<nav class="navbar container" role="navigation" aria-label="main navigation">
				<div class="navbar-brand">
					<a class="navbar-item" href="<?php echo $_ENV["uripath"];?>/admin/">
					<?php 
					$logo_image_id = Configuration::get_configuration_value('general_options','admin_logo');
					if ($logo_image_id) {
						$logo_src = $_ENV["uripath"] . "/image/" . $logo_image_id;
					}
					else {
						$logo_src = $_ENV["uripath"] . "/admin/templates/clean/alba_logo.webp";
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
							require_once("navigation.php");
							Component::render_admin_nav($navigation);
						?>
					
					</div>

					<div class="navbar-end">
					<div class="navbar-item">
						<div class="buttons">
						<a target="_blank" href="<?php echo $_ENV["uripath"];?>/" class="button is-default">
							Front-End
						</a>
						<a onclick='<?php Hook::execute_hook_actions('logout_onclick_js');?>' href="<?php echo $_ENV["uripath"];?>/admin/logout" class="button is-light">
							Log Out <?php echo Input::stringHtmlSafe(CMS::Instance()->user->username); ?>
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
		</div>
    </section>
	<script>
		<?php
			echo file_get_contents(__DIR__ . "/js/script.js");
		?>
	</script>
</body>
</html>


