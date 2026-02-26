<?php
namespace HoltBosse\Alba\Fields\PickerOrdered;

Use HoltBosse\Form\{Field, Input};
Use Respect\Validation\Validator as v;
Use HoltBosse\Alba\Components\CssFile\CssFile;

class PickerOrdered extends Field {
	// @phpstan-ignore missingType.iterableValue
	public array $select_options = [];
	public bool $searchable = true;

	public function display(): void {
		$required="";
		$existing_arr = explode(",",$this->default);

		if ($this->required) {$required=" required ";}
		echo "<div class='field'>";
			echo "<label class='label'>" . $this->label . "</label>";
            echo "<div class='control'>";

			$loaded_lis = "";

			(new CssFile())->loadFromConfig((object)[
				"filePath"=>__DIR__ . "/style.css",
				"injectIntoHead"=>false,
			])->display();
                ?>
                
                <hr>
                <h5 class="title"><?php echo $this->label;?></h5>
				<input data-repeatableindex="{{replace_with_index}}" class='picker_data' type='hidden' value='<?php echo $this->default;?>' <?php echo "{$required} id='{$this->id}' {$this->getRenderedName()} {$this->getRenderedForm()}";?>>
                <div class='twocol_picker' id='twocol_picker_<?php echo $this->id;?>' >
                    <div class='twocol_picker_left'>
                        <h4 class='is-title title is-4'>All Items</h4>
						<?php if ($this->searchable):?>
						<div class='contentpicker_search_wrap'>
							<div class="control pickersearch_control">
								<input id='<?php echo $this->id;?>_search' value="" name="contentpicker_search" class="input pickersearch" type="text" placeholder="Search">
							</div>
							<button type='button' class='is-default is-small button pickersearch_clear' >X</button>
						</div>
						<?php endif; ?>
                        <div class='twocol_picker_wrap'>
                            <ul class='twocol_picker_ul'>
                                <?php foreach ($this->select_options as $item):?>
                                    <li 
									<?php if (in_array($item->value, $existing_arr)) { 
										echo " style='display:none;' ";
										$picked_class = " picked";
									}
									else {
										$picked_class = "";
									}
									?>
                                    class='draggable_content_item twocol_picker_source_item <?php echo $picked_class; ?>' 
                                    draggable 
                                    data-content_id='<?php echo $item->value;?>'
                                    data-content_title='<?php echo $item->text;?>'
                                    >
                                        <?php echo Input::stringHtmlSafe($item->text);?><span class='right-arrow pull-right'>></span>
                                    </li>
									
                                <?php endforeach; ?>
                            </ul>
							
                        </div>
                    </div>
					<?php 
					// generate default currently selected list
					// check for existing content
					// (ensures that if content is deleted, won't show up here)
					foreach ($existing_arr as $loaded_id) {
						$exists = false;
						foreach ($this->select_options as $item) {
							if ($loaded_id==$item->value) {
								$loaded_lis .= "<li data-content_id='{$item->value}' data-content_title='{$item->text}'>" . Input::stringHtmlSafe($item->text) . "</li>";
								break;
							}
						}
					}
					?>
                    <div class="twocol_picker_right">
                        <h4 class='is-title title is-4'>Currently Selected</h4>
                        <div class='twocol_picker_wrap'>
                            <ul class='twocol_picker_ul'>
								<?php echo $loaded_lis;?>
                            </ul>
                        </div>
						
                    </div>
					
                </div>
				<p class='note'>Click items on the left to add to selected area. Drag and drop in selected area to reorder. To remove a selected item, click it.</p>
                <script type="module">
					<?php
						$scriptContent = file_get_contents(__DIR__ . "/script.js");
						$scriptContent = str_replace("{{replace_with_rendered_name}}", $this->getRenderedName(), $scriptContent);
						echo $scriptContent;
					?>
                </script>
                <hr>
                <?php

			echo "</div>";
		echo "</div>";
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}

	}

	public function loadFromConfig(object $config): self {
		parent::loadFromConfig($config);
		
		$this->filter = $config->filter ?? v::StringVal();
		$this->type = $config->type ?? 'error!!!';
		$this->searchable = $config->searchable ?? true;
		$this->select_options = $config->select_options ?? [];

		return $this;
	}

	public function validate(): bool {
		if ($this->isMissing()) {
			return false;
		}
		return true;
	}
}