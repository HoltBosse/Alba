<?php

// SeamlessCMS Installer

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



function show_error ($string, $continue=false) {
	echo "
	<head>
	<title>SeamlessCMS Installation - Failed</title>
	</head>
	<style>
	body {display:flex;align-items:center;background:#eee;}
	h1 {color:#333;font-size:7vh;text-shadow: -0.1em 0.1em 0.2em rgba(0,0,0,0.5); max-width:80vw; margin:1em auto;}
	</style>
	";
	echo "<h1>{$string}</h1>";
	if (!$continue) {
		exit(0);
	}
}

function pprint_r ($msg) {
	echo "<pre>"; print_r ($msg); echo "</pre>";
}

function change_config_file_settings ($filePath, $newSettings) {
    // Build the new file as a string
    $newFileStr = "<?php\n\ndefined('CMSPATH') or die; // prevent unauthorized access \n\n class Config {\n\n";
    foreach ($newSettings as $name => $val) {
        // Using var_export() allows you to set complex values such as arrays and also
        // ensures types will be correct
        $newFileStr .= "static \${$name} = " . var_export($val, true) . ";\n";
	}
	$newFileStr .= "\n\n}"; // close class structure
    // Write it back to the file
    file_put_contents($filePath, $newFileStr);
}

function show_all_messages ($messages) {
	foreach ($messages as $message) {
		echo "<div class='msg {$message['class']}'>";
		echo "<h5>{$message['title']}</h5>";
		echo "<p>{$message['text']}</p>";
		echo "</div>";
	}
}



function get_pdo ($dbhost, $dbname, $dbuser, $dbpass, $dbchar) {
	$dsn = "mysql:host=" . $dbhost . ";dbname=" . $dbname . ";charset=" . $dbchar;
	$options = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
		PDO::ATTR_EMULATE_PREPARES   => false,
	];
	try {
		$pdo = new PDO($dsn, $dbuser, $dbpass, $options);
	} catch (\PDOException $e) {
		return false;
	}
	return $pdo;
}





// END FUNCTIONS





// load schema.sql
$schema_sql = file_get_contents( __DIR__ . '/schema.sql');
if (!$schema_sql) {
	show_error("Failed to load schema.sql");
}

// load config 
$config_path = __DIR__ . '/../config.php';
if (!file_exists($config_path) || !is_readable($config_path) || !is_writable($config_path)) {
	show_error('Config file not found, or not readable, or not writeable');
}
// check .htaccess files in both root and admin folders
$htaccess_root_path = __DIR__ . '/../.htaccess';
if (!file_exists($htaccess_root_path)) {
	show_error('htaccess root file not found');
}
$htaccess_admin_path = __DIR__ . '/../admin/.htaccess';
if (!file_exists($htaccess_admin_path)) {
	show_error('htaccess admin file not found');
}

//define ("CMSPATH", realpath(dirname(__FILE__)));
define ("CMSPATH", realpath(dirname($config_path)));
include_once ($config_path);

// config loaded
// check db from config
$pdo = get_pdo (Config::$dbhost, Config::$dbname, Config::$dbuser, Config::$dbpass, Config::$dbchar);

if (!$pdo) {
	// config pdo not working, see if we got new credentials from user and if so try those
	if (isset($_POST['dbhost']) && isset($_POST['dbname']) && isset($_POST['dbuser']) && isset($_POST['dbpass']) && isset($_POST['dbchar']) ) {
		// TODO filter input
		// setup potential config variables
		$dbhost = $_POST['dbhost'];
		$dbname = $_POST['dbname'];
		$dbuser = $_POST['dbuser'];
		$dbpass = $_POST['dbpass'];
		$dbchar = $_POST['dbchar'];
		$sitename = $_POST['sitename'];	
		$uripath = $_POST['uripath']; // if site is in sub-folder from www-root add here
		$template = 'basic';
		$frontendlogin = false;
		$debug = false;
		$pdo = get_pdo ($dbhost,$dbname, $dbuser, $dbpass, $dbchar);
		if ($pdo) {
			// credentials worked, save config
			$newSettings = array(
				'dbhost' => $dbhost,
				'dbname' => $dbname,
				'dbuser' => $dbuser,
				'dbpass' => $dbpass,
				'dbchar' => $dbchar,
				'uripath' => $uripath,
				'sitename' => $sitename,
				'template' => $template,
				'frontendlogin' => $frontendlogin,
				'domain' => 'auto',
				'debug' => $debug,
				'session_length' => 15,
			);
			change_config_file_settings ($config_path, $newSettings);	
		}
	}
}

// used config to connect, if failed tried user input, if failed reach here with no pdo
// if ok, config saved above and pdo fine

if ($pdo) {
	// config db credentials in config at this point are good 
	$query = "select count(*) as c FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'users'";
	$stmt = $pdo->prepare($query);
	$stmt->execute(array());
	$table_count = $stmt->fetch()->c;

	if ($table_count==0) {
		// no tables found, assume install :)
		try {
			/* echo "<h1>no user table - running schema</h1>"; */
			$tables_created = $pdo->exec($schema_sql);
			/* echo "<pre>"; print_r ($schema_sql); echo "</pre>";
			echo "Result: <pre>"; print_r ($tables_created); echo "</pre>"; */
		}
		catch (PDOException $e) {
			//show_error('DB Error: ' . $e->getMessage());
			show_error('DB Error: Unable to create new tables.');
		} catch (Exception $e) {
			show_error('Unknown Error: Unable to create new tables.');
		}
	}
	else {
		echo "<h1>user table exists</h1>";
	}
	

	// got here, db ok, tables ok
	$query = "select count(*) as c from groups";
	$stmt = $pdo->prepare($query);
	$stmt->execute(array());
	$group_count = $stmt->fetch()->c;
	if ($group_count) {
		// RESTORE AFTER BUG FIXES
		show_error ("Old installation found - please delete this installation folder");
	}
	

	// setup default groups
	$query = "truncate table groups; ";
	$query .= "insert into groups (value, display) values ('admin','Administrators');";
	$query .= "insert into groups (value, display) values ('editor','Contributors');";
	$pdo->exec($query);


	// setup default template
	$query = "truncate table templates; ";
	$query .= "insert into templates (is_default, title, folder, description) values (1,'basic','basic','A very simple template to get you started.');";
	$pdo->exec($query);

	// setup default content types
	$query = "truncate table content_types; ";
	$query .= "insert into content_types (title, controller_location, description, state) ";
	$query .= "values ('Basic Article','basic_article','A simple HTML content item with a WYSIWYG editor.',1);";
	$pdo->exec($query);

	// setup default content type views
	//id	content_type_id	title	location
	$query = "truncate table content_views; ";
	$query .= "insert into content_views (content_type_id, title, location) ";
	$query .= "values (1,'Single Article','single');";
	$pdo->exec($query);
	$query .= "insert into content_views (content_type_id, title, location) ";
	$query .= "values (2,'Single Article','blog');";
	$pdo->exec($query);


	// insert default user
	$password = $_POST['password'];
	$email = $_POST['email'];
	$username = $_POST['name'];
	$hash = password_hash ($password, PASSWORD_DEFAULT);
	$query = "INSERT INTO users (username, email, password, state) VALUES (?,?,?,1)";
	$new_user_ok = $pdo->prepare($query)->execute([$username,$email,$hash]);
	// get inserted admin user id
	$admin_user_id = $pdo->lastInsertId();
	// insert user group map
	// get admin group id
	$query = "select id from groups where value='admin'";
	$stmt = $pdo->prepare($query);
	$stmt->execute(array());
	$admin_group_id = $stmt->fetch()->id;

	// insert group mapping
	$query = "insert into user_groups (user_id, group_id) values(?,?)";
	$stmt = $pdo->prepare($query);
	$stmt->execute(array($admin_group_id, $admin_user_id));
	
	if ($new_user_ok) {
		show_error ('Installation complete!');
	}
	else {
		show_error ('Database created - Error creating default user. Clear DB and try again!');
	}

	// setup default widgets
	// TODO: setup widgets
	// they are 'discovered' on first use now

	
}
else {
		// display credentials form
		if (!empty($_POST)) {
			show_error ('Database connection failed!');
		}
		if (isset($_SERVER['HTTPS']) &&
			($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
			isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
			$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
		$protocol = 'https';
		$protocalwarning = "";
		}
		else {
		$protocol = 'http';
		$protocalwarning = "";
		if ($protocol=="http") {
			$protocalwarning = "<h5 class='warning'>This URL is not secured by SSL, consider enabling this before submitting database/user credentials.</h5>";
		}
		}
		?>
	<html><head>
		<title>SeamlessCMS Installation</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<style>
			html {
				font-size:14px;
				margin:0;
				padding:0;
				background:#eee;
			}
			h5.warning {
				font-size:120%;
				color:#d77;
				margin: 1em;
			}
			h1,h2 {
				text-align:center;
			}
			h2 {
				margin:0;
				margin-bottom:0.25em;
				padding:0.5em;
			}
			form {
				margin:0 auto;
				padding:0.3rem;
				text-align:center;
			}
			#fieldsets {
				display:flex;
				justify-content:center;
			}
			.fieldset {
				margin:1rem;
				border: 2px solid white;
				padding:0.3rem;
			}
			.input-group {
				display:flex;
				flex-direction:column;
				background:#ddd;
				margin:0.7rem;
				padding:0.3rem;
			}
			p.help {
				margin:0;
				margin-top:0.25em;
				opacity:0.8;
				font-style:italic;
				font-size:75%;
				text-align:left;
				padding-left:1em;
			}
			label {
				margin-bottom:0.25em;
				font-weight:bold;
				text-align:left;
				opacity:0.8;
			}
			input[type=text],input[type=password],input[type=email] {
				padding:0.5em;
				width:50vw;
				max-width:40ch;
				border:none;
			}
			body {
				display:flex;
				flex-direction:column;
				justify-content:center;
			}
			button[type=submit] {
				font-size:120%;
				border:0;
				background:#6a6;
				color:white;
				border-radius:0.3em;
				padding:0.5em 1em;
				margin-top:1em;
			}
			.msg {
				background: #777;
				padding: 1em;
				color: white;
			}
			.msg.error {
				background:#a77;
			}
			.msg h5 {
				margin:0;
			}
			.msg p {
				margin:0;
			}
			.msg p:first-of-type {
				margin-top:1em;
			}
		</style>
	</head><body>
		
		<h1>SeamlessCMS Install</h1>
		<form method="POST" action="">
		
			<?php echo $protocalwarning; ?>
			<div id='fieldsets'>
				<div class='fieldset' id='dbinfo'>
					<h2>Database Setup</h2>
					<div class='input-group'>
						<label for='dbhost'>Database Hostname</label>
						<input type='text' maxlength=255 required value='<?php echo Config::$dbhost;?>' name='dbhost' placeholder='localhost'>
						<p class='help'>Usually localhost or 127.0.0.1</p>
					</div>
					<div class='input-group'>
						<label for='dbname'>Database Name</label>
						<input type='text' maxlength=255 required value='<?php echo Config::$dbname;?>' name='dbname' placeholder='dbname'>
					</div>
					<div class='input-group'>
						<label for='dbuser'>Database Username</label>
						<input type='text' maxlength=255 required value='<?php echo Config::$dbuser;?>' name='dbuser' placeholder='username'>
					</div>
					<div class='input-group'>
						<label for='dbpass'>Database Password</label>
						<input type='password' maxlength=255 required value='<?php echo Config::$dbpass;?>' name='dbpass' placeholder='password'>
					</div>
					<div class='input-group'>
						<label for='dbchar'>Database Character Set</label>
						<input type='text' maxlength=255 required value='<?php echo Config::$dbchar;?>' name='dbchar' placeholder='character set'>
						<p class='help'>If in any doubt, leave it as 'utf8mb4'</p>
					</div>
					
				</div>
				<div class='fieldset' id='admininfo'>
					<h2>Default User</h2>
					<div class='input-group'>
						<label for='email'>Default Admin Email</label>
						<input type='email' maxlength=255 required  name='email'  placeholder='email'>
						<p class='help'>Used for login</p>
					</div>
					<div class='input-group'>
						<label for='name'>Display Name</label>
						<input type='text' maxlength=255 required  name='name' placeholder=''>
					</div>
					<div class='input-group'>
						<label for='password'>Choose Password</label>
						<input type='password' maxlength=255 required name='password' placeholder=''>
						<p class='help'>Type carefully!</p>
					</div>
				</div>

				<div class='fieldset' id='siteinfo'>
					<h2>Site Information</h2>
					<div class='input-group'>
						<label for='sitename'>Site Name</label>
						<input type='text' maxlength=255 required name='sitename' value='<?php echo Config::$sitename;?>' placeholder='Site Name'>
					</div>
					<div class='input-group'>
						<label for='name'>Site Sub-Folder</label>
						<input type='text' maxlength=255  value='<?php echo Config::$uripath;?>' name='uripath' placeholder=''>
						<p class='help'>Path relative to root of webfolder. For most sites should be left empty.</p>
						<p class='help'>Note, no trailing slash. e.g. /foldername</p>
					</div>
				</div>

			</div>
			
			<button type='submit'>Update And Test Configuration &#xbb;</button>
			
		</form>
	</body></html>
		<?php
	
}