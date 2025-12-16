<?php 

Use HoltBosse\Alba\Core\{CMS, Hook, Configuration, Component};
Use HoltBosse\Form\Input;
Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\DB\DB;
Use Respect\Validation\Validator as v;

$segments = CMS::Instance()->uri_segments;
if(sizeof($segments)>0 && !CMS::isAdminController($segments[0])) {
	CMS::raise_404();
}

if(Input::getvar('current_domain',v::intVal()) !== null) {
	$_SESSION["current_domain"] = Input::getvar('current_domain');
	header("Location: " . $_SERVER["SCRIPT_URL"]);
	die();
}

$accessToDomains = [];
foreach(CMS::Instance()->user->groups as $group) {
	if($group->domain === null) {
		continue;
	}
	$domains = explode(",", $group->domain);
	foreach($domains as $domain) {
		$accessToDomains[$domain] = true;
	}
}

if(!isset($_SESSION["current_domain"])) {

	$currentDomain = CMS::getDomainIndex($_SERVER['HTTP_HOST']);
	if(!in_array($currentDomain, array_keys($accessToDomains))) {
		//set to first accessible domain
		$currentDomain = array_key_first($accessToDomains);
	}

	$_SESSION["current_domain"] = $currentDomain;
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
						require_once(__DIR__ . "/navigation.php");
						Component::render_admin_nav($navigation);
					?>
				</div>

				<div class="navbar-end">
					<div class="navbar-item">
						<div class="buttons">
							<form>
								<?php
									if(sizeof($accessToDomains)>1) {
										$domainList = DB::fetchAll("SELECT id, display FROM `domains`", [], ["mode"=>PDO::FETCH_KEY_PAIR]);

										$domainPicker = new Select();
										$domainPicker->loadFromConfig((object) [
											"name"=>"current_domain",
											"id"=>"current_domain",
											"select_options"=>array_map(function($domainIndex) use ($domainList) {
												return (object) [
													"value"=>$domainIndex,
													"text"=>$domainList[$domainIndex] ?? "Unknown Domain",
												];
											}, array_keys($accessToDomains)),
											"default"=>$_SESSION["current_domain"],
										]);
										$domainPicker->display();
									}
								?>
								<style>
									form:has(select#current_domain) {
										margin-bottom: 0;

										div.field {
											margin: 0;

											label {
												margin: 0;
											}
										}
									}
								</style>
								<script>
									document.getElementById('current_domain').addEventListener('change', (e)=>{
										e.target.closest("form").submit();
									});
								</script>
							</form>
							<a target="_blank" href="<?php echo $_ENV["uripath"];?>/" class="button is-default">
								Front-End
							</a>
							<a onclick='<?php Hook::execute_hook_actions('logout_onclick_js');?>' href="<?php echo $_ENV["uripath"];?>/admin/logout" class="button is-light">
								Log Out
							</a>
						</div>
					</div>
				</div>
			</div>
		</nav>
		<main id="main">
			<div class="container">

				<?php CMS::Instance()->display_messages();?>
			
				<?php CMS::Instance()->render_controller();?>
			</div>
		</main>
		<script>
			<?php
				echo file_get_contents(__DIR__ . "/js/script.js");
			?>
		</script>
	</body>
</html>


