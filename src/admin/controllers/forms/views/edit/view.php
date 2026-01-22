<?php
    use HoltBosse\Alba\Core\{CMS, Component};
    use HoltBosse\Alba\Components\Admin\ControlBar\ControlBar as AdminControlBar;
    use HoltBosse\Alba\Components\CssFile\CssFile;

    (new CssFile())->loadFromConfig((object)[
		"filePath"=>__DIR__ . "/style.css",
	])->display();
?>

<form action="" method="post">

    <a href='#' class='toggle_siblings'>show/hide required fields</a>
    <div class='toggle_wrap '>
        <div class='flex'>
            <?php $requiredDetailsForm->display(); ?>
        </div>
    </div>

    <br><br>

    <label class="label">Form Builder</label>
    <section class="form-builder">
        <div class="control-panel">
            <p>Select Field</p>
        </div>
        <div class="fields-panel">
            <div class="new-field">
                <div class="new-field-gui-container">
                    <div class="plus-button">
                        <i class="fa-solid fa-square-plus"></i>
                    </div>
                    <select class="new-field-select">
                        <option value="">Select Field Type</option>
                        <?php
                            $jsonConfig = json_decode(file_get_contents(__DIR__ . "/config.json"));
                            foreach($jsonConfig->allowedFields as $fieldType) {
                                echo "<option value='$fieldType'>" . ucwords($fieldType) . "</option>";
                            }

                            if($formId) {
                                $formFields = file_get_contents($_ENV["root_path_to_forms"] . "/forms/form_instance_" . $formId . ".json");
                                echo "<script> window.existingFormData = $formFields;</script>";
                            }
                        ?>
                    </select>
                <div>
            </div>
        </div>
    </section>

    <br><br>


    <?php
        $emailField->display();

        $formSubmitPageField->display();
    ?>
    <input type="hidden" name="form_configuration_submission" value='1'>

    <?php
        (new AdminControlBar())->loadFromConfig((object)[])->display();
    ?>
</form>

<script>
    <?php echo file_get_contents(__DIR__ . "/script.js"); ?>
</script>