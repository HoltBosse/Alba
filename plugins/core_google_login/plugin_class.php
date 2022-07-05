<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Plugin_core_google_login extends Plugin {

    public function init() {
        // add to system hooks
        CMS::add_action("additional_login_options", $this, 'display_button'); 
        CMS::add_action("authenticate_user", $this, 'authenticate_token'); 
        CMS::add_action("logout_onclick_js", $this, 'logout_onclick_js');
        CMS::add_action('add_to_head', $this, 'logout_js');
    }

    public function logout_onclick_js() {
        // ACTION - attached to logout_onclick_js hook
        echo "googleSignOut();";
    }

    public function logout_js() {
        // ACTION - attached to add_to_head hook
        echo '<script src="https://apis.google.com/js/platform.js?prompt=select_account&onload=onLoad" async defer></script>';
        echo '<meta name="google-signin-client_id" content="' . $this->get_option('client_id') . '">';
        echo "<script>
        function onLoad() {
            gapi.load('auth2', function() {
              gapi.auth2.init();
            });
          }
        function googleSignOut() {
          var auth2 = gapi.auth2.getAuthInstance();
          auth2.signOut().then(function () {
            console.log('User signed out.');
          });
        }
      </script>";
    }

    public function authenticate_token($user_object) {
        // FILTER - attached to authenticate_user hook
        // authenticate_user hook passes user_object
        if ($user_object->id!==false) {
            // already logged in 
            return $user_object;
        }
        $id_token = Input::getvar('id_token');
        if (!$id_token) {
            // google login not attempted
            return $user_object;
        }

        $a_user = $user_object;

        $url = "https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=" . $id_token;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        $result = curl_exec($curl);
        if (!curl_errno($curl)) {
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($http_code==200) {
                $google_email = json_decode($result)->email;
                $query = 'select * from users where email=? and state>=1';
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
            }
            else {
                CMS::Instance()->queue_message('Google server failed to respond','danger',Config::$uripath . "/admin");
                // google server call failed
            }
        }
        else {
            CMS::Instance()->queue_message('Error sending data to Google','danger',Config::$uripath . "/admin");
            // curl failed
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
        <script src="https://apis.google.com/js/platform.js" async defer></script>
        
        <meta name="google-signin-client_id" content="<?php echo $this->get_option('client_id');?>">
        <div class="g-signin2" data-onsuccess="onSignIn"></div>
        <?php
    }
}




