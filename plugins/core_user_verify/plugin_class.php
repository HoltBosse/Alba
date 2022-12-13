<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Plugin_core_user_verify extends Plugin {

    public function init() {
        // add to system hooks
        CMS::add_action("verify_user", $this, 'user_verification');
        CMS::add_action("on_user_save", $this, 'new_user_mail');
    }

    public function make_message($message="") {
        ?>
            <section style="display: flex; justify-content:center; align-items: center;">
                <div>
                    <p><?php echo $message; ?></p>
                </div>
            </section>
        <?php
    }

    public function send_email($email, $username, $key, $redirect) {
        $mail = new Mail();
        $mail->addAddress($email,$username);
        $mail->subject = "User Verification Details for " . Config::sitename();
        $mail->html = "<p>Hello, You have been registered for an account on " . Config::sitename() . "</p>";
        $mail->html .= "<p>please click here to verify your account: <a href='https://" . ($redirect ?? $_SERVER['SCRIPT_URL']) . "?key=" . $key . "'>Verify</a></p>";
        $mail->html .= "<p>If you believe this message to be in error or have not signed up for/requested an account, no further action is required</p>";
        $mail->send();
    }

    public function user_verification() {
        if(!$_GET) {
            //we want details via post
            $username = Input::getvar("username", "TEXT");
            $email = Input::getvar("email", "TEXT");
            $password1 = Input::getvar("password1", "TEXT");
            //perhaps consider optional server side two password field verification?

            if($username && $email && $password1 && !DB::fetch("SELECT * FROM users WHERE email=?", $email)) {
                //make user here
                $uid = User::create_new($username, $password1, $email, array_map('intval', explode(",", $this->get_option('user_groups') ?? "")), 0); //we want the user to be disabled by default

                $user = new User();
                $user->load_from_id($uid);

                //send email
                $this->send_email($email, $username, $user->generate_reset_key(), $this->get_option('verify_url'));

                $message = "Please open the link sent to your email address to verify your account";
            } else {
                $message = "We're sorry, there was an error validating your credentials";
            }

            $this->make_message($message);
        } else {
            if(Input::getvar("key", "TEXT")) {
                //get verification key here and validate account
                $user = new User();
                $user->get_user_by_reset_key(Input::getvar("key", "TEXT"));
                if($user->id) {
                    DB::exec("UPDATE users SET state=1 WHERE id=?", $user->id); //enable the user
                    $user->remove_reset_key();
                    $_SESSION['user_id'] = $user->id;
                    echo "<script>setTimeout(function () { window.location.href= 'https://" . $_SERVER['SERVER_NAME'] . $this->get_option('verified_redirect') . "'; },5000);</script>";
                    $message = "Welcome, your account has been enabled";
                } else {
                    $message = "We're sorry, there has been an error activating your account";
                }

                $this->make_message($message);
            } else {
                $this->make_message("We're sorry, there has been an error");
            }
        }
    }

    public function new_user_mail(...$args) {
        if($this->get_option('admin_emails')=="yes") {
            $user = $args[0][0];
            DB::exec("UPDATE users SET state=0 WHERE id=?", $user->id); //disable the user
            $this->send_email($user->email, $user->username, $user->generate_reset_key(), $this->get_option('verify_url'));
            CMS::Instance()->queue_message('User Email Sent','success');
        }
    }
}




