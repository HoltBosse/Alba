<?php
defined('CMSPATH') or die; // prevent unauthorized access

?>

<style>
    <?php echo file_get_contents(CMSPATH . "/admin/controllers/pages/views/graphicaledit/style.css"); ?>
</style>
<script>
    <?php echo file_get_contents(CMSPATH . "/admin/controllers/pages/views/graphicaledit/bridgelog.js"); ?>
    <?php echo file_get_contents(CMSPATH . "/admin/controllers/pages/views/graphicaledit/script.js"); ?>
</script>

<div class="fixed-control-bar">
    <button class="button is-primary" type="submit" onclick="alert('disabled');">Save</button>
    <button class="button is-warning" type="button" onclick="window.history.back();">Cancel</button>
</div>

<section class="ge_section_wrapper">
    <main class="container">
        <iframe style="width: 100%; height: 100%;" src="/admin/pages/graphicaleditrender/<?php echo $segments[2]; ?>"></iframe>
    </main>
    <aside>
        <div class="pagecontrols">
            <label>Page<input data-for="page_config_tab" checked type="radio" name="pagecontrolselect"></label>
            <label>Element<input data-for="element_config_tab" type="radio" name="pagecontrolselect"></label>
            <p style="justify-self: end;">X</p>
        </div>
        <hr>
        <div class="page_config_tab">
            <?php
                ob_start();
                    $pageOptionsForm = new Form(CMSPATH . "/admin/controllers/pages/views/graphicaledit/pageoptions.json");
                    $pageOptionsForm->display_front_end();
                $pageOptionsFormContent = ob_get_clean();
                //mod image field start
                $pageOptionsFormContent = str_replace("Selected Image", "", $pageOptionsFormContent);
                $pageOptionsFormContent = str_replace(">Choose New Image<", ">+<", $pageOptionsFormContent);
                $pageOptionsFormContent = str_replace(">Crop Image<", "><i class='fa fa-crop'></i><", $pageOptionsFormContent);
                $pageOptionsFormContent = str_replace(">Upload New Image<", "><i class='fa fa-upload'></i><", $pageOptionsFormContent);
                $pageOptionsFormContent = str_replace(">Clear<", ">X<", $pageOptionsFormContent);
                //mod image field end
                echo $pageOptionsFormContent;
            ?>
        </div>
        <div class="element_config_tab">
            <p>element config</p>
        </div>
    </aside>
</section>