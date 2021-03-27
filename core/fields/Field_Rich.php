<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Rich extends Field {

	public function display() {
		?>
		<style>
		.editor { border:2px dashed #aaa; padding:1rem; max-height:25rem; overflow:auto;}
		.editor_button {margin-left:1rem;}
		.editor h1, .editor h2, .editor h3, .editor h4, .editor h5,.editor p {
			padding:0.5rem;
			margin:0.5rem;
			background:rgba(0,0,0,0.05);
			border:2px dotted rgba(0,0,0,0.05);
			position:relative;
		}
		.editor p::before, .editor h1::before, .editor h2::before, .editor h3::before, .editor h4::before, .editor h5::before {
			font-size:60%;
			background:white;
			padding:0.5rem;
			margin:0.5rem;
			box-shadow:0 0 5px rgba(0,0,0,0.2);
			display:inline-block;
			position:absolute;
			top:-1rem;
			right:-1rem;
		}
		.editor p::before {
			content:"P";
		}
		.editor p {
			overflow:hidden;
		}
		.editor h1::before {
			content:"H1";
		}
		.editor h2::before {
			content:"H2";
		}
		.editor h3::before {
			content:"H3";
		}
		.editor h4::before {
			content:"H4";
		}
		.editor h5::before {
			content:"H5";
		}
		.editor .rich_image {
			max-width:10em;
			padding:1rem;
			margin:1rem;
			border:2px dotted rgba(0,0,0,0.2);
		}
		.editor .rich_image:hover {
			cursor:pointer;
		}
		.editor .rich_image_active {
			outline:2px dotted green;
		}
		
		</style>
		<script>
			document.addEventListener("DOMContentLoaded", function(){
				
				// move markup to hidden textarea on blur
				document.querySelector('#editor_for_<?php echo $this->name;?>').addEventListener('blur',function(e){
					//console.log('updating textarea for editor');
					raw = e.target.innerHTML;
					document.querySelector('#<?php echo $this->name;?>').innerText = raw;
				});
				// move textarea to markup in editable on blur
				document.querySelector('#<?php echo $this->id;?>').addEventListener('blur',function(e){
					//console.log('updating textarea for editor');
					raw = e.target.value;
					document.querySelector('#editor_for_<?php echo $this->name;?>').innerHTML = raw;
				});
				// remove styles on paste
				document.querySelector('#editor_for_<?php echo $this->name;?>').addEventListener("paste", function(pasteEvent) {
					let found_image = false;
					pasteEvent.preventDefault();
					//console.log((pasteEvent.clipboardData || pasteEvents.originalEvent.clipboardData).items);
					let items = (pasteEvent.clipboardData || pasteEvents.originalEvent.clipboardData).items;
					for (var i = 0; i < items.length; i++) {
						// Skip content if not image
						if (items[i].type.indexOf("image") == -1) continue;
						// Retrieve image on clipboard as blob
						let blob = items[i].getAsFile();
						var reader = new FileReader();
						reader.onload = function(event) {
							//let pasted_img = new Image;
							//pasted_img.src = event.target.result;
							let img_markup = `<img class="pasted rich_image" src="${event.target.result}"/>`;
							document.execCommand("insertHTML", false, img_markup);
						};
    					reader.readAsDataURL(blob);
						found_image = true;
					}
					
					if (!found_image) {
						// assume text / rich or otherwise
						console.log('not image - cleaning paste');
						let text = pasteEvent.clipboardData.getData("text/plain");
						document.execCommand("insertHTML", false, text);
					}
				});
				// click event handler for editor - for now used for handling image float changes etc... //rich_image
				document.querySelector('#editor_for_<?php echo $this->name;?>').addEventListener('click',function(e){
					if (e.target.classList.contains('rich_image')) {
						// handle rich image click
						// clear any active rich image
						var current_active = document.querySelector('#editor_for_<?php echo $this->name;?> .rich_image_active');
						if (current_active!==null) {
							current_active.classList.remove('rich_image_active');
						}
						// make clicked active
						var img = e.target;
						img.classList.add('rich_image_active');
					}
				});


				// toolbar click - TODO: handle multiple editors per page // DONE?

				document.querySelector('#editor_toolbar_for_<?php echo $this->name; ?>').addEventListener('click',function(e){
					e.preventDefault();

					if (e.target.classList.contains('fa')) {
						editor_button = e.target.closest('.editor_button');
					}
					else {
						editor_button = e.target;
					}
					command = editor_button.dataset.command;
					console.log('Command: ',command);

					if (editor_button.classList.contains('toggle_editor_raw')) {
						control = editor_button.closest('.control');
						raw = control.querySelector('textarea.editor_raw');
						if (raw.style.display=='block') {
							raw.style.display='none';
						}
						else {
							raw.style.display='block';
						}
						return false;
					}

					if (command == 'h1' || command == 'h2' || command == 'h3' || command == 'h4' || command == 'p') {
						document.execCommand('formatBlock', false, command);
					}
					
					else if (command == 'createlink' || command == 'insertimage') {
						url = prompt('Enter the link here: ','https:\/\/');
						document.execCommand(command, false, url);
					}

					else if (command=='floatleft') {
						var active_image = document.querySelector('#editor_for_<?php echo $this->name;?> .rich_image_active');
						if (active_image!==null) {
							active_image.classList.add('pull-left');
							active_image.classList.remove('rich_image_active','pull-right');
						}
						else {
							alert('No image selected');
						}
					}

					else if (command=='edit_image_props') {
						var active_image = document.querySelector('#editor_for_<?php echo $this->name;?> .rich_image_active');
						if (active_image!==null) {
							let new_alt = window.prompt('Enter ALT text: ',active_image.alt);
							let new_title = window.prompt('Enter TITLE text: ',active_image.title);
							if (new_alt) {
								active_image.alt = new_alt;
							}
							if (new_alt) {
								active_image.title = new_title;
							}
							// TODO: force change into textarea containing markup?
						}
						else {
							alert('No image selected');
						}
					}

					else if (command=='floatright') {
						var active_image = document.querySelector('#editor_for_<?php echo $this->name;?> .rich_image_active');
						if (active_image!==null) {
							active_image.classList.add('pull-right');
							active_image.classList.remove('rich_image_active','pull-left');
						}
						else {
							alert('No image selected');
						}
					}

					else if (command=='floatclear') {
						var active_image = document.querySelector('#editor_for_<?php echo $this->name;?> .rich_image_active');
						if (active_image!==null) {
							active_image.classList.remove('pull-left','pull-right');
							active_image.classList.remove('rich_image_active');
						}
						else {
							alert('No image selected');
						}
					}
					
					else if (command == 'img') {
						//alert('choose image');
						// launch image selector
						var media_selector = document.createElement('div');
						media_selector.innerHTML =`
						<div class='media_selector_modal' style='position:fixed;width:100vw;height:100vh;background:black;padding:1em;left:0;top:0;z-index:99;'>
						<button id='media_selector_modal_close' class="modal-close is-large" aria-label="close"></button>
						<h1 style='color:white;'>Choose Image <a href='#' class='delete_parent'>X</a></h1>
						<div class='form-group'>
							<input id='media_selector_modal_search'/><button type='button' id='trigger_media_selector_search'>Search</button>
						</div>
						<div class='media_selector'><h2>LOADING</h2></div>
						</div>
						`;
						document.body.appendChild(media_selector); 

						// remember the editor to refocus for image insertion
						window.last_editor = document.querySelector('#editor_toolbar_for_<?php echo $this->name; ?>');
						window.sel = document.getSelection(); 
						window.saved = [ window.sel.focusNode, window.sel.focusOffset ];

						// handle click close
						document.getElementById('media_selector_modal_close').addEventListener('click',function(e){
							var modal = e.target.closest('.media_selector_modal');
							modal.parentNode.removeChild(modal);
						});
						// handle modal close
						media_selector.querySelector('.delete_parent').addEventListener('click',function(e){
							e.preventDefault();
							e.target.parentNode.parentNode.parentNode.removeChild(e.target.parentNode.parentNode);
						});
						// add click event handler to capture child selection clicks
						media_selector.addEventListener('click',function(e){
							//console.log(e.target);
							e.preventDefault();
							e.stopPropagation();
							let selected_image = e.target.closest('.media_selector_selection');
							console.log(selected_image);
							if (selected_image!==null) {
								let media_id = selected_image.dataset.id;
								let alt = selected_image.querySelector('img').alt;
								let title = selected_image.querySelector('img').title;
								let url = `<?php echo Config::$uripath;?>/image/${media_id}/web`;
								let image_markup = `<img alt="${alt}" title="${title}" class="rich_image" data-media_id="${media_id}" data-size="web" src="${url}"/>`;
								console.log(image_markup);
								// refocus editor
								window.last_editor.focus();
								// restore caret position
								window.sel.collapse(window.saved[0], window.saved[1]);
								// insert image
								document.execCommand('insertHTML',false, image_markup);
								let modal = selected_image.closest('.media_selector_modal');
								modal.parentNode.removeChild(modal);
							} // else clicked on container not on an anchor or it's children
						});
						// search handler
						let searchtrigger = document.getElementById('trigger_media_selector_search').addEventListener('click',function(e){
							let searchtext = document.getElementById('media_selector_modal_search').value;
							fetch_images(searchtext, null); // string, no tags
						});

						// do initial listing
						fetch_images (null, null); // no search, all tags

						function fetch_images(searchtext, taglist) {
						
							// fetch images
							postAjax('<?php echo Config::$uripath;?>/admin/images/api', {"action":"list_images","searchtext":searchtext}, function(data) { 
								let image_list = JSON.parse(data);
								let image_list_markup = "<ul class='media_selector_list single'>";
								image_list.images.forEach(image => {
									image_list_markup += `
									<li>
										<a class='media_selector_selection' data-id='${image.id}'>
										<img title='${image.title}' alt='${image.alt}' src='<?php echo Config::$uripath;?>/image/${image.id}/thumb'>
										<span>${image.title}</span>
										</a>
									</li>`;
								});
								image_list_markup += "</ul>";
								media_selector.querySelector('.media_selector').innerHTML = image_list_markup;
								
							});
						}
					}

					else document.execCommand(command, false, null);
				});
			});
		</script>
		<?php
		if (!Config::$debug) {
			echo "<style>.editor_raw {display:none;}</style>";
		}
		echo "<div class='field'>";
			echo "<label class='label'>{$this->label}</label>";
			echo "<div class='control'>";
				$required="";
				if ($this->required) {$required=" required ";}
				?>
				<!-- toolbar -->
				<div class='hbcms_editor_toolbar' id='editor_toolbar_for_<?php echo $this->name; ?>'>
					<a class='editor_button' href="#" data-command='h1'>H1</a>
					<a class='editor_button' href="#" data-command='h2'>H2</a>
					<a class='editor_button' href="#" data-command='h3'>H3</a>
					<a class='editor_button' href="#" data-command='h4'>H4</a>
					<a class='editor_button' href="#" data-command='p'>P</a>
					<a class='editor_button' href="#" data-command='img'><i class="fa fa-images"></i></a>
					<a class='editor_button' href="#" data-command='undo'><i class='fa fa-undo'></i></a>
					<a class='editor_button' href="#" data-command='createlink'><i class='fa fa-link'></i></a>
					<a class='editor_button' href="#" data-command='justifyLeft'><i class='fa fa-align-left'></i></a>
					<a class='editor_button' href="#" data-command='superscript'><i class='fa fa-superscript'></i></a>
					<a class='editor_button image_selected' href='#' data-command='floatleft'>FL</a>
					<a class='editor_button image_selected' href='#' data-command='floatright'>FR</a>
					<a class='editor_button image_selected' href='#' data-command='floatclear'>FC</a>
					<a class='editor_button image_selected' href='#' data-command='edit_image_props'>ALT/TITLE</a>
					<a class='editor_button toggle_editor_raw' href="#" data-command='none'><i class='fa fa-edit'></i></a>
				</div>
				<?php
				echo "<div class='editor content' contentEditable='true' id='editor_for_{$this->name}'>{$this->default}</div>";
				echo "<h6 class='editor_raw'>Raw Markup</h6>";
				echo "<textarea value='' maxlength={$this->maxlength} minlength={$this->minlength} class='filter_{$this->filter} input editor_raw' {$required} type='text' id='{$this->id}' {$this->get_rendered_name()}>{$this->default}</textarea>";
			echo "</div>";
			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>";
	}

	public function inject_designer_javascript() {
		?>
		<script>
			window.Field_Rich = {};
			// template is what gets injected when the field 'insert new' button gets clicked
			window.Field_Rich.designer_template = `
			<div class="field">
				<h2 class='heading title'>Rich/HTML Field</h2>	

				<label class="label">Label</label>
				<div class="control has-icons-left has-icons-right">
					<input required name="label" class="input iss-success" type="label" placeholder="Label" value="">
				</div>

				<label class="label">Required</label>
				<div class="control has-icons-left has-icons-right">
					<input name="required" class="checkbox iss-success" type="checkbox"  value="">
				</div>
			</div>`;
		</script>
		<?php 
	}

	public function designer_display() {

	}

	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->maxlength = $config->maxlength ?? 9999;
		$this->filter = $config->filter ?? 'RAW';
		$this->minlength = $config->minlength ?? 0;
		$this->missingconfig = $config->missingconfig ?? false;
		$this->type = $config->type ?? 'error!!!';
		$this->default = $config->default ?? '<p></p>';
	}

	public function validate() {
		// TODO: enhance validation
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}