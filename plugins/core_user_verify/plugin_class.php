<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Plugin_core_user_verify extends Plugin {

    public function init() {
        // add to system hooks
        CMS::add_action("verify_user", $this, 'user_verification');
        CMS::add_action("on_user_save", $this, 'new_user_mail');
    }

    public function make_message($message="", $extra_html="") {
        ?>
            <section style="display: flex; justify-content:center; align-items: center;">
                <div>
                    <p><?php echo $message; ?></p>
                    <?php echo $extra_html; ?>
                </div>
            </section>
        <?php
    }

    public function send_email($email, $username, $key, $redirect) {
        
        $mail = new Mail();
        $mail->addAddress($email,$username);
        $mail->subject = "Verify Your " . Config::sitename() . " Account";
        $mail->html = "<p>Hello, You have been registered for an account on " . Config::sitename() . "</p>";

        // get logos that might be used
        $custom_logo_id = $this->get_option("logo_image");
        $admin_logo_id = Configuration::get_configuration_value('general_options','admin_logo');

        // identify which logo to use
        $site_domain = "https://" . $_SERVER['SERVER_NAME'];
        if ($custom_logo_id) {
            $logo_src = $site_domain . "/image/" . $custom_logo_id;
        }
        else if ($admin_logo_id) {
            $logo_src = $site_domain . "/image/" . $admin_logo_id;
        }
        else {
            $logo_src = $site_domain . "/admin/templates/clean/alba_logo.webp";
        }
        
        // set link and cta colors
        $config_link_color = $this->get_option("email_link_color") ?? "#3e8ed0";
        $link_color = $config_link_color == "" ? "#3e8ed0" : $config_link_color;
        $config_cta_color = $this->get_option("email_cta_color") ?? "#3e8ed0";
        $cta_color = $config_cta_color == "" ? "#3e8ed0" : $config_cta_color;

        $mail->html = "
            <div style='font-family: BlinkMacSystemFont, -apple-system, \"Segoe UI\", Roboto, Oxygen, Ubuntu, Cantarell, \"Fira Sans\", \"Droid Sans\", \"Helvetica Neue\", Helvetica, Arial, sans-serif; font-size: 16px; padding: 0; margin: 0;'>
                <table style=\"padding: 0; margin: 0; border-spacing: 0px; background-color: lightgrey;\">
                    <tbody>
                        <tr style=\"height: 25px;\"></tr>
                        <tr>
                            <td style=\"width: 50px;\"></th>
                            <td style=\"width: 500px; padding: 25px; background-color: white; border-radius: 0px;\">
                                <br>
                                <div style=\"text-align: center;\">
                                    <a href=\"" . $site_domain . "\">
                                        <img src=\"" . $logo_src . "\" alt=\"" . Config::sitename() . " Logo\" style=\"width: 300px;\">
                                    </a>
                                </div>
                                <br>
                                <div style=\"text-align: left; font-weight: normal;\">
                                    <h1 style='text-align: center;  font-size: 24px;'>
                                        Verify Your Account
                                    </h1>
                                    <p>Your email was just used to register for an account with <a href=\"https://" . $_SERVER["SERVER_NAME"] . "\" style=\"color: " . $link_color . "\">" . Config::sitename() . '</a>. If this was you:</p>
                                    <br>
                                    <div style="text-align: center;">
                                        <a style="text-align: center;  text-decoration: none;  background-color: ' . $cta_color . '; color: white; padding: 10px 15px; border-radius: 5px; font-size: 16px;" href="https://' . ($redirect ?? $_SERVER['SCRIPT_URL']) . "?key=" . $key . "\">Verify Your Account</a>
                                    </div>
                                    <br>
                                    <p>If you did not register for an " . Config::sitename() . " account <span style=\"font-weight: bold;\">do not</span> click the button above. Your email will not be associated with any account. No further action is needed.</p>
                                    <br>
                                    <br>
                                </div>
                            </th>
                            <td style=\"width: 50px; \"></th>
                        </tr>
                        <tr style=\"height: 25px;\"></tr>
                    </tbody>
                </table>
            </div>    
        ";
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
                    $extra_html = "<p>you will be redirected in 5 seconds</p><noscript><p>it looks like you have javascript disabled. as this breaks the redirect, please go to https://" . $_SERVER['SERVER_NAME'] . $this->get_option('verified_redirect') . "</p></noscript>";
                } else {
                    $message = "We're sorry, there has been an error activating your account";
                }

                $this->make_message($message, $extra_html ?? "");
            } else {
                $this->make_message("We're sorry, there has been an error");
            }
        }
    }

    public function new_user_mail(...$args) {
        if($this->get_option('admin_emails')=="yes" && (strpos($args[0][0]->registered, date("Y-m-d H:i")) !== false)) {
            $user = $args[0][0];
            DB::exec("UPDATE users SET state=0 WHERE id=?", $user->id); //disable the user
            $this->send_email($user->email, $user->username, $user->generate_reset_key(), $this->get_option('verify_url'));
            CMS::Instance()->queue_message('User Email Sent','success');
        }
    }
}




