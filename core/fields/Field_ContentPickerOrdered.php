<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_ContentPickerOrdered extends Field {

	public $select_options;

	function __construct($content_type=1) {
		$this->id = "";
		$this->name = "";
		$this->default=$content_type;
		$this->list_unpublished=false;
		$this->tags=[];
	}

	public function display() {
		$required="";
		$existing_arr = explode(",",$this->default);
		if ($this->content_type) {
			if (!is_numeric($this->content_type)) {
				// content type denoted by content controller location - this is a unique safe folder name
				// e.g. basic_article
				$this->content_type = Content::get_content_type_id($this->content_type);
			}
			if ($this->content_type && is_numeric($this->content_type)) {
				
				if ($this->list_unpublished) {
					$min_state = 0;
				}
				else {
					$min_state = 1;
				}
				if (!$this->tags) {
					// default order is alphabetical
					$query = "select * from content where content_type={$this->content_type} and state>={$min_state} order by title ASC";
					$options_all_articles = CMS::Instance()->pdo->query("select * from content where content_type={$this->content_type} and state>={$min_state} order by title ASC")->fetchAll();
				}
				else {
					$tags_csv = "'".implode("','", $this->tags)."'";
					$query = "select c.* from content c where c.content_type={$this->content_type} and c.state=1 ";
					$query .= " and c.id in (";
						$query .= " select tc.content_id from tagged tc where tc.content_type_id={$this->content_type} and tc.tag_id in (";
							$query .= "select t.id from tags t where t.state>={$min_state} and t.alias in ($tags_csv)";
						$query .= ")";
					$query .= ") order by c.title ASC";
					$stmt = CMS::Instance()->pdo->query($query);
					$options_all_articles = $stmt->fetchAll();
				}
			}
		}
		if (!$options_all_articles) {
			// content type was not able to be established
			if (Config::$debug) {
				echo "<h5>Error determining content type</h5>";
				return false;
			}
		}
		if ($this->required) {$required=" required ";}
		echo "<div class='field'>";
			echo "<label class='label'>" . $this->label . "</label>";
            echo "<div class='control'>";

			$loaded_lis = "";
                ?>
                <style>
                    .twocol_picker {display:flex; gap:2rem;}
                    .twocol_picker > * {width:50%;}
                    .twocol_picker_wrap {border:2px solid rgba(0,0,0,0.2); padding:1rem; }
					.twocol_picker_left .twocol_picker_wrap {max-height:20rem; overflow-y:auto;}
                    .twocol_picker ul {display:flex; flex-direction:column; gap:0.5rem;}
                    .twocol_picker ul li {padding:0.5rem; border:1px solid rgba(0,0,0,0.1);}
                    .twocol_picker ul li:hover {background-color:#ededed; cursor:pointer;}
					.twocol_picker li.drag-sort-active {opacity:0.2;}
					span.right-arrow {opacity:0.2; font-weight:bold;}
                </style>
                
                <hr>
                <h5 class="title"><?php echo $this->label;?></h5>
				<input type='hidden' value='<?php echo $this->default;?>' <?php echo "{$required} id='{$this->id}' {$this->get_rendered_name()}";?>>
                <div class='twocol_picker' id='twocol_picker_<?php echo $this->id;?>' >
                    <div class='twocol_picker_left'>
                        <h4 class='is-title title is-4'>All Items</h4>
                        <div class='twocol_picker_wrap'>
                            <ul class='twocol_picker_ul'>
                                <?php foreach ($options_all_articles as $item):?>
                                    <li 
                                    class='draggable_content_item twocol_picker_source_item' 
                                    draggable 
                                    data-content_id='<?php echo $item->id;?>'
                                    data-content_title='<?php echo $item->title;?>'
                                    >
                                        <?php echo $item->title;?><span class='right-arrow pull-right'>></span>
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
						foreach ($options_all_articles as $item) {
							if ($loaded_id==$item->id) {
								$loaded_lis .= "<li data-content_id='{$item->id}' data-content_title='{$item->title}'>{$item->title}</li>";
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
                <script>
                    let picker_<?php echo $this->id;?> = document.getElementById('twocol_picker_<?php echo $this->id;?>');
					// apply drag drop to server rendered lis
					let rendered_lis = picker_<?php echo $this->id;?>.querySelectorAll('.twocol_picker_right ul li');
					rendered_lis.forEach(li => {
						li.setAttribute('draggable', true);
						li.ondragend = function(item) {
							item.target.classList.remove('drag-sort-active');
							// update field
							let hidden_input = document.getElementById("<?php echo $this->id;?>");
							let csv_arr = [];
							let all_li = li.closest('ul').querySelectorAll('li');
							all_li.forEach(an_li => {
								csv_arr.push(an_li.dataset.content_id);
							});
							hidden_input.value = csv_arr.join(",");
						}
						li.ondrag = function(item){
							const selectedItem = item.target,
							list = selectedItem.parentNode,
							x = event.clientX,
							y = event.clientY;
							selectedItem.classList.add('drag-sort-active');
							let swapItem = document.elementFromPoint(x, y) === null ? selectedItem : document.elementFromPoint(x, y);
							if (list === swapItem.parentNode) {
								swapItem = swapItem !== selectedItem.nextSibling ? swapItem : swapItem.nextSibling;
								list.insertBefore(selectedItem, swapItem);
							}
						}
					});
					// handle clicks etc
                    picker_<?php echo $this->id;?>.addEventListener('click',function(e){
                        if (e.target.classList.contains('twocol_picker_source_item')) {
							let title = e.target.dataset.content_title;
							let id = e.target.dataset.content_id;
                            let li = document.createElement('LI');
							li.dataset.content_id = id;
							li.dataset.content_title = title;
							li.innerText = title;
							// handle drag drop
							li.setAttribute('draggable', true);
							li.ondragend = function(item) {
								item.target.classList.remove('drag-sort-active');
								// update field
								let hidden_input = document.getElementById("<?php echo $this->id;?>");
								let csv_arr = [];
								let all_li = ul.querySelectorAll('li');
								all_li.forEach(an_li => {
									csv_arr.push(an_li.dataset.content_id);
								});
								hidden_input.value = csv_arr.join(",");
							}
							li.ondrag = function(item){
								const selectedItem = item.target,
								list = selectedItem.parentNode,
								x = event.clientX,
								y = event.clientY;
								selectedItem.classList.add('drag-sort-active');
								let swapItem = document.elementFromPoint(x, y) === null ? selectedItem : document.elementFromPoint(x, y);
								if (list === swapItem.parentNode) {
									swapItem = swapItem !== selectedItem.nextSibling ? swapItem : swapItem.nextSibling;
									list.insertBefore(selectedItem, swapItem);
								}
							}
							// add to ul
							let ul = picker_<?php echo $this->id;?>.querySelector('.twocol_picker_right ul');
							ul.appendChild(li);
							// update field
							let hidden_input = document.getElementById("<?php echo $this->id;?>");
							let csv_arr = [];
							let all_li = ul.querySelectorAll('li');
							all_li.forEach(an_li => {
								csv_arr.push(an_li.dataset.content_id);
							});
							hidden_input.value = csv_arr.join(",");
                        }
						else {
							if (e.target.nodeName=="LI") {
								// update field
								let ul = e.target.closest('ul');
								e.target.remove();
								let hidden_input = document.getElementById("<?php echo $this->id;?>");
								let csv_arr = [];
								let all_li = ul.querySelectorAll('li');
								all_li.forEach(an_li => {
									csv_arr.push(an_li.dataset.content_id);
								});
								hidden_input.value = csv_arr.join(",");
								
								
							}
						}
                    });
                    //console.log(picker_<?php echo $this->id;?>);
                </script>
                <hr>
                <?php

			echo "</div>";
		echo "</div>";
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}

	}

	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->filter = $config->filter ?? 'CSVINT';
		$this->missingconfig = $config->missingconfig ?? false;
		$this->default = $config->default ?? '';
		$this->type = $config->type ?? 'error!!!';
		$this->content_type = $config->content_type ?? 1; // default to articles if not provided
		$this->empty_string = $config->empty_string ?? '';
		$this->tags = $config->tags ?? [];
		$this->list_unpublished = $config->list_unpublished ?? false;
	}

	public function get_friendly_value() {
		//CMS::pprint_r ($this->content_type);
		//CMS::pprint_r ($this);
		echo DB::fetch('select title from content where id=?',$this->default)->title ?? "";
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}