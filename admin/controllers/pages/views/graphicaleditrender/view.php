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
        <?php
        for ($i = 1; $i <= 20; $i++) {
            CMS::pprint_r("some rando content");
            echo "<br><br><br><br><br><br>";
        }
        CMS::jprint_r("test");
    echo "<section>";
$pageContent = ob_get_clean();

$allContent = str_replace("<!--PAGECONTENT-->", $pageContent, $templateMarkup);
echo $allContent;


die;