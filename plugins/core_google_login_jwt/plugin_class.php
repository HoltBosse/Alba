<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Plugin_core_google_login_jwt extends Plugin {

    public function init() {
        // add to system hooks
        CMS::add_action("additional_login_options", $this, 'display_button'); 
        CMS::add_action("authenticate_user", $this, 'authenticate_token'); 
        CMS::add_action("logout_onclick_js", $this, 'logout_onclick_js');
        CMS::add_action('add_to_head', $this, 'logout_js');
    }

    public function logout_onclick_js() {
        // ACTION - attached to logout_onclick_js hook
        // not needed for new google login method
        // leaving just in case we need to do anything else
    }

    public function logout_js() {
        // ACTION - attached to add_to_head hook
        // Not needed for new google login method
        // leaving just in case we need to do anything else
    }


    public static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    public function authenticate_token($user_object) {
        // FILTER - attached to authenticate_user hook
        // authenticate_user hook passes user_object
        
        if ($user_object->id!==false) {
            // already logged in 
            return $user_object;
        }

        $jwt_credentials = Input::getvar('credential');
        
        if (!$jwt_credentials) {
            // google login not attempted
            return $user_object;
        }

        // double-cookie CSRF mitigation check
        $csrf_token_body = Input::getvar('g_csrf_token');
        $csrf_token_cookie = $_COOKIE['g_csrf_token'];
        if (!$csrf_token_body || !$csrf_token_cookie || ($csrf_token_body!=$csrf_token_cookie)) {
            CMS::Instance()->queue_message('CSRF verification check failed','danger', Config::$uripath . "/admin");
        }

        // do jwt stuff
        $tks = explode(".",$jwt_credentials);
        $jwt_obj = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',$tks[1]))));
        $sig = $this->urlsafeB64Decode($tks[2]);
        $data = $tks[0] . "." . $tks[1]; // original JWT payload

        if (!$jwt_obj) {
            CMS::Instance()->queue_message('Invalid/no JWT returned from Google','danger', Config::$uripath . "/admin");
        }

        // assume aud/iss/exp exist in google jwt
        // todo: more robust checking necessary for general use

        if ($jwt_obj->aud !== $this->get_option('client_id')) {
            CMS::Instance()->queue_message('Google client id does not match JWT from server','danger', Config::$uripath . "/admin");
        }

        if ($jwt_obj->iss !== "https://accounts.google.com" && $jwt_obj->iss !== "accounts.google.com") {
            CMS::Instance()->queue_message('JWT token is not from Google accounts API','danger', Config::$uripath . "/admin");
        }

        if (time() >= $jwt_obj->exp) {
            CMS::Instance()->queue_message('Google JWT expired','danger', Config::$uripath . "/admin");
        }

        // check token signature against public keys
        // TODO: cache and only retrieve once expired
        $google_pems = file_get_contents("https://www.googleapis.com/oauth2/v1/certs");
        $google_pems_obj = json_decode($google_pems);
        if (!$google_pems) {
            CMS::Instance()->queue_message('Failed to retrieve valid PEMs from Google','danger', Config::$uripath . "/admin");
        }
        $valid = false;
        $pem_arr = get_mangled_object_vars($google_pems_obj); // convert obj to arr
        
        // loop over public keys for match
        foreach ($pem_arr as $pem) {
            $prepped_pem = openssl_pkey_get_public($pem); // prepare
            $valid = openssl_verify( $data, $sig, $prepped_pem, "RSA-SHA256"); 
            if ($valid===1) {
                break;
            }
        }
        if ($valid!==1) {
            CMS::Instance()->queue_message('Invalid JWT','danger', Config::$uripath . "/admin");
        }

        // reached here - JWT is authentic and current

        $a_user = $user_object;

        // check if google email matches user in syste,

        $google_email = $jwt_obj->email;
        $query = 'select * from users where email=?';
        $stmt = CMS::Instance()->pdo->prepare($query);
        $stmt->execute(array($google_email));
        $result = $stmt->fetch();
        if ($result) {
            // google user email matched user in system - return user details class
            $a_user->load_from_id($result->id);
            return $a_user;
        }
        else {
            CMS::Instance()->queue_message('User not registered','danger',Config::$uripath . "/admin");
            // user not in system - register?
        }
            
        return $a_user;
    }

    public function display_button(...$args) {
        // ACTION - attached to additional_login_options hook
        ?>
        <script>
     

        function onSignIn(googleUser) {
            var profile = googleUser.getBasicProfile();
            console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
            console.log('Name: ' + profile.getName());
            console.log('Image URL: ' + profile.getImageUrl());
            console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
            var id_token = googleUser.getAuthResponse().id_token; // for sending to server
            var auth2 = gapi.auth2.getAuthInstance();
            auth2.signOut(); // signout of google every time to prevent loops with unknown user in database
            window.location.href += "?id_token=" + id_token;
        }
        </script>
        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <?php 
        //$client_id =  $this->get_option('client_id');
        $login_page = $this->get_option('login_page') ?? Config::$uripath . "/admin";
        ?>
        <div id="g_id_onload"
            data-client_id="<?php echo $this->get_option('client_id');?>"
            data-ux_mode="redirect"
            data-login_uri="<?php echo $login_page;?>"
            data-context="signin"
            data-auto_select="true">
        </div>
        <div class="g_id_signin" 
        data-type="standard" 
        data-shape="rectangular"
        data-theme="outline"
        data-text="signin"
        data-size="large"
        data-logo_alignment="left"></div>
        <?php
    }
}




