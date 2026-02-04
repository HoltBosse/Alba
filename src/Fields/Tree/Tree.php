<?php
namespace HoltBosse\Alba\Fields\Tree;

Use HoltBosse\Form\Field;
Use HoltBosse\DB\DB;

class Tree extends Field {

	public $dataset;
	public $sql;

	public function display() {
		echo "<h1>{$this->label}</h1>";
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}
		echo "<div class='tree_field_container'>";
			?>

			<!-- display available items for tree -->
			
			<div class="field is-grouped is-grouped-multiline available_tree_items">
				
				<?php foreach ($this->dataset as $item):?>
				<div class="control">
					<div class="tags">
						<span 	draggable="true" 
								data-transferjson='{"value":<?php echo $item->value;?>,"text":"<?php echo $item->text;?>"}' 
								data-title="<?php echo $item->text;?>" 
								data-id="<?php echo $item->value;?>"
								 ondragstart="tree_dragstart_handler(event)" 
								 class='tree_item tag is-link is-light is-info'>
								 <i class="fas fa-grip-lines"></i> &nbsp;&nbsp;&nbsp; <?php echo $item->text;?>
						</span>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			
			

			<div id='tree_wrap' class='tree_field_tree_wrap'>
				<!-- <div class='tree_field_node root'>
					<p><strong>Root</strong></p>
					<div class='tree_field_node_drop_wrap'>
						<div droppable="true" 
							class='tree_field_node_drop'
							data-nodejson='{"value":-1,"text":"Root","children":[]}';
							ondrop="drop_handler(event)" ondragover="dragover_handler(event)">
							Drop Here
						</div>
					</div>
				</div> -->
			</div>

			<?php
			$required="";
			$decoded_default = html_entity_decode ($this->default);
			if ($this->required) {$required=" required ";}
			echo "<input type='hidden' value='{$decoded_default}' class='filter_{$this->filter} input' {$required} type='text' id='{$this->id}' {$this->getRenderedName()} {$this->getRenderedForm()}>";	
		echo "</div>";
		?>
		<script>
			// old default init
			/* window.tree = {
				"children":[],
				"value":-1,
				"text":"Root",
				"parent":false
			}; */
			window.tree = JSON.parse('<?php echo $decoded_default;?>');

			function rerender_tree() {
				markup = render_node(window.tree);
				//console.log(markup);
				document.getElementById('tree_wrap').innerHTML = markup;
			}

			function render_node(node) {
				root_class = '';
				is_root = false;
				if (node.value==-1) {
					is_root = true;
					root_class = ' root ';
				}
				markup='';
				markup+=`
				<div class='tree_field_node ${root_class}'
				draggable="true" 
							data-transferjson='{"value":${node.value},"text":"${node.text}"}' 
							data-title="${node.text}" 
							data-id="${node.value}"
							ondragstart="tree_dragstart_handler(event)">
					<div class='tree_field_node_drop_wrap'>
						<p 
							class='node_title'>
							<strong>${node.text}</strong>
						</p>`
						if (!is_root) {
							markup+=`
							
							<div class='before_after_node_drop' droppable="true" 
									data-nodejson='{"value":${node.value},"text":"${node.text}","children":[]}' 
									ondrop="drop_handler_sibling(event,'before')" ondragover="dragover_handler(event)">
									Insert Before
							</div>`;
						}
						markup+=`
						<div droppable="true" 
							class='tree_field_node_drop'
							data-nodejson='{"value":${node.value},"text":"${node.text}","children":[]}' 
							ondrop="drop_handler(event)" ondragover="dragover_handler(event)">
							Add Child
						</div>`;
						if (!is_root) {
							markup+=`
							<div class='before_after_node_drop' droppable="true" 
									data-nodejson='{"value":${node.value},"text":"${node.text}","children":[]}' 
									ondrop="drop_handler_sibling(event,'after')" ondragover="dragover_handler(event)">
									Insert After
							</div><button data-id=${node.value} class='warning delete is-danger' type='button'></button>`;
						}
						markup+=`
					</div>
					<div class='node_children'>`;
				node.children.forEach(child => {
					markup+=render_node(child);
				});
				markup+=`
					</div>
				</div>
				`;
				return markup;
			}

