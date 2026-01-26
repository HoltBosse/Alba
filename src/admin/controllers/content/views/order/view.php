<?php

Use HoltBosse\Alba\Core\{CMS, Content, Component};
Use HoltBosse\Alba\Components\Html\Html;
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
Use HoltBosse\Form\{Input, Form};

?>

<style>
    .orderitem:hover {
        cursor:move;
    }
    .sortable-chosen {
        font-weight:bold;
    }
    table#orderitems tr {
        grid-template-columns: repeat(<?php echo sizeof($content_list_fields)+4;?>, 1fr); /* match # of columns in ordering table */
    }
    table.table td:nth-of-type(2), table.table th:nth-of-type(2) {
        grid-column: span 3;
    }
    [data-hidestate="true"] {
        display: none !important;
    }
    .state1 {
        color:#00d1b2;
    }
    .state0 {
        color:#f66;
    }
</style>

<?php
    $header = "Order &ldquo;" . Content::get_content_type_title($content_type) . "&rdquo; Content";
    $rightContent = "<a class='is-primary button btn' href='" . $_ENV["uripath"] . "/admin/content/all/$content_type'>Back To All &ldquo;" . Content::get_content_type_title($content_type) . "&rdquo; Content</a>";

    (new TitleHeader())->loadFromConfig((object)[
        "header"=>html_entity_decode($header),
        "byline"=>"Drag and drop items into your preferred order.",
        "rightContent"=>(new Html())->loadFromConfig((object)[
            "html"=>"<div>" . $rightContent . "</div>",
            "wrap"=>false
        ])
    ])->display();
?>

<table class='table is-fullwidth' id="orderitems">
    <thead>
        <tr>
            <th>State</th>
            <th>Title</th>
            <?php if ($content_list_fields):?>
                <?php foreach ($content_list_fields as $content_list_field):?>
                    <th><?php echo $content_list_field->label; ?></th>
                <?php endforeach; ?>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody id="ordertablebody">
        <?php foreach ($all_content as $i):?>
            <tr data-ordering="<?=$i->ordering;?>" data-content_type="<?=$content_type;?>" data-content_id="<?=$i->id;?>" data-hidestate="<?=$i->state<0  ? "true" : "false";?>" class=" orderitem">
                <td>
                <?php if ($i->state==1) { 
                    echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
                }
                elseif ($i->state==0) {
                    echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
                }
                else {
                    foreach($custom_fields->states as $state) {
                        if($content_item->state==$state->state) {
                            echo "<i style='color:$state->color' class='fas fa-times-circle' aria-hidden='true'></i>";
                            $ok = true;
                        }
                    }
                    if(!$ok) {
                        echo "<i class='fas fa-times-circle' aria-hidden='true'></i>"; //default grey color if state not found
                    }
                }
                ?>
                </td>
                <td><?php echo Input::stringHtmlSafe($i->title);?></td>
                <?php if ($content_list_fields):?>
                    <?php foreach ($content_list_fields as $content_list_field):?>
                        <td><?php 
                            // limit number of rows to show friendly values for performance/timeout reasons
                            // TODO: make per-content type json config value - allow for flexibility?
                            if (sizeof($all_content) < 500) {
                                $propname = "{$content_list_field->name}"; 
                                $classname = Form::getFieldClass($content_list_field->type);
                                $curfield = new $classname();
                                $curfield->loadFromConfig($named_custom_fields[$propname]); // load config - useful for some fields
                                $curfield->default = $i->$propname; // set temp field value to current stored value
                                // TODO: pass precalc array of table names for content types to aid in performance of lookups 
                                // some fields will currently parse json config files to determine tables to query for friendly values
                                // PER row/field. not ideal. ESPECIALLY IMPORTANT FOR ORDER VIEW SINCE ALL CONTENT ITEMS SHOWN
                                echo $curfield->getFriendlyValue($named_custom_fields[$propname]); // pass named field custom field config to help determine friendly value
                            }
                            else {
                                echo $i->{$content_list_field->name}; // field contents from DB
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
    let content_type=<?=$content_type;?>;
    <?php echo file_get_contents(__DIR__ . "/script.js"); ?>
</script>

