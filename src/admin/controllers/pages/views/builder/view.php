<?php

use HoltBosse\Alba\Components\CssFile\CssFile;
use HoltBosse\Form\Form;
use HoltBosse\Alba\Core\{CMS, File, AttributeForm};

(new CssFile())->loadFromConfig((object)[
    "filePath" => __DIR__ . "/style.css"
])->display();

$pageOptions = new Form(__DIR__ . "/page_options.json");

/* $form = AttributeForm::generateFormForClass("HoltBosse\\Alba\\Components\\Button\\Button", true);
CMS::pprint_r($form);
die; */

?>

<div class="top-control-bar">
    <div class="control-bar-logo">

    </div>
    <div class="control-bar-content">
        <div class="edit-bar-toggles">
            <i class="fa-solid fa-angles-left"></i>
            <i class="fa-solid fa-angles-right"></i>
        </div>
        <div class="page-name">
            <p class="page-title-preview">Page Title</p>
        </div>
        <div>
            <button class="button is-primary">Save</button>
        </div>
    </div>
</div>

<div class="page-builder-container">
    <aside class="page-builder-sidebar">
        <br>
        <div class="eb-left-option active" data-pane="component-picker-sidebar">
            <i class="fa-solid fa-hammer"></i>
            <p>Blocks</p>
        </div>
        <div class="eb-left-option" data-pane="outline-configuration-sidebar">
            <i class="fa-solid fa-layer-group"></i>
            <p>Outline</p>
        </div>
    </aside>
    <aside class="component-picker-sidebar edit-bar eb-left active">
        <?php
            foreach($availableComponents as $category => $components) {
                echo "<details open class='component-category'>";
                    echo "<summary>" . ucfirst($category) . "</summary>";
                    echo "<div class='component-options'>";
                        foreach($components as $componentName => $componentClass) {
                            $componentConfig = AttributeForm::generateFormForClass($componentClass, true);
                            echo "<div class='component-option' data-component='$componentName' data-config='" . json_encode($componentConfig) . "' draggable='true'>";
                                echo "<p>$componentName</p>";
                                echo "<i class='fa-solid fa-grip'></i>";
                            echo "</div>";
                        }
                    echo "</div>";
                echo "</details>";
            }
        ?>
    </aside>
    <aside class="outline-configuration-sidebar edit-bar eb-left">
        <p>outline configuration</p>
    </aside>

    <section class="page-builder-main">
        <div class="page-viewport-options">
            <i class="fa-solid fa-mobile" data-size="mobile" title="Mobile"></i>
            <i class="fa-solid fa-tablet" data-size="tablet" title="Tablet"></i>
            <i class="fa-solid fa-desktop" data-size="desktop" title="Desktop"></i>
            <i class="fa-solid fa-maximize" data-size="max" title="Maximize"></i>
        </div>
        <div class="page-viewport-container">
            <div class='iframe-container'>
                <iframe src="/admin/pages/render"></iframe>
            </div>
        </div>
    </section>

    <aside class="component-configuration-sidebar edit-bar eb-right">
        <p>component configuration</p>
    </aside>
    <aside class="page-configuration-sidebar edit-bar eb-right active">
        <?php $pageOptions->display(); ?>
    </aside>
</div>

<script type="module">
    <?php echo file_get_contents(__DIR__ . "/script.js"); ?>
</script>