			function get_node(a_node, value) {
				//console.log('Looking for ',value, ' in node ', a_node);
				var found_node = null;
				if (a_node.value==value) {
					//console.log('found');
					//return node;
					found_node = a_node;
				}
				if (found_node===null) {
					//console.log('checking children...');
					for (var n=0; n < a_node.children.length; n++) {
						//console.log(a_node, ' has child - testing ',n);
						found_node = get_node(a_node.children[n], value);
						if (found_node!==null) {
							break;
						}
					}
				}
				return found_node;
			}

			function update_field_data() {
				hidden_field = document.getElementById('<?php echo $this->id;?>');
				hidden_field.value = JSON.stringify(window.tree);
			}

			function add_node (node, parent) {
				//console.log('Adding ',node,' to ',parent);
				parent.children.push({
					"value":node.value,
					"text":node.text,
					"children":[],
					"parent":parent.value
				});
				update_field_data();
			}

			function add_sibling_node (node, sibling, position) {
				//console.log('adding ',node, ' to ', sibling, ' at ',position);
				// node to be added, sibling node, position = 'before' or 'after'
				sibling_parent = get_node (window.tree, sibling.parent);
				//console.log('sibling parent: ',sibling_parent);
				sibling_index = get_node_index (sibling_parent, sibling);
				
				if (position=='before') {
					target_index=sibling_index;
				}
				else {
					target_index=sibling_index+1;
				}
				sibling_parent.children.splice(target_index, 0, {
					"value":node.value,
					"text":node.text,
					"children":[],
					"parent":sibling_parent.value
				});
				update_field_data();
			}

			function tree_dragstart_handler(ev) {
				//console.log(JSON.parse(ev.target.dataset.transferjson));
				ev.dataTransfer.setData("text/plain", ev.target.dataset.transferjson);
			}
			function dragover_handler(ev) {
				ev.preventDefault();
				ev.dataTransfer.dropEffect = "move";
			}

			

			function drop_handler(ev) {
				ev.preventDefault();
				var data = ev.dataTransfer.getData("text/plain");
				new_node_data = JSON.parse(ev.dataTransfer.getData("text/plain"));
				parent_node_data = JSON.parse(ev.target.dataset.nodejson);
				//console.log(parent_node_data);
				parent_node = get_node(window.tree, parent_node_data.value);
				if (parent_node===null) {
					//console.log('Error - parent node not found.');
					return false;
				}
				else {
					/* draggable_tree_items = document.querySelectorAll('.tree_item');
					draggable_tree_items.forEach(item => {
						if (item.dataset.id==new_node_data.value) {
							item.removeAttribute('draggable');
							item.classList.toggle('disabled');
						}
					}); */
					// first check if node exists in tree already
					// this should only happen if tree node is dragged
					// and not a new node tag
					var existing_node = get_node(window.tree, new_node_data.value);
					
					if (existing_node===null) {
						// dragged from tag list
						draggable_tree_items = document.querySelectorAll('.tree_item');
						draggable_tree_items.forEach(item => {
							if (item.dataset.id==new_node_data.value) {
								item.removeAttribute('draggable');
								item.classList.remove('disabled');
							}
						});
					}
					else {
						if (existing_node.children.length>0) {
							alert('Cannot move items with children yet');
							return false;
						}
						// dragged from tree and not tag list - remove existing node
						var parent = get_node(window.tree, existing_node.parent);
						var index=0;
						for (i=0; i<parent.children.length; i++) {
							if (parent.children[i].value==existing_node.value) {
								index=i;
								break;
							}
						}
						var removed = parent.children.splice(index, 1); 
					}
					
					add_node (new_node_data, parent_node);
				}
				rerender_tree();
			}

			function get_node_index (parent_node, child_node) {
				for (var n=0; n<parent_node.children.length; n++) {
					if (parent_node.children[n].value==child_node.value) {
						return n;
					}
				}
				return null;
			}

