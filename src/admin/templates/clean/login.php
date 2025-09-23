<?php

Use HoltBosse\Alba\Core\{CMS, Hook, User, Mail};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;

$view = Input::getvar('view','STRING');

// handle reset request

$resetemail = Input::getvar('resetemail','EMAIL');
if ($resetemail) {
	$resetUser = new User();
	$resetUser->load_from_email($resetemail);
	$message = $resetUser->sendResetEmail();

	if($message->hasMessage()) {
		CMS::Instance()->queue_message(...$message->toQueueMessageArgsArray());
	}

	die;
}

$resetkey = Input::getvar('resetkey','RAW'); 
if ($resetkey) {
	$view = "newpassword";
	// check if passwords sent
	$password1 = Input::getvar('newpassword1','RAW'); 
	$password2 = Input::getvar('newpassword2','RAW'); 

	if ($password1 && $password2) {
		$resetUser = new User();
		$message = $resetUser->resetPassword($password1, $password2, $resetkey);

		if($message->hasMessage()) {
			CMS::Instance()->queue_message(...$message->toQueueMessageArgsArray());
		}
	}

	// just show newpassword view, no passwords sent for key
}

// end of reset handling

$updatePassword = Input::getvar('updatepassword','RAW');
if ($updatePassword) {
	$view = "newpassword";

	$token = Input::getvar('token','RAW');

	$password1 = Input::getvar('newpassword1','RAW'); 
	$password2 = Input::getvar('newpassword2','RAW');

	if ($password1 && $password2) {
		$resetUser = new User();
		$resetUser->get_user_by_reset_key($token);
		$message = $resetUser->resetPassword($password1, $password2, $token);

		if($message->success===true) {
			DB::exec("UPDATE `users` SET `state`=1 WHERE `id`=?",[$resetUser->id]);
		}

		if($message->hasMessage()) {
			if($message->success===false && str_contains($message->redirectTo, "resetkey")) {
				$message->redirectTo = $_ENV["uripath"] . "/admin?updatepassword=true&token=" . $token;
			}

			CMS::Instance()->queue_message(...$message->toQueueMessageArgsArray());
		}
	}
}

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
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/1.0.0/css/bulma.min.css"></link>
		<style>
			<?php
				echo file_get_contents(__DIR__ . "/css/dashboard.css");
				echo file_get_contents(__DIR__ . "/css/layout.css");
				echo file_get_contents(__DIR__ . "/css/darkmode.css");
			?>
		</style>

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

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
		</style>
	</head>
	<body>
		<?php 
		//CMS::pprint_r (CMS::Instance());
		?>
		<div class='container'>
			<?php CMS::Instance()->display_messages();?>
			
		</div>
		<div id="login" class='container '>

			<?php if (!$view):?>
		
				<form class="" submit="" method="POST">

					<h1 class='title is-1'><?php echo $_ENV["sitename"] . " Admin Login";?></h1>
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

					<?php if (Hook::count_actions_for_hook('additional_login_options')):?>
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

					<p class="help"><a href='<?php echo $_ENV["uripath"];?>/admin'>Login</a></p>

				</form>
			<?php elseif ($view=='newpassword'):?>
				<?php
					if($updatePassword) {
						$url = $_ENV["uripath"] . "/admin?updatepassword=true&token=" . Input::getvar('token','RAW');
					} elseif($resetkey) {
						$url = $_ENV["uripath"] . '/admin?view=newpassword>resetkey=' . $resetkey;
					}	
				?>
				<form class='' submit='<?php echo $url; ?>' action='' method="POST">
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

					<p class="help"><a href='<?php echo $_ENV["uripath"];?>/admin'>Login</a></p>

				</form>
			<?php endif; ?>

		</div>
	</body>
</html>