<?php
namespace HoltBosse\Alba\Core;

use HoltBosse\Form\Input;

class Access {
    private static $adminAccessRegistry = [
        "" => [1,2],
        "content" => [1, 2],
        "tags" => [1, 2],
        "categories" => [1, 2],
        "images" => [1, 2]
    ];

    public static function registerAdminAccessRule(string $path, array $groups): bool {
        if(!isset(self::$adminAccessRegistry[$path])) {
            self::$adminAccessRegistry[$path] = $groups;

            return true;
        }

        return false;
    }

    public static function registerAdminAccessRuleOverride(string $path, array $groups) {
        self::$adminAccessRegistry[$path] = $groups;
    }

    public static function getAdminAccessRule(string $path): ?array {
        return isset(self::$adminAccessRegistry[$path]) ? self::$adminAccessRegistry[$path] : null;
    }

    public static function can_access($page_groups=[], $user_groups=[]) {
        //prep and parse the inputs
        if (!$user_groups) {
            $user_groups = CMS::Instance()->user->groups;
        }
        if (!$page_groups && CMS::Instance()->isAdmin()) {
            // default to admin in absence of explicit admin config
            // remove sizeof check for php 8.0+
            $page_groups=[1];
        }

        //logic
        if(!$page_groups) {
            return true;
        }

        foreach ($user_groups as $group) {
            if (in_array($group->id, $page_groups)) {
                return true;
            }
        }

        return false;
    }

    public static function onLoginSuccess($redirectPath): Message {
        //check if they need to change their password on first login
        if(CMS::Instance()->user->state==2) {
            return new Message(
                false,
                MessageType::Warning,
                'Welcome ' . Input::stringHtmlSafe(CMS::Instance()->user->username) . '. Please update your password.',
                $_ENV["uripath"] . '/admin?updatepassword=true&token=' . CMS::Instance()->user->generate_reset_key()
            );
        }

        $_SESSION['user_id'] = CMS::Instance()->user->id;
        if (isset($_SESSION['redirect_url'])) {
            $redirectPath = $_SESSION['redirect_url'];
            unset($_SESSION['redirect_url']);
        }
        Actions::add_action("userlogin", (object) [
            "user"=>CMS::Instance()->user->id,
        ], CMS::Instance()->user->id);
        Hook::execute_hook_actions('user_logged_in');


        return new Message(
            true,
            MessageType::Success,
            'Welcome ' . Input::stringHtmlSafe(CMS::Instance()->user->username),
            $redirectPath
        );
    }

    public static function handleLogin($email, $password, ?int $domain=null): Message {
        // check for login attempt
        $loginUser = new User();
        $redirectPath = $_ENV["uripath"] . "/";
        if (CMS::Instance()->isAdmin()) {
            $redirectPath = $_ENV["uripath"] . '/admin';
        }

        // authenticate plugins hook

        CMS::Instance()->user = Hook::execute_hook_filters('authenticate_user', CMS::Instance()->user); 
        
        if (CMS::Instance()->user->id!==false) {
            return Access::onLoginSuccess($redirectPath);
        }

        // continue with core login attempt

        if ($password && (!$email)) {
            // badly formatted email submitted and discarded by php filter
            return new Message(false, MessageType::Danger, 'Invalid email', $redirectPath);
        }
        if ($email && $password) {
            if ($loginUser->load_from_email($email, $domain)) {
                if ($loginUser->state<1) {
                    return new Message(false, MessageType::Danger, 'Incorrect email or password', $redirectPath);
                }

                //CMS::pprint_r($loginUser); die;

                $canAccessOnDomain = false;
                foreach($loginUser->groups as $group) {
                    $domains = explode(",", $group->domain);
                    foreach($domains as $domain) {
                        if($domain == CMS::getDomainIndex($_SERVER['HTTP_HOST'])) {
                            $canAccessOnDomain = true;
                            break 2;
                        }
                    }
                }
                if(!$canAccessOnDomain) {
                    return new Message(false, MessageType::Danger, 'Incorrect email or password', $redirectPath);
                }

                // user exists, check password
                if ($loginUser->check_password($password)) {
                    // logged in!
                    CMS::Instance()->user = $loginUser;
                    
                    return Access::onLoginSuccess($redirectPath);
                } else {
                    return new Message(false, MessageType::Danger, 'Incorrect email or password', $redirectPath);
                }
            } else {
                return new Message(false, MessageType::Danger, 'Incorrect email or password', $redirectPath);
            }
        }

        return new Message(false, MessageType::Warning);
    }
}