			// sibling drop handler
			function drop_handler_sibling(ev, position) {
				ev.preventDefault();
				/* console.log('inserting sibling ',position);
				return false; */
				var data = ev.dataTransfer.getData("text/plain");
				var new_node_data = JSON.parse(data);
				var sibling_node_data = JSON.parse(ev.target.dataset.nodejson);
				//console.log(parent_node_data);
				var sibling_node = get_node(window.tree, sibling_node_data.value);
				if (sibling_node===null) {
					console.log('Error - sibling_node not found.');
					return false;
				}
				else {

					// first check if node exists in tree already
					// this should only happen if tree node is dragged
					// and not a new node tag
					var existing_node = get_node(window.tree, new_node_data.value);
					
					if (existing_node===null) {
						// dragged from tag list
						draggable_tree_items = document.querySelectorAll('.tree_item');
						draggable_tree_items.forEach(item => {
							if (item.dataset.id==new_node_data.value) {
								item.removeAttribute('draggable');
								item.classList.toggle('disabled');
							}
						});
					}
					else {
						if (existing_node.children.length>0) {
							alert('Cannot move items with children yet');
							return false;
						}
						// dragged from tree and not tag list - remove existing node
						var parent = get_node(window.tree, existing_node.parent);
						var index=0;
						for (i=0; i<parent.children.length; i++) {
							if (parent.children[i].value==existing_node.value) {
								index=i;
								break;
							}
						}
						var removed = parent.children.splice(index, 1); 
					}

					// add new sibling node to tree
					add_sibling_node (new_node_data, sibling_node, position);
					//add_node (new_node_data, parent_node);
				}
				rerender_tree();
			}

			// delete tree node event listener
			document.getElementById('tree_wrap').addEventListener('click',function(e){
				if (e.target.classList.contains('delete')) {
					e.preventDefault();
					e.stopPropagation();
					node_id = parseInt(e.target.dataset.id);
					//console.log('About to get_node ',node_id, ' in tree ', window.tree);
					//return false;
					node = get_node (window.tree, node_id);
					if (node===null) {
						console.log('failed to find node in ', window.tree, ' with value ', node_id);
						return false;
					}
					if (node.children.length>0) {
						alert('Cannot remove items with children');
						return false;
					}
					
					console.log('Getting parent node of ',node, ' in tree ', window.tree);
					/* return false; */
					parent = get_node(window.tree, node.parent);
					if (parent!==null) {
						// reset tree item to be draggable
						draggable_tree_items = document.querySelectorAll('.tree_item');
						draggable_tree_items.forEach(item => {
							if (item.dataset.id==node_id) {
								item.setAttribute('draggable','true');
								item.classList.toggle('disabled');
							}
						});
						// remove from tree and render
						index=0;
						for (i=0; i<parent.children.length; i++) {
							if (parent.children[i].value==node_id) {
								index=i;
								break;
							}
						}
						
						removed = parent.children.splice(index, 1); 
						//console.log(removed);
						
						update_field_data();
						rerender_tree();
					}
					else {
						console.log('Error - parent not found');
					}
					
				}
			});

			// check on load if saved draggable tree items are already in tree
			// if they are set to disabled and undraggable
			draggable_tree_items = document.querySelectorAll('.tree_item');
			draggable_tree_items.forEach(draggable_item => {
				found_node = get_node (window.tree, draggable_item.dataset.id);
				if (found_node) {
					draggable_item.removeAttribute('draggable');
					draggable_item.classList.toggle('disabled');
				}
			});

			// render on load
			rerender_tree();


		</script>
		<?php
	}

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);
		
		$this->sql = $config->sql ?? 'SELECT id AS value, title AS text FROM pages WHERE state=1';
		$this->dataset = DB::fetchAll($this->sql);
		$this->default = $config->default ?? '{"parent":null,"value":"-1","text":"Root","children":[]}';

		return $this;
	}

	public function validate() {
		// TODO: enhance validation
		if ($this->isMissing()) {
			return false;
		}
		return true;
	}
}