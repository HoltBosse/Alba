<?php
defined('CMSPATH') or die; // prevent unauthorized access

$coreFields = glob(CMSPATH . "/core/fields/Field_*.php");
$userFields = glob(CMSPATH . "/user_classes/Field_*.php");
$fieldsList = array_merge($coreFields, $userFields);
//CMS::pprint_r($fieldsList);
?>

<style>
    <?php echo file_get_contents(CMSPATH . "/admin/controllers/forms/views/edit/style.css"); ?>
</style>

<h1 class="title">New Form</h1>

<section class="form_editor_wrapper">
    <div class="controls_panel">
        <div class="controls_button_fields_grid">
            <label for="add_field" class="button is-primary">Add Field</label>
            <input id="add_field" type="radio" style="display: none;" name="controls_pane_option" checked>
            <label for="field_settings" class="button is-primary" disabled>Field Settings</label>
            <input id="field_settings" type="radio" style="display: none;" name="controls_pane_option">
        </div>
        <hr>
        <div class="add_field_list controls_button_fields_grid">
            <?php
                foreach($fieldsList as $fieldPath) {
                    $className = basename($fieldPath, ".php");
                    $type = str_replace("Field_", "", $className);
                    /*
                    $classInstance = new $className();
                    //if($classInstance::show_in_editor()) {
                    $markup = $classInstance::get_editor_markup();
                    */
                    $markup = "<p>$type</p>";
                    echo "<a
                            class='button is-info'
                            data-display_markup='$markup'
                            data-type='$type'
                        >
                        $type
                        </a>";
                }
            ?>
        </div>
        <div class="field_configuration_list">
            <p>fields config select</p>
        </div>
    </div>
    <div class="fields_panel">
        
    </div>
</section>
<br><br>

<div class="fixed-control-bar">
	<button title="Save and exit" class="button is-primary" type="submit">Save</button>
	<button class="button is-warning" type="button" onclick="window.history.back();">Cancel</button>
</div>

<script>
    <?php echo file_get_contents(CMSPATH . "/admin/controllers/forms/views/edit/script.js"); ?>
</script>