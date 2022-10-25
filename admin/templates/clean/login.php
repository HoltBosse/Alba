<?php defined('CMSPATH') or die; // prevent unauthorized access 
$view = Input::getvar('view','STRING');

// handle reset request

$resetemail = Input::getvar('resetemail','EMAIL');
if ($resetemail) {
	$reset_user = new User();
	$reset_user->load_from_email($resetemail);

	if ($reset_user && $reset_user->username != 'guest') {
		$key = $reset_user->generate_reset_key();
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$domain = $_SERVER['HTTP_HOST'].'/';
		$domain_url = $protocol.$domain;
		$link = $domain_url . "admin/?resetkey=" . $key;
		$markup = "
		<h5>Hi {$reset_user->username}</h5>
		<p>A password reset has been request on " . Config::sitename() . "</p>
		<p>Click <a target='_blank' href='{$link}'>here</a> to choose a new password.</p>
		<p>If you did not initiate this request, please ignore this email.</p>
		";
		$mail = new Mail();	
		$mail->addAddress($resetemail,Config::sitename() . - " User");
		$mail->subject = 'Reset Email for ' . Config::sitename();
		$mail->html = $markup;
		$mail->send();
	}
	// either sent or not, show same message
	CMS::Instance()->queue_message('If your email was associated with a user, you should receive a message with further instructions shortly.','success',Config::uripath() . '/admin');
}

$resetkey = Input::getvar('resetkey','RAW'); 
if ($resetkey) {
	$view = "newpassword";
	// check if passwords sent
	$password1 = Input::getvar('newpassword1','RAW'); 
	$password2 = Input::getvar('newpassword2','RAW'); 
	if ($password1 && $password2) {
		if ($password1 != $password2) {
			CMS::Instance()->queue_message('Passwords did not match.','danger', Config::uripath() . '/admin?resetkey=' . $resetkey);	
		}
		else {
			// check resetkey matches a valid and current resetkey in user table
			$reset_user = new User();
			$reset_user_exists = $reset_user->get_user_by_reset_key ($resetkey);
			if ($reset_user_exists) {
				// remove resetkey from user, update password and redirect to admin login
				if (!$reset_user->remove_reset_key()) {
					CMS::Instance()->queue_message('Error removing reset key.', 'error', Config::uripath()."/admin");
				}
				if ($reset_user->update_password ($password1)) {
					CMS::Instance()->queue_message('Password changed for ' . $reset_user->username,'success', Config::uripath() . '/admin');	
				}
				else {
					CMS::Instance()->queue_message('Unable to reset password. Please contact the system administrator.','danger', Config::uripath() . '/admin?resetkey=' . $resetkey);		
				}
			}
			else {
				// no matching user for resetkey found or resetkey is outdated
				CMS::Instance()->queue_message('Invalid reset key or reset key is too old.','danger', Config::uripath() . '/admin?resetkey=' . $resetkey);	
			}
		}
	}
	// just show newpassword view, no passwords sent for key
}


// end of reset handling

if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
	$protocol = 'https';
}
else {
	$protocol = 'http';
}
$protocalwarning = "";
if ($protocol=="http") {
	$protocalwarning = "<h5 class='warning'>This URL is not secured by SSL, consider enabling this before submitting database/user credentials.</h5>";
}



?>

<html>
<meta name="viewport" content="width=device-width, user-scalable=no" />
	<head><!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/bulma.min.css"></link>
<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/dashboard.css"></link>
<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/layout.css"></link>
<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/darkmode.css"></link>

<script src="https://kit.fontawesome.com/e73dd5d55b.js" crossorigin="anonymous"></script>

		<style>
		#login.container {
			height:100vh;
			display:flex;
			align-items:center;
			justify-content: center;
		}
		h5.warning {
			font-size:120%;
			color:#d88;
		}
		form {
			max-width:30em;
		}
		/* input[type=email], input[type=password] {
			padding:0.25em 0.5em;
			border:1px solid #aaa;
		} */
		</style>
		</head>
		<body>
		<?php 
		//CMS::pprint_r (CMS::Instance());
		?>
		<div class='container'>
			<?php CMS::display_messages();?>
			
		</div>
		<div id="login" class='container '>

			<?php if (!$view):?>
		
				<form class="" submit="" method="POST">

					<h1 class='title is-1'><?php echo Config::sitename() . " Admin Login";?></h1>
					<?php echo $protocalwarning; ?>
					<div class='field'>
						<label class="label" for='email'>Email</label>
						<div class="control has-icons-left">
							<input class='input' autocapitalize="none" type="email" name="email" required>
							<span class="icon is-small is-left">
								<i class="fas fa-envelope"></i>
							</span>
						</div>
						<p class="help">Required</p>
					</div>
					
					<div class='field'>
						<label class="label" for='password'>Password</label>
						<div class="control has-icons-left">
							<input class='input' type="password" name="password" required>
							<span class="icon is-small is-left">
								<i class="fas fa-unlock"></i>
							</span>
						</div>
						<p class="help">Required</p>
					</div>

					<button class="button is-primary" type="submit">Log In</button>

					<p class="help"><a href='?view=resetpassword'>Forgot Password</a></p>

					<?php if (Hook::count_actions_for_hook ('additional_login_options')):?>
					<hr>
					<p class='center'><em>or log in with</em></p>
					<hr>
					<?php Hook::execute_hook_actions('additional_login_options'); ?>
					<?php endif; ?>

				</form>

			<?php elseif ($view=='resetpassword'):?>
				<form class='' submit='' action='' method="POST">
					<h1 class='title is-1'>Reset Password</h1>

					<div class='field'>
						<label class="label" for='email'>Email</label>
						<div class="control has-icons-left">
							<input class='input' autocapitalize="none" type="resetemail" name="resetemail" required>
							<span class="icon is-small is-left">
								<i class="fas fa-envelope"></i>
							</span>
						</div>
						<p class="help">The email that the reset password link will be sent to. Tokens are valid for 24hours</p>
					</div>

					<button class="button is-primary" type="submit">Request Reset</button>

					<p class="help"><a href='<?php echo Config::uripath();?>/admin'>Login</a></p>

				</form>
			<?php elseif ($view=='newpassword'):?>
				<form class='' submit='<?php echo Config::uripath() . '/admin?view=newpassword>resetkey=' . $resetkey?>' action='' method="POST">
					<h1 class='title is-1'>Enter New Password</h1>

					<div class='field'>
						<label class="label" for='email'>New Password</label>
						<div class="control has-icons-left">
							<input class='input' autocapitalize="none" type="password" name="newpassword1" required>
							<span class="icon is-small is-left">
								<i class="fas fa-unlock"></i>
							</span>
						</div>
						
					</div>
					<div class='field'>
						<label class="label" for='email'>New Password</label>
						<div class="control has-icons-left">
							<input class='input' autocapitalize="none" type="password" name="newpassword2" required>
							<span class="icon is-small is-left">
								<i class="fas fa-unlock"></i>
							</span>
						</div>
						<p class="help">Enter the same password twice.</p>
					</div>

					<button class="button is-primary" type="submit">Change Password</button>

					<p class="help"><a href='<?php echo Config::uripath();?>/admin'>Login</a></p>

				</form>
			<?php endif; ?>

		</div>
		</body>
		</html>