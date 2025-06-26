<?php 
defined('CMSPATH') or die;
?>

<script>window.uripath = "<?php echo Config::uripath();?>";</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/1.0.0/css/bulma.min.css"></link>
<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/darkmode.css?v=4"></link>
<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/dashboard.css?v=4"></link>
<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/layout.css?v=4"></link>

<script src="https://kit.fontawesome.com/e73dd5d55b.js" crossorigin="anonymous"></script>

<?php
// reCAPTCHA
$rc_sitekey = Configuration::get_configuration_value ('general_options', 'rc_sitekey');
if ($rc_sitekey):?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>