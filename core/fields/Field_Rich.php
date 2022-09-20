<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Rich extends Field {

	public function display() {
		?>
		<style>
		.editor a {
			position:relative;
		}
		.editor a[target='_blank']::before {
			content: "🔗";
			font-weight:400;
			display:block;
			position:absolute;
			transform:translateY(-1em);
			text-align:right;
			font-size:12px;
			opacity:0.5;
			right:0;
			top:0;
			min-width:16px;
		}
		.editor .internal_anchor {
			display:inline-block;
			width:2em;
			height:2em;
			text-align:center;
			aspect-ratio:1/1;
			content:"A";
			background:black;
			color:white;
			margin:0.25em;
			font-size:10px;
			padding:0.25em;
			/* text-indent:-9999px; */
		}
		
		.editor { border:2px dashed #aaa; padding:1rem; max-height:25rem; overflow:auto;}
		.editor_button {margin-left:1rem;}
		.editor h1, .editor h2, .editor h3, .editor h4, .editor h5,.editor p, .editor ul, .editor ol {
			padding:0.5rem;
			margin:0.5rem;
			background:rgba(0,0,0,0.05);
			border:2px dotted rgba(0,0,0,0.05);
			position:relative;
		}
		.editor p::before, .editor h1::before, .editor h2::before, .editor h3::before, .editor h4::before, .editor h5::before, .editor ul::before, .editor ol::before {
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
		.editor ul::before {
			content:"UL";
		}
		.editor ol::before {
			content:"UL";
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
		.editor figure.rich_image_figure {
			margin: 1rem;
			padding: 1rem;
			border: 2px solid rgba(0,0,0,0.1);
			display: inline-block;
		}
		</style>
		<script>

			document.addEventListener("DOMContentLoaded", function(){
				
				// move markup to hidden textarea on input
				document.querySelector('#editor_for_<?php echo $this->name;?>').addEventListener('input',function(e){
					raw = e.target.innerHTML; 
					textarea = document.querySelector('#<?php echo $this->name;?>');
					textarea.value = raw; console.log(textarea);
				});
				// move textarea to markup in editable on any change
				document.querySelector('#<?php echo $this->id;?>').addEventListener('input',function(e){
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
					else {
						// remove rich_image_active
						let all_editor_active_images = e.target.closest('.editor').querySelectorAll('img.rich_image_active');
						all_editor_active_images.forEach(img => {
							img.classList.remove('rich_image_active');
						});
					}
				});

				/**
				 * Creates and displays a modal handling onclick events for user input. An example function call might look
				 * like the following:
				 * 
				 * createModal(["Name", "Age"], 
				 *			   ["John", "23"],
				 *			   ["This is what people call you", "This is how long you've existed"], 
				 *			   function() { console.log("I'm called in onCreate!")}, 
				 *			   function() { console.log("I'm called in onAdd!")}, 
				 *			   function() { console.log("I'm called in onCancel!")});
				 *
				 * @param {array} inputLabels - An array with the basic names of inputs being requested.
				 * @param {array} inputIds - An array containing the ids for created input fields.
				 * @param {array} currentValues - An array containing the current values of inputs if they exist. Empty by default.
				 * @param {array} helpLabels - An array containing the help values associated with the inputs to be displayed to the user.
				 * @param {function} onCreate - A user defined function to be executed upon creation of the modal.
				 * @param {function} onAdd - A user defined function to be executed upon user's click of "Add" button.
				 * @param {function} onCancel - A user defined function to be executed upon user's click of "Cancel" button.
				**/
				function createModal(inputLabels, inputIds, currentValues=[], helpLabels, onCreate, onAdd, onCancel) {

					onCreate();
					// create and show modal based on desired user inputs
					let modal = document.createElement('div');
					// modal.id = "add_info_modal_for_<?php echo $this->name;?>";
					modal.classList = "modal is-active";
					let modal_html = `
						<div class="modal-background"></div>
						<div class="modal-content">
							<div class="box">
					`;
					for (let i = 0; i < inputLabels.length; i++) {
						modal_html += `
								<div class="field">
									<label class="label">${inputLabels[i]}</label>
									<div class="control">
										<input id="${inputIds[i]}" class="input" type="text" value="${currentValues[i]}">
									</div>
									<p class='help'>${helpLabels[i]}</p>
								</div>
						`;
					}
					modal_html += `
								<button class="button is-primary" data-modal-action="add">Add</button>
								<button class="button is-warning" data-modal-action="cancel">Cancel</button>
								
							</div>
						</div>						
					`;

					modal.innerHTML = modal_html;
					document.body.appendChild(modal);

					// make first model input focus
					let first_input = modal.querySelector('input');
					if (first_input) {
						first_input.focus();
					}

					// listener for modal
					modal.addEventListener('click', function(e){
						e.preventDefault();

						function closeModal() {
							modal = e.target.closest('.modal.is-active');
							parent = modal.parentNode;
							parent.removeChild(modal);	
							// update editor raw textarea with changes
							let markup = document.querySelector('#editor_for_<?php echo $this->name;?>').innerHTML;
							document.querySelector('#<?php echo $this->name;?>').value = markup;
						}

						switch (e.target.dataset.modalAction) {
							
							case "add":
								onAdd();
								closeModal();
								break;

							case "cancel":
								onCancel();
								closeModal();
								break;
						}

					});
				}

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
					
					else if (command == 'createlink') {

						var selection = window.getSelection().toString();

						let uniq = ("<?php echo $this->name;?>").replace(/\s+/g, '_');		// for ids
						
						let iL = ["URL", "Display Text"];
						let iI = [`url_for_${uniq}`, `display_text_for_${uniq}`];
						let cV = ["", selection];
						let hL = ["Enter full link including https://", ""];

						function onCreate() {
							// insert link html at anchor location
							let link_html = "<a id='newly_created_link_for_<?php echo $this->name;?>'>" + selection + "</a>";
							document.execCommand('insertHTML', false, link_html);							
							let link = document.getElementById('newly_created_link_for_<?php echo $this->name;?>');
							link.removeAttribute('style');	// get rid of any styling that might be preserved from user copy/paste
						}

						function onAdd() {
							// get values and update link
							let link = document.getElementById('newly_created_link_for_<?php echo $this->name;?>');
							link.href = document.getElementById(iI[0]).value;
							link.innerHTML = document.getElementById(iI[1]).value;
							link.removeAttribute('id');		// remove id so future links not messed up
						}


						function onCancel() {
							// replace anchor with original text
							let link = document.getElementById('newly_created_link_for_<?php echo $this->name;?>');
							link.parentNode.replaceChild(document.createTextNode(selection), link);
						}

						createModal(iL, iI, cV, hL, onCreate, onAdd, onCancel);

					}

					else if (command == 'createanchor') {
						window.sel = document.getSelection();
						anchorid = prompt('Anchor ID: ','');
						if (window.sel.getRangeAt && window.sel.rangeCount) {
							range = window.sel.getRangeAt(0);
							var frag = range.createContextualFragment("<a title='#"+anchorid+"' class='internal_anchor' id='"+anchorid+"' ><i class='fa fa-anchor' aria-hidden='true'></i></a>");
							range.insertNode(frag);
						}
					}

					else if (command=='floatleft') {
						var active_image = document.querySelector('#editor_for_<?php echo $this->name;?> .rich_image_active');
						if (active_image!==null) {
							active_image.classList.add('pull-left');
							active_image.classList.remove('rich_image_active','pull-right');

							// do the same for parent figure if active_image captioned
							if (active_image.parentElement.tagName == 'FIGURE') {
								active_image.parentElement.classList.add('pull-left');
								active_image.parentElement.classList.remove('pull-right');
							}
						}
						else {
							alert('No image selected');
						}
					}

					else if (command=='edit_image_props') {
						
						var active_image = document.querySelector('#editor_for_<?php echo $this->name;?> .rich_image_active');
						
						if (active_image!==null) {
							
							// get current image props
							let alt = active_image.alt;
							let title = active_image.title;

							let uniq = ("<?php echo $this->name;?>").replace(/\s+/g, '_');		// for ids

							let iL = ["Alt Text", "Image Title"];
							let iI = [`alt_text_for_${uniq}`, `title_for_${uniq}`,];
							let cV = [alt, title];
							let hL = ["Alternative text for the visually impaired, will also display when the browser cannot render the image.",
									  "Title will appear in a tooltip on hover of the image. It can also be used to provide a broader description than the alt text."];
							
							function onAdd() {
								// set to image
								let new_alt = document.getElementById(iI[0]).value;
								let new_title = document.getElementById(iI[1]).value;
								active_image.alt = new_alt;
								active_image.title = new_title;
								active_image.classList.remove('rich_image_active');
								
								// push updated content to textarea
								active_image.closest('.control').querySelector('textarea').value = active_image.closest('.editor').innerHTML;
							}

							createModal(iL, iI, cV, hL, function(){}, onAdd, function(){});
							
						}
						else {
							alert('No image selected');
						}
					}

					else if (command=='edit_image_attribution') {
						var active_image = document.querySelector('#editor_for_<?php echo $this->name;?> .rich_image_active');
						if (active_image!==null) {

							// check data attributes if present to load into modal
							let author = active_image.dataset.author ? active_image.dataset.author : "";
							let source = active_image.dataset.source ? active_image.dataset.source : "";
							let license = active_image.dataset.license ? active_image.dataset.license : "";
							let licenselink = active_image.dataset.licenselink ? active_image.dataset.licenselink : "";

							let uniq = ("<?php echo $this->name;?>").replace(/\s+/g, '_');		// for ids

							let iL = ["Image Author", "Image Source", "License Name", "License Link"];
							let iI = [`image_author_for_${uniq}`, `image_source_for_${uniq}`, `license_name_for_${uniq}`, `license_link_for_${uniq}`];
							let cV = [author, source, license, licenselink];
							let hL = ["", "", "", "Enter full link including https://"];

							function onAdd() {
								
								// get new values for information
								let new_author = document.getElementById(iI[0]).value;
								let new_source = document.getElementById(iI[1]).value;
								let new_license = document.getElementById(iI[2]).value;
								let new_licenselink = document.getElementById(iI[3]).value;
								
								// update image dataset
								active_image.dataset.author = new_author;
								active_image.dataset.source = new_source
								active_image.dataset.license = new_license
								active_image.dataset.licenselink = new_licenselink

								// will be used to determine if there will be captions
								let new_data = [new_author, new_source, new_license, new_licenselink];

								function allEmpty(array) {
									for (const item of array) {
										if (item!="") return false;
									}
									return true;
								}

								let hasFigure = active_image.parentElement.nodeName==="FIGURE";
								let hasCaptions = !allEmpty(new_data);

								function createFigure() {
									let image = active_image;
									let figure = document.createElement('FIGURE');
									figure.classList.add('rich_image_figure');
									
									// deal with pull left and right
									if (image.classList.contains('pull-right')) { 
										figure.classList.add('pull-right');
									}
									if (image.classList.contains('pull-left')) {
										figure.classList.add('pull-left');
									}

									active_image.replaceWith(figure);
									figure.appendChild(image);
								}

								function removeFigure() {
									let figure = active_image.parentElement;
									let image = active_image;
									figure.replaceWith(image);
								}

								function addCaptions() {

									let figure = active_image.parentElement;

									function appendFigcaptionToFigure(label, content, figure) {
										let figCaption = document.createElement('FIGCAPTION');
										let uniq = label.replace(/\s+/g, '_').toLowerCase();
										let div = `<div class="image_${uniq}"><span class="attrib_label">${label}:&nbsp;</span><span class="attrib_value">${content}</span></div>`;
										figCaption.innerHTML = div; 
										console.log(figCaption);
										figure.appendChild(figCaption);
									} 

									// create only needed captions
									if (new_author) {
										appendFigcaptionToFigure(`Author`, new_author, figure);
									}
									if (new_source) {
										appendFigcaptionToFigure(`Source`, new_source, figure);
									}
									if (new_license || new_licenselink) {

										function makeLinkHTML(href, content) {
											return `<a href="${href}">${content}</a>`;
										}

										// sort out whether a link should/shouldn't be included
										let new_content = "";
										if (new_licenselink && new_license) {
											new_content = makeLinkHTML(new_licenselink, new_license);
										} 
										else if (new_licenselink && !new_license) {
											new_content = makeLinkHTML(new_licenselink, new_licenselink);
										}
										else if (!new_licenselink && new_license) {
											new_content = new_license;
										}

										appendFigcaptionToFigure(`License`, new_content, figure);
									}
								}

								// logical schema for when to add/rem figure and captions
								if (!hasFigure && hasCaptions) {
									createFigure();
									addCaptions();
								}
								else if (hasFigure && hasCaptions) {
									removeFigure();
									createFigure();
									addCaptions();
								} 
								else if (hasFigure && !hasCaptions) {
									removeFigure();
								}
								else if (!hasFigure && !hasCaptions) {
									// all is well, do nothing
								}

							}

							createModal(iL, iI, cV, hL, function(){}, onAdd, function(){});
							
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

							// do the same for parent figure if active_image captioned
							if (active_image.parentElement.tagName == 'FIGURE') {
								active_image.parentElement.classList.add('pull-right');
								active_image.parentElement.classList.remove('pull-left');
							}

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

							// do the same for parent figure if active_image captioned
							if (active_image.parentElement.tagName == 'FIGURE') {
								active_image.parentElement.classList.remove('pull-left', 'pull-right');
							}
						}
						else {
							alert('No image selected');
						}
					}
					
					else if (command=='img') {
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

					else if (command=='toggle_external_link') {
						let this_sel = document.getSelection(); 
						let this_parent = this_sel.focusNode.parentElement;
						if (this_parent && this_parent.nodeName=="A") {
							// caret inside anchor!
							if (this_parent.target=="_blank") {
								this_parent.target="_self";
							}
							else {
								this_parent.target="_blank";
							}
						}
					}

					else if (command == 'ul' || command == 'ol') {
						if (command=='ol') {
							document.execCommand('insertorderedlist', false, command);
						}
						else {
							document.execCommand('insertunorderedlist', false, command);
						}
					}
					else if (command == 'addclass') {
						
						// get parent model to potentially use later
						let parent = window.getSelection().focusNode.parentNode;

						let uniq = ("<?php echo $this->name;?>").replace(/\s+/g, '_');		// for ids

						let iL = ["Add Class"];
						let iI = [`new_class_for_${uniq}`];
						let cV = [""];
						let hL = ["Must be alphanumeric with no spaces or will not be added."];
						
						function onAdd() {
							
							// add class
							let new_class_name = document.getElementById(iI[0]).value;
							var modal = event.target.closest('.modal');
							if (new_class_name) {
								parent.classList.add(new_class_name);
							}
						}

						createModal(iL, iI, cV, hL, function(){}, onAdd, function(){});

					}

					else if (command=="removeFormat") {
						// remove all inline style from all elements and
						// then do default remove formatting
						let editor = document.querySelector('#editor_for_<?php echo $this->name;?>');
						let editor_els = editor.querySelectorAll('*');
						editor_els.forEach(el => {
							el.removeAttribute('style');
						});
						document.execCommand(command, false, null);
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
					<a class='editor_button' href="#" data-command='ul'>UL</a>
					<a class='editor_button' href="#" data-command='ol'>OL</a>
					<a class='editor_button' href="#" data-command='bold'><i class="fa fa-bold"></i></a>
					<a class='editor_button' href="#" data-command='underline'><i class="fa fa-underline"></i></a>
					<a class='editor_button' href="#" data-command='addclass'>Cls+</a>
					<a class='editor_button' href="#" data-command='img'><i class="fa fa-images"></i></a>
					<a class='editor_button' href="#" data-command='undo'><i class='fa fa-undo'></i></a>
					<a class='editor_button' href="#" data-command='createlink'><i class='fa fa-link'></i></a>
					<a class='editor_button' href="#" data-command='unlink'><i class='fa fa-unlink'></i></a>
					<a class='editor_button' href="#" data-command='createanchor'><i class='fa fa-anchor'></i></a>
					<a class='editor_button' href="#" data-command='toggle_external_link' title='Toggle external link'><i class='fa fa-external-link'></i></a>
					<a class='editor_button' href="#" data-command='justifyLeft'><i class='fa fa-align-left'></i></a>
					<a class='editor_button' href="#" data-command='superscript'><i class='fa fa-superscript'></i></a>
					<a class='editor_button' href="#" data-command='removeFormat'><i class='fa fa-broom'></i></a>
					<a class='editor_button image_selected' href='#' data-command='floatleft'>FL</a>
					<a class='editor_button image_selected' href='#' data-command='floatright'>FR</a>
					<a class='editor_button image_selected' href='#' data-command='floatclear'>FC</a>
					<a class='editor_button image_selected' href='#' data-command='edit_image_props'>IMG ALT/TITLE</a>
					<a class='editor_button image_selected' href='#' data-command='edit_image_attribution'>IMG ATTRIB</a>
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

	public function set_from_submit() {
		$value = Input::getvar($this->name, $this->filter);
		if (is_string($value)||is_numeric($value)) {
			// strip empty tags / nonsense made by ol/ul creation
			// they are illegal html ;) but they are
			// produced by UL/OL creation in contenteditable
			$value = str_replace("<p></p>","",$value);
			$value = str_replace("<p><ul>","<ul>",$value);
			$value = str_replace("</ul></p>","</ul>",$value);
			$this->default = $value;
		}
	}

	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->maxlength = $config->maxlength ?? 99999;
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