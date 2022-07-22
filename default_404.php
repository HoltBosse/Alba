<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
<div style="display:flex; justify-content:center; align-items:center; height: 100%;">
    <div style="max-width: 50%;">
        <div style="display: flex; gap: 1rem; align-items:center; justify-content:center;">
            <?php 
                $logo_image_id = Configuration::get_configuration_value('general_options','admin_logo');
                $logo_src = $logo_image_id ? Config::$uripath . "/image/" . $logo_image_id : Config::$uripath . "/admin/templates/clean/alba_logo.webp";
            ?>
            <img src="<?php echo $logo_src;?>" >
            <h1 class="title" style="font-size: 6rem; width: 6rem;">404</h1>
        </div>
        <br><br>
        <div>
            <h1 class="title is-3" style="text-align:center;">Oops, something went wrong &#129300</h1>
            <p style="text-align:center;"><a href="/" style="color: black; font-size: 1.5rem; text-decoration: underline;">Visit Home</a></p>
        </div>
    </div>
</div>