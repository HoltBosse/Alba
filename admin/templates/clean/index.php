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

						<?php if (Access::can_access(Admin_Config::$access["settings"])):?>
						<div class="navbar-item has-dropdown is-hoverable">
							<a class="navbar-link">System</a>
							<div class="navbar-dropdown">
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/settings/general">General Settings</a>
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/settings/updates">Check For Updates</a>
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/settings/info">System Information</a>
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/settings/backups">Backups</a>
							</div>
						</div>
						<?php endif; ?>

						<?php if (Access::can_access(Admin_Config::$access["users"])):?>
						<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/users" class="navbar-link">Users</a>
						<?php endif; ?>

						<?php if (Access::can_access(Admin_Config::$access["pages"])):?>
						<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/pages/">Pages</a>
						<?php endif; ?>

						<?php if (Access::can_access(Admin_Config::$access["content"])):?>
						<div class="navbar-item has-dropdown is-hoverable">
							<a class="navbar-link">Content</a>
							<div class="navbar-dropdown">
								<?php foreach (Content::get_all_content_types() as $content_type):?>
									<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/content/all/<?php echo $content_type->id;?>"><?php echo $content_type->title;?></a>
								<?php endforeach; ?>
								<!-- removed all content view -->
								<hr class="dropdown-divider">
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/content/check">Check Flat Tables</a>
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/content/new">Create New Type</a>
							</div>
						</div>
						<?php endif; ?>

						<?php if (Access::can_access(Admin_Config::$access["widgets"])):?>
						<!--<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/controllers/all">Controllers</a>-->
						<div class="navbar-item has-dropdown is-hoverable">
							<a class="navbar-link">Widgets</a>
							<div class="navbar-dropdown">
								<?php foreach (Widget::get_all_widget_types() as $widget_type):?>
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/widgets/show/<?php echo $widget_type->id; ?>"><?php echo $widget_type->title; ?></a>
								<?php endforeach; ?>
								<hr class="dropdown-divider">
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/widgets/show/">All Widgets</a>
							</div>
						</div>
						<?php endif; ?>

						<?php if (Access::can_access(Admin_Config::$access["plugins"])):?>
						<div class="navbar-item has-dropdown is-hoverable">
							<a class="navbar-link">Plugins</a>
							<div class="navbar-dropdown">
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/plugins/show/">All Plugins</a>
								<hr class="dropdown-divider">
								<?php foreach (Plugin::get_all_plugins() as $plugin):?>
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/plugins/edit/<?php echo $plugin->id; ?>"><?php echo $plugin->title; ?></a>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endif; ?>

						<?php if (Access::can_access(Admin_Config::$access["tags"])):?>
						<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/tags">Tags</a>
						<div class="navbar-item has-dropdown is-hoverable">
							<a class="navbar-link">Media</a>
							<div class="navbar-dropdown">
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/images/show/">Manage Images</a>
								
								<hr class="dropdown-divider">
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/images/discover">Process FTP/Uploaded Images</a>
								
							</div>
						</div>
						<?php endif; ?>

						<?php if (Access::can_access(Admin_Config::$access["categories"])):?>
						<div class="navbar-item has-dropdown is-hoverable">
							<a href="<?php echo Config::uripath();?>/admin/categories/all"class="navbar-link">Categories</a>
							<div class="navbar-dropdown">
								<?php foreach (Content::get_all_content_types() as $content_type):?>
									<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/categories/all/<?php echo $content_type->id;?>"><?php echo $content_type->title;?></a>
								<?php endforeach; ?>
								<hr class="dropdown-divider">
								<a class="navbar-item" href="<?php echo Config::uripath();?>/admin/categories/all/-3">Tags</a>
							</div>
						</div>
						<?php endif; ?>
					
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
			CMS::showinfo();
		} ?>
       
		

      </div>
    </section>
	<script src='<?php echo Config::uripath();?>/admin/templates/clean/js/script.js'></script>
</body>
</html>


