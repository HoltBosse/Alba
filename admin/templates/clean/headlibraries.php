<?php 
defined('CMSPATH') or die;
?>

<script>window.uripath = "<?php echo Config::uripath();?>";</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/1.0.0/css/bulma.min.css"></link>
<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/darkmode.css?v=3"></link>
<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/dashboard.css?v=3"></link>
<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/layout.css?v=3"></link>

<script src="https://kit.fontawesome.com/e73dd5d55b.js" crossorigin="anonymous"></script>

<!-- multiselect - Slim Select © 2020 Brian Voelker - Used under MIT license. -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.26.0/slimselect.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.26.0/slimselect.min.css" rel="stylesheet"></link>
<!-- end multiselect -->

<?php
// reCAPTCHA
$rc_sitekey = Configuration::get_configuration_value ('general_options', 'rc_sitekey');
if ($rc_sitekey):?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>