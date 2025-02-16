<?php
defined('CMSPATH') or die; // prevent unauthorized access

// TODO: handle custom states - see all view
// TODO: handle base grid column count - see all view

?>

<style>
    <?php echo file_get_contents(CMSPATH . "/admin/controllers/content/views/all/style.css"); ?>
    #orderitems {}
    .orderitem {
        /* display:flex;
        gap:1rem; */
        &:hover {
            cursor:move;
        }
    }
    .sortable-ghost {
    }
    .sortable-chosen {
        font-weight:bold;
    }
    .sortable-drag {
    }
    table#orderitems tr {
        grid-template-columns: repeat(<?php echo sizeof($content_list_fields)+4;?>, 1fr); /* match # of columns in ordering table */
    }
    table.table td:nth-of-type(<?php echo Admin_Config::$show_ids_in_tables ? 3 : 2; ?>), table.table th:nth-of-type(<?php echo Admin_Config::$show_ids_in_tables ? 3 : 2; ?>) {
        grid-column: span 3;
    }
</style>

<h1 class='title is-1'>Order <?php echo "&ldquo;" . Content::get_content_type_title($content_type) . "&rdquo; ";?>Content
	<a class='is-primary pull-right button btn' href='<?php echo Config::uripath();?>/admin/content/all/<?php echo $content_type;?>'>Back To All &ldquo;<?php echo Content::get_content_type_title($content_type);?>&rdquo; Content</a>
    <span class='unimportant subheading'>Drag and drop items into your preferred order. </span>
</h1>

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
            <tr data-ordering="<?=$i->ordering;?>" data-content_type="<?=$content_type;?>" data-content_id="<?=$i->id;?>" class=" orderitem">
                <td>
                <?php if ($i->state==1) { 
                    echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
                }
                elseif ($i->state==0) {
                    echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
                }
                else {
                    // TODO: custom states
                    echo $i->state;
                }
                ?>
                </td>
                <td><?=$i->title;?></td>
                <?php if ($content_list_fields):?>
                    <?php foreach ($content_list_fields as $content_list_field):?>
                        <td><?php 
                            // limit number of rows to show friendly values for performance/timeout reasons
                            // TODO: make per-content type json config value - allow for flexibility?
                            if (sizeof($all_content) < 500) {
                                $propname = "{$content_list_field->name}"; 
                                $classname = "Field_" . $content_list_field->type;
                                $curfield = new $classname();
                                $curfield->load_from_config($named_custom_fields[$propname]); // load config - useful for some fields
                                $curfield->default = $i->$propname; // set temp field value to current stored value
                                // TODO: pass precalc array of table names for content types to aid in performance of lookups 
                                // some fields will currently parse json config files to determine tables to query for friendly values
                                // PER row/field. not ideal. ESPECIALLY IMPORTANT FOR ORDER VIEW SINCE ALL CONTENT ITEMS SHOWN
                                echo $curfield->get_friendly_value($named_custom_fields[$propname]); // pass named field custom field config to help determine friendly value
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

<script src='<?php echo Config::uripath();?>/admin/controllers/content/views/order/Sortable.js'></script>
<script>
    new Sortable(document.getElementById('ordertablebody'), {
        animation: 150,
        onUpdate: function (e) {
            /* console.log("From ", e.oldIndex, " to ", e.newIndex);
            console.log(e.item.dataset); */
            // update all ordering indices to ensure correctness
            // get arr of ids
            let id_arr=[];
            let content_type=<?=$content_type;?>;
            document.querySelectorAll('.orderitem').forEach((e)=>{
                id_arr.push(e.dataset.content_id);
            });
            let id_arr_string = id_arr.join(",");
            if (id_arr_string) {
                // pass to server
                console.log(id_arr_string);
                
                async function update_ordering() {
                    const formData = new FormData();

                    // orderall
                    // can be used to pass ALL ids in content for ordering
					//formData.append("ids", id_arr_string);
					//formData.append("action", "setorderall");
					//formData.append("content_type", <?=$content_type;?>);

                    // changeorder
                    // id, new_order, prev_order, content_type
                    formData.append("action", "changeorder");
                    formData.append("id", e.item.dataset.content_id);
                    formData.append("content_type", <?=$content_type;?>);
                    formData.append("new_order", e.newIndex+1); // client-size index 0 based, ordering 1
                    formData.append("prev_order", e.oldIndex+1);
                    fetch("<?=Config::uripath();?>/admin/content/api", {
                            method: "POST",
                            body: formData,
                    }).then((response) => response.json()).then((data) => {
                        console.log(data);
                    });
                }

                update_ordering();
            }
        },
    });
</script>

