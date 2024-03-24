<?php 
defined('CMSPATH') or die;
?>

<script>window.uripath = "<?php echo Config::uripath();?>";</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/1.0.0/css/bulma.min.css"></link>
<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/darkmode.css"></link>
<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/dashboard.css"></link>
<link rel="stylesheet" href="<?php echo Config::uripath();?>/admin/templates/clean/css/layout.css"></link>

<script src="https://kit.fontawesome.com/e73dd5d55b.js" crossorigin="anonymous"></script>

<!-- multiselect - Slim Select © 2020 Brian Voelker - Used under MIT license. -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.26.0/slimselect.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.26.0/slimselect.min.css" rel="stylesheet"></link>
<!-- end multiselect -->

<!-- cropperjs - fengyuanchen - MIT license -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
<!-- end cropperjs -->

<script>
    /* Utility functions for global admin use */

    function postAjax(url, data, success) {
        var params = typeof data == 'string' ? data : Object.keys(data).map(
                function(k){ return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
            ).join('&');

        var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
        xhr.open('POST', url);
        xhr.onreadystatechange = function() {
            if (xhr.readyState>3 && xhr.status==200) { success(xhr.responseText); }
        };
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send(params);
        return xhr;
    }

    // no js insertAfter - only insertBefore. silly.
    Object.prototype.insertAfter = function (newNode) { this.parentNode.insertBefore(newNode, this.nextSibling); }
</script>

<?php
// reCAPTCHA
$rc_sitekey = Configuration::get_configuration_value ('general_options', 'rc_sitekey');
if ($rc_sitekey):?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>