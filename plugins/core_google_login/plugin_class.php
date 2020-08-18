<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Plugin_core_google_login extends Plugin {
    public function init() {
        CMS::add_action("additional_login_options", $this, 'display_button'); 
        CMS::add_action("authenticate_user", $this, 'authenticate_token'); 
    }

    public function display_button(...$args) {
        ?>
        <script src="https://apis.google.com/js/platform.js" async defer></script>
        <script>
        function onSignIn(googleUser) {
            var profile = googleUser.getBasicProfile();
            console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
            console.log('Name: ' + profile.getName());
            console.log('Image URL: ' + profile.getImageUrl());
            console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
        }
        </script>
        <meta name="google-signin-client_id" content="<?php echo $this->get_option('client_id');?>">
        <div class="g-signin2" data-onsuccess="onSignIn"></div>
        <?php
    }
}




