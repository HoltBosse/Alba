<?php
    Use HoltBosse\Alba\Core\{Configuration, File};
?>

<script>window.uripath = "<?php echo $_ENV["uripath"]; ?>";</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/1.0.0/css/bulma.min.css"></link>
<style>
    <?php
        echo File::getContents(__DIR__ . "/css/dashboard.css");
        echo File::getContents(__DIR__ . "/css/layout.css");
        echo File::getContents(__DIR__ . "/css/searchform.css");
        echo File::getContents(__DIR__ . "/css/darkmode.css");
    ?>
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

<?php
// reCAPTCHA
$rc_sitekey = Configuration::get_configuration_value ('general_options', 'rc_sitekey');
if ($rc_sitekey) {
    echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
}
?>