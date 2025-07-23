<?php
namespace HoltBosse\Alba\Fields\ImagePosition;

Use HoltBosse\Form\Field;
Use HoltBosse\Form\Fields\Input\Input as TextInput;
Use HoltBosse\Alba\Core\{CMS, Plugin, Hook};
Use HoltBosse\DB\DB;

class ImagePosition extends TextInput {
    public $fieldname;

    public function display() {
        ?>
            <style>
                [data-field_id]:has([<?php echo $this->getRenderedName(); ?>]) {
                    display: none;
                }

                <?php echo file_get_contents(__DIR__ . "/style.css"); ?>
            </style>
        <?php

        echo "<div style='display: none;'>";
            parent::display();
        echo "</div>";
    }

    public function loadFromConfig($config) {
		parent::loadFromConfig($config);

        $this->fieldname = $config->fieldname ?? null;

        $pluginConfig = (object) [
            "title"=>"fake plugin from ImagePosition",
            "location"=>"",
            "id"=>"ImagePositionPlugin",
            "description"=>"ImagePosition Field Plugin",
            "version"=>"1.0",
            "author"=>"workaj",
            "website"=>"https://alba.holtbosse.com",
        ];

        $fakeplugin = new class($pluginConfig, $this) extends Plugin {
            public $field;
            
            public function __construct($pluginConfig, $field) {
                $this->field = $field;

                parent::__construct($pluginConfig);
            }
            
            public function init() {
                CMS::add_action("render_image_field_buttons",$this,'add_button'); // label, function, priority  
            }

            public function add_button($pageContents, ...$args) {
                $field = $args[0][0];

                ob_start();
                    $buttonId = "id_" . uniqId();
                    ?>
                        <button id="<?php echo $buttonId; ?>" data-repeatableindex="{{replace_with_index}}" type="button" class='button is-success'>Set Focal Point</button>
                        <script type="module">
                            document.querySelector("#trigger_image_clear_<?php echo $this->field->fieldname; ?>").addEventListener("click", ()=>{
                                document.querySelector(`[<?php echo $this->field->getRenderedName(); ?>]`).value = "";
                            });

                            document.querySelector(`#<?php echo $buttonId; ?>[data-repeatableindex="{{replace_with_index}}"]`).addEventListener("click", (e)=>{
                                let img_wrapper = document.getElementById("selected_image_<?php echo $this->field->fieldname; ?>");
                                if(!img_wrapper.closest(".selected_image_wrap").classList.contains("active")) {
                                    alert("no image selected");
                                    return false;
                                }
                                let imageUrlChunks = img_wrapper.querySelector("img").getAttribute("src").split("/");
                                imageUrlChunks = imageUrlChunks.filter((el)=>{return el!="";});
                                if(imageUrlChunks[imageUrlChunks.length-1]=="thumb") {
                                    imageUrlChunks.pop();
                                }
                                let id = imageUrlChunks[imageUrlChunks.length-1];
                                //console.log(id);
                                
                                const dialog = document.createElement("dialog");
                                dialog.innerHTML = `
                                    <div class="imagepositiondialogwrapper">
                                        <span class="imagepositiondialogclose">X</span>
                                        <br>
                                        <br>
                                        <div class="image-focal-point-wrapper">
                                            <img src="/image/${id}">
                                            <span class="image-focal-point-target">+</span>
                                        </div>
                                        <br>
                                        <button id="imagepositionset" type="button" class="button is-success">Set</button>
                                    </div>
                                `;
                                dialog.classList.add("imagepositiondialog");

                                if(document.querySelector(`[<?php echo $this->field->getRenderedName(); ?>]`).value!="") {
                                    const chunks = document.querySelector(`[<?php echo $this->field->getRenderedName(); ?>]`).value.split(" ");
                                    //console.log(chunks);
                                    
                                    dialog.querySelector(".image-focal-point-target").style.left = chunks[0];
                                    dialog.querySelector(".image-focal-point-target").style.top = chunks[1];
                                }

                                dialog.querySelector("#imagepositionset").addEventListener("click", (e)=>{
                                    const dataStore = document.querySelector(`[<?php echo $this->field->getRenderedName(); ?>]`);
                                    const pointer = dialog.querySelector(".image-focal-point-target");

                                    dataStore.value = `${pointer.style.left} ${pointer.style.top}`;
                                    //console.log(dataStore.value);
                                    
                                    dialog.remove();
                                });
                                dialog.querySelector(".imagepositiondialogclose").addEventListener("click", (e)=>{
                                    dialog.remove();
                                });
                                dialog.querySelector(".image-focal-point-wrapper").addEventListener("click", (e)=>{
                                    const rect = dialog.querySelector(".image-focal-point-wrapper").getBoundingClientRect();
                                    const x = e.clientX - rect.left;
                                    const y = e.clientY - rect.top;

                                    const xPercent = (x / rect.width) * 100;
                                    const yPercent = (y / rect.height) * 100;
                                    
                                    const pointer = dialog.querySelector(".image-focal-point-target");
                                    pointer.style.left = `${xPercent}%`;
                                    pointer.style.top = `${yPercent}%`;

                                });
                                document.body.appendChild(dialog);
                                dialog.showModal();
                            });
                        </script>
                    <?php
                $buttonContents = ob_get_clean();

                if($this->field->fieldname==$field->name) {
                    return $pageContents . $buttonContents;
                } else {
                    return $pageContents;
                }
            }
        };
		
	}
}