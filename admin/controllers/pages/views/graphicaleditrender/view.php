<?php
defined('CMSPATH') or die; // prevent unauthorized access
ob_get_clean();
ob_get_clean();

ob_start();
    require_once(CMSPATH . "/templates/pico_graphicaledit/index.php");
$templateMarkup = ob_get_clean();

ob_start();
    echo "<section id='graphicaleditrendersection'>";
        ?>
            <script>
                <?php echo file_get_contents(CMSPATH . "/admin/controllers/pages/views/graphicaleditrender/consolepatch.js"); ?>
                <?php echo file_get_contents(CMSPATH . "/admin/controllers/pages/views/graphicaleditrender/commands.js"); ?>
                <?php echo file_get_contents(CMSPATH . "/admin/controllers/pages/views/graphicaleditrender/script.js"); ?>
            </script>
            <section style="border: 1px dashed; padding: 1rem;">
                <div>
                    <p style="background-color: red; text-align: center; margin-bottom: 0;">EXISTING CONTENT HERE</p>
                </div>
                <br>
                <div style="display: flex; justify-content: center;">
                    <span style="border: 1px solid; border-radius: 20rem; height: 2rem; width: 2rem; text-align: center;">+</span>
                </div>
            </section>
            <br>
            <div style="display: flex; justify-content: center;">
                <span style="border: 1px solid; border-radius: 2rem; white-space: pre;">  + new widget +  </span>
            </div>
        <?php
        CMS::jprint_r("test");
    echo "<section>";
$pageContent = ob_get_clean();

$allContent = str_replace("<!--PAGECONTENT-->", $pageContent, $templateMarkup);
echo $allContent;


die;