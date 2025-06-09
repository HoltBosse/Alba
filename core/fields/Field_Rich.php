<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Rich extends Field {

	public function display() {
		//$this->get_rendered_name()
		//$this->name

		?>
			<section class="editor_root_node">
				<style>
					.editor_root_node {
						--border-color: color-mix(in srgb, currentcolor 100%, black 90%);
						--background-color: var(--bulma-body-background-color);

						border-radius: 0.5rem;
						border: 1px solid var(--border-color);
						position: relative;

						.link-bubble-bar, .image-bubble-bar {
							background-color: var(--background-color);
							border-radius: 0.5rem;
							border: 1px solid var(--border-color);
							display: flex;

							& > div {
								padding: 0.5rem;
								cursor: pointer;
								font-size: 0.75rem;
								display: flex;
								align-items: center;
								gap: 0.5rem;

								&:hover {
									background-color: color-mix(in srgb, currentcolor 100%, black 50%);
									color: color-mix(in srgb, currentcolor 100%, white 90%);
								}

								&:first-of-type {
									border-top-left-radius: 0.5rem;
									border-bottom-left-radius: 0.5rem;
								}

								&:last-of-type {
									border-top-right-radius: 0.5rem;
									border-bottom-right-radius: 0.5rem;
								}


								& > * {
									pointer-events: none;
								}
							}

							hr {
								height: 2em;
								width: 1px;
								background-color: var(--border-color);
								margin: 0;
								padding: 0;
							}
						}

						.gui_editor_control_bar {
							display: flex;
							gap: 0.75rem;
							align-items: center;
							padding: 0 0.75rem;
							position: sticky;
							top: 4.5rem;
							background-color: var(--background-color);
							z-index: 1;
							border-radius: 0.5rem;

							hr {
								height: 1em;
								width: 1px;
								background-color: currentColor;
								margin: 0;
							}

							select {
								background-color: transparent;
								color: currentColor;
								border: 0px solid transparent;
								padding: 0.5rem 0.25rem;
								min-width: 8rem;

								&, &::picker(select) {
									appearance: base-select;
								}

								option {
									display: grid;
									grid-template-columns: [icon] auto [content] 1fr [mark] auto;
									color: black;
									
									&::checkmark {
										grid-area: 1 / mark;
									}
								}

								option[value="unknown"]:not(:checked) {
									display: none;
								}
							}

							.fa.fa-bold, .fa.fa-italic, .fa.fa-underline, .fa.fa-link, .fa.fa-puzzle-piece, .fa.fa-anchor {
								border-radius: 0.5rem;
								padding: 0.5rem;

								&.active {
									background-color: color-mix(in srgb, currentcolor 100%, black 90%);
									color: color-mix(in srgb, currentcolor 100%, white 90%);
								}

								&:not(.active):hover {
									background-color: color-mix(in srgb, currentcolor 100%, black 50%);
									color: color-mix(in srgb, currentcolor 100%, white 90%);
								}
							}

							.dropdown:has(input:checked) .dropdown-menu {
								display: block;

								.dropdown-trigger {
									padding: 0.5rem;

									&:hover {
										background-color: color-mix(in srgb, currentcolor 100%, black 50%);
										color: color-mix(in srgb, currentcolor 100%, white 90%);
									}
								}
							}

							.dropdown-wrapper {
								--width: 2.5rem;

								&.insert-options {
									--width: 6rem;
								}
								
								position: relative;
								min-height: 1.5rem;
								min-width: var(--width);
								border-radius: 0.5rem;
								padding: 0.5rem;

								&:hover {
									background-color: color-mix(in srgb, currentcolor 100%, black 50%);
									color: color-mix(in srgb, currentcolor 100%, white 90%);
								}

								& > * {
									position: absolute;
									top: 0rem;
									left: 0rem;
								}

								&.text-style > * {
									position: absolute;
									top: 0.25rem;
									left: 0.25rem;
								}

								div {
									display: flex;
									justify-content: space-between;
									width: var(--width);
									padding-right: 0.5rem;
									align-items: center;
								}

								&.insert-options div {
									padding-left: 0.5rem;
								}

								select {
									opacity: 0;
									min-width: var(--width);
									max-width: var(--width);
									height: 1.75rem;
									padding: 0;

									&::picker(select) {
										top: calc(anchor(bottom) + 1px);
									}

									option::checkmark {
										display: none;
									}
								}

								&.text-style select::picker(select) {
									right: anchor(right);
								}
							}

							.justifytype[disabled] {
								cursor: not-allowed;
								opacity: 0.5;
							}
						}

						.editor_seperator {
							margin-bottom: 0.5rem;
							margin-top: 0.25rem;
							background-color: var(--border-color);
							height: 1px;
							position: sticky;
							top: 7rem;
							z-index: 2;
						}

						.gui_editor {
							padding: 0 0.75rem;
							padding-bottom: 0.75rem;

							& > div {
								outline: 0px transparent;

								p.is-editor-empty:first-child::before {
									content: attr(data-placeholder);
									float: left;
									height: 0;
									pointer-events: none;
									opacity: 0.5;
								}

								/* some default css styles */
								h1 {
									font-size: 2.5rem;
								}
								h2 {
									font-size: 2.25rem;
								}
								h3 {
									font-size: 2rem;
								}
								h4 {
									font-size: 1.75rem;
								}
								h5 {
									font-size: 1.5rem;
								}
								h6 {
									font-size: 1.25rem;
								}
								ul {
									list-style: disc;
								}
								ul, ol {
									padding-left: 1.5rem;
								}
								blockquote {
									border-left: 3px solid color-mix(in srgb, currentcolor 100%, black 90%);
									padding-left: 0.5rem;
								}
								u {
									text-decoration: underline
								}
								iframe, img {
									border: 5px solid grey;
								}

								img.rich_image {
									max-width: 20rem;

									&.pull-left {
										float: left;
									}

									&.pull-right {
										float: right;
									}
								}

								a.internal_anchor {
									font-weight: 900;
									font-family: "Font Awesome 5 Free";
									-webkit-font-smoothing: antialiased;
									display: inline-block;
									font-style: normal;
									font-variant: normal;
									text-rendering: auto;
									line-height: 1;

									&:before {
										content: "\f13d";
									}
								}
							}
						}
					}
				</style>
				<div class="link-bubble-bar">
					<div>
						<i class='fa fa-external-link'></i>
						<span>Toggle Target</span>
					</div>
					<hr>
					<div>
						<i class='fa fa-unlink'></i>
						<span>Unlink</span>
					</div>
				</div>
				<div class="image-bubble-bar">
					<div>
						<i class='fa fa-align-left'></i>
						<span>Float Left</span>
					</div>
					<hr>
					<div>
						<i class='fa fa-align-center'></i>
						<span>Clear Float</span>
					</div>
					<hr>
					<div>
						<i class='fa fa-align-right'></i>
						<span>Float Right</span>
					</div>
				</div>
				<div class="gui_editor_control_bar">
					<i class="fa fa-rotate-left"></i>
					<i class="fa fa-rotate-right"></i>
					<hr>
					<select class="linetype">
						<option value="paragraph"><i class="fa fa-align-justify"></i>Paragraph</option>
						<option value="heading1"><i class="fa fa-heading"></i>Heading 1</option>
						<option value="heading2"><i class="fa fa-heading"></i>Heading 2</option>
						<option value="heading3"><i class="fa fa-heading"></i>Heading 3</option>
						<option value="heading4"><i class="fa fa-heading"></i>Heading 4</option>
						<option value="bulletlist"><i class="fa fa-list-ul"></i>Bullet List</option>
						<option value="orderedlist"><i class="fa fa-list-ol"></i>Numbered List</option>
						<option value="quote"><i class="fa fa-quote-left"></i>Quote</option>
						<option value="code"><i class="fa fa-code"></i>Code Block</option>
						<option value="unknown"><i class="fa fa-question"></i>Unknown</option>
					</select>
					<hr>
					<div style="display: flex; gap: 0.25rem; align-items: center;">
						<i class="fa fa-bold"></i>
						<i class="fa fa-italic"></i>
						<i class="fa fa-underline"></i>
						<i class='fa fa-puzzle-piece'></i>
						<i class='fa fa-link'></i>
						<i class='fa fa-anchor'></i>
						<div class="text-style dropdown-wrapper">
							<div>
								<i class="fa fa-font"></i>
								<i class="fa fa-angle-down"></i>
							</div>
							<select>
								<?php
								//FUTURE: implement these
								/*
									<option value="lowercase"><i class="fa fa-question"></i>Lowercase</option>
									<option value="uppercase"><i class="fa fa-question"></i>Uppercase</option>
									<option value="capitalize"><i class="fa fa-question"></i>Capitalize</option>
								*/
								?>
								<option value="strikethrough"><i class="fa fa-strikethrough"></i>Strikethrough</option>
								<option value="subscript"><i class="fa fa-compress"></i>Subscript</option>
								<option value="superscript"><i class="fa fa-superscript"></i>Superscript</option>
								<option value="unknown" selected style="display: none;">unknown</option>
							</select>
						</div>
						<hr>
						<div class="insert-options dropdown-wrapper ">
							<div>
								<i class="fa fa-plus"></i>
								<span>Insert</span>
								<i class="fa fa-angle-down"></i>
							</div>
							<select>
								<option value="hr"><i class="fa fa-minus"></i>Horizontal Rule</option>
								<option value="image"><i class="fa fa-image"></i>Image</option>
								<option value="youtube"><i class="fa fa-video"></i>Youtube</option>
								<!-- <option value="table"><i class="fa fa-table"></i>Table</option> -->
								<option value="unknown" selected style="display: none;">unknown</option>
							</select>
						</div>
						<hr>
					</div>
					<select class="justifytype">
						<option value="left"><i class="fa fa-align-left"></i>Left Align</option>
						<option value="center"><i class="fa fa-align-center"></i>Center Align</option>
						<option value="right"><i class="fa fa-align-right"></i>Right Align</option>
					</select>
				</div>
				<hr class="editor_seperator">
				<div class="gui_editor"></div>
				<textarea style="display: none;" id='<?php echo $this->id; ?>' <?php echo $this->get_rendered_name(); ?> data-repeatableindex="{{replace_with_index}}"><?php echo $this->default; ?></textarea>
				<script type="module">
					import { Editor } from 'https://esm.sh/@tiptap/core@2.14.0'
					import StarterKit from 'https://esm.sh/@tiptap/starter-kit@2.14.0'
					import Placeholder from 'https://esm.sh/@tiptap/extension-placeholder@2.14.0'
					import Underline from 'https://esm.sh/@tiptap/extension-underline@2.14.0'
					import Superscript from 'https://esm.sh/@tiptap/extension-superscript@2.14.0'
					import Subscript from 'https://esm.sh/@tiptap/extension-subscript@2.14.0'
					//import TextStyle from 'https://esm.sh/@tiptap/extension-text-style@2.14.0'
					import Link from 'https://esm.sh/@tiptap/extension-link@2.14.0'
					import Youtube from 'https://esm.sh/@tiptap/extension-youtube@2.14.0'
					import Image from 'https://esm.sh/@tiptap/extension-image@2.14.0'
					import {openMediaSelector} from "/core/js/media_selector.js"
					import TextAlign from 'https://esm.sh/@tiptap/extension-text-align@2.14.0'
					import Paragraph from 'https://esm.sh/@tiptap/extension-paragraph@2.14.0'
					import Heading from 'https://esm.sh/@tiptap/extension-heading@2.14.0'
					import BubbleMenu from 'https://esm.sh/@tiptap/extension-bubble-menu@2.14.0'
					
					const editorWrapperRoot = document.querySelector(`.editor_root_node:has([<?php echo $this->get_rendered_name(); ?>][data-repeatableindex="{{replace_with_index}}"])`);
					const editorElement = editorWrapperRoot.querySelector('.gui_editor');
					const editorControlBar = editorWrapperRoot.querySelector(".gui_editor_control_bar");
					const editorSubmitElement = editorWrapperRoot.querySelector(`[<?php echo $this->get_rendered_name(); ?>]`);
					const editorLinetypeSelect = editorControlBar.querySelector(".linetype");

					const classExtension = {
						addAttributes() {
							return {
								...this.parent?.(),
								class: {
									default: null,
									renderHTML: attributes => {
										return attributes.class ? { class: attributes.class } : {};
									},
								},
							}
						},
					};

					const idExtension = {
						addAttributes() {
							return {
								...this.parent?.(),
								id: {
									default: null,
									renderHTML: attributes => {
										return attributes.id ? { id: attributes.id } : {};
									},
								},
							}
						},
					};

					const editorInstance = new Editor({
						element: editorElement,
						extensions: [
							StarterKit.configure({
								paragraph: false,
								heading: false,
							}),
							Paragraph.extend(classExtension),
							Heading.extend(classExtension),
							Placeholder.configure({
								placeholder: `<?php echo $this->placeholder; ?>`,
							}),
							Underline,
							Superscript,
							Subscript,
							//TextStyle.configure({ mergeNestedSpanStyles: true }),
							Link.configure({
								openOnClick: false,
								autolink: true,
								defaultProtocol: 'https',
								protocols: ['http', 'https'],
								HTMLAttributes: {
									rel: null,
									target: null,
								},
								isAllowedUri: (url, ctx) => {
									try {
										//only possible by code
										if (url==null) {
											return true;
										}

										// construct URL
										let parsedUrl;
										if(url.startsWith("/") || url.startsWith("#")) {
											parsedUrl = new URL(`${ctx.defaultProtocol}://${window.location.hostname}${url}`);
										} else {
											parsedUrl = url.includes(':') ? new URL(url) : new URL(`${ctx.defaultProtocol}://${url}`);
										}

										// use default validation
										if (!ctx.defaultValidate(parsedUrl.href)) {
											console.log("url failed 1", url);
											return false
										}

										// disallowed protocols
										const disallowedProtocols = ['ftp', 'file', 'mailto']
										const protocol = parsedUrl.protocol.replace(':', '')

										if (disallowedProtocols.includes(protocol)) {
											console.log("url failed 2", url);
											return false
										}

										// only allow protocols specified in ctx.protocols
										const allowedProtocols = ctx.protocols.map(p => (typeof p === 'string' ? p : p.scheme))

										if (!allowedProtocols.includes(protocol)) {
											console.log("url failed 3", url);
											return false
										}

										// all checks have passed
										return true
									} catch(e) {
										console.log(e);
										console.log("url failed 4");
										return false
									}
								},
							}).extend(idExtension),
							Youtube,
							Image.extend(classExtension),
							TextAlign.configure({
								types: ['heading', 'paragraph'],
							}),
							BubbleMenu.configure({
								pluginKey: "linkBubbleBar",
								element: editorWrapperRoot.querySelector('.link-bubble-bar'),
								shouldShow: ({ editor, view, state, oldState, from, to }) => {
									return editor.isActive('link')/*  && !editor.getAttributes('link').class.contains("internal_anchor") */;
								},
							}),
							BubbleMenu.configure({
								pluginKey: "imageBubbleBar",
								element: editorWrapperRoot.querySelector('.image-bubble-bar'),
								shouldShow: ({ editor, view, state, oldState, from, to }) => {
									return editor.isActive('image');
								},
							}),
						],
						content: `<?php echo $this->default; ?>`,
					});

					console.log(editorInstance);

					//saving
					function updateEditorSave() {
						editorSubmitElement.innerHTML = editorInstance.getHTML();
					}

					editorWrapperRoot.addEventListener("keyup", (e)=>{
						updateEditorSave();
					});

					//dialog creation code

					//takes a js object and converts it to a form field
					function renderField(field) {
						let html = `<div class="field">`;
							if(field.label) {
								html+=`<label class="label">${field.label}</label>`;
							}
							switch (field.type) {
								case "input":
									html+=`<input id="${field.id}" ${field.pattern ? `pattern="${field.pattern}"` : ""} class="input" type="text" value="${field.value}">`;
									break;

								case "select":
									html+=`<div class="select"><select style="width: 100%;" id="${field.id}">`;
										field.options.forEach((option)=>{
											html+=`<option ${option.value==field.value ? "selected" : ""} value="${option.value}">${option.text}</option>`;
										});
									html+=`</select></div>`;
									break;

								default:
									html+=`<p>INVALID FIELD TYPE!!!`;
									break;
							}
							if(field.helpText) {
								html+=`<p class='help'>${field.helpText}</p>`;
							}
						html += `</div>`;

						return html;
					}

					//creates a sane modal from fields provided, returns a modal. listen to custom events modalFormAdd, and modalFormCancel
					function createModal(fields=[]) {
						// create and show modal based on desired user inputs
						let modal = document.createElement('div');
						// modal.id = "add_info_modal_for_<?php echo $this->name;?>";
						modal.classList = "modal is-active";
						let modal_html = `
							<div class="modal-background"></div>
							<div class="modal-content">
								<div class="box">
						`;
						fields.forEach((field)=>{
							modal_html+=renderField(field);
						})
						modal_html += `
									<button class="button is-primary" data-modal-action="add">Add</button>
									<button class="button is-warning" data-modal-action="cancel">Cancel</button>
									
								</div>
							</div>						
						`;

						modal.innerHTML = modal_html;
						document.body.appendChild(modal);

						// make first modal input focus
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
							}

							switch (e.target.dataset.modalAction) {
								
								case "add":
									let validity = true;
									modal.querySelectorAll("input, select").forEach((el)=>{
										if(el.validity.valid==false) {
											validity = false;
										}
									});

									if(validity==false) {
										alert("Invalid Field Entry!");
										return;
									}

									modal.dispatchEvent(new CustomEvent("modalFormAdd", { target: modal }));
									closeModal();
									break;

								case "cancel":
									modal.dispatchEvent(new CustomEvent("modalFormCancel", { target: modal }));
									closeModal();
									break;
							}

						});

						return modal;
					}

					//gui updating
					function updateLineTypeSelect() {
						/*
							tiptap isActive method is junk, half the time it lies to you
							so we use the underlying ProseMirror state api
						*/
						
						const from = editorInstance.state.selection.$from;

						switch (from.parent.type.name) {
							case "paragraph":
								if(from.node(-1).type.name=="listItem") {
									switch (from.node(-2).type.name) {
										case "bulletList":
											editorLinetypeSelect.value = "bulletlist";
											break;
										case "orderedList":
											editorLinetypeSelect.value = "orderedlist";
											break;
										default:
											console.log("issue figuring out list type in selector");
											editorLinetypeSelect.value = "unknown";
											break;
									}
								} else if(from.node(-1).type.name=="blockquote") {
									editorLinetypeSelect.value = "quote";
								} else {
									editorLinetypeSelect.value = "paragraph";
								}
								break;
							case "heading":
								switch (from.parent.attrs.level) {
									case 1:
										editorLinetypeSelect.value = "heading1";
										break;
									case 2:
										editorLinetypeSelect.value = "heading2";
										break;
									case 3:
										editorLinetypeSelect.value = "heading3";
										break;
									case 4:
										editorLinetypeSelect.value = "heading4";
										break;
									default:
										console.log("issue figuring out heading type in selector");
										editorLinetypeSelect.value = "unknown";
										break;
								}
								break;
							case "codeBlock":
								editorLinetypeSelect.value = "code";
								break;
							default:
								console.log("issue figuring out element in type selector: ", from.parent.type.name);
								editorLinetypeSelect.value = "unknown";
								break;
						}
					}

					function updateTextStylingOptions() {
						if(editorInstance.isActive('bold') && !editorControlBar.querySelector(".fa.fa-bold").classList.contains("active")) {
							editorControlBar.querySelector(".fa.fa-bold").classList.add("active");
						} else if(!editorInstance.isActive('bold') && editorControlBar.querySelector(".fa.fa-bold").classList.contains("active")) {
							editorControlBar.querySelector(".fa.fa-bold").classList.remove("active");
						}

						if(editorInstance.isActive('italic') && !editorControlBar.querySelector(".fa.fa-italic").classList.contains("active")) {
							editorControlBar.querySelector(".fa.fa-italic").classList.add("active");
						} else if(!editorInstance.isActive('italic') && editorControlBar.querySelector(".fa.fa-italic").classList.contains("active")) {
							editorControlBar.querySelector(".fa.fa-italic").classList.remove("active");
						}

						if(editorInstance.isActive('underline') && !editorControlBar.querySelector(".fa.fa-underline").classList.contains("active")) {
							editorControlBar.querySelector(".fa.fa-underline").classList.add("active");
						} else if(!editorInstance.isActive('underline') && editorControlBar.querySelector(".fa.fa-underline").classList.contains("active")) {
							editorControlBar.querySelector(".fa.fa-underline").classList.remove("active");
						}
					}

					function updatejustifytype() {
						const justifyTypeSelect = editorWrapperRoot.querySelector(".justifytype");

						const from = editorInstance.state.selection.$from;
						const nodeType = from.parent.type.name;

						if(nodeType!="paragraph" && nodeType!="heading" && !justifyTypeSelect.hasAttribute("disabled")) {
							justifyTypeSelect.setAttribute("disabled", "");
						} else if(justifyTypeSelect.hasAttribute("disabled")) {
							justifyTypeSelect.removeAttribute("disabled");
						}

						switch (from.parent.attrs.textAlign) {
							case "left":
								justifyTypeSelect.value="left";
								break;
							case "center":
								justifyTypeSelect.value="center";
								break;
							case "right":
								justifyTypeSelect.value="right";
								break;
							default:
								//console.log("unable to figure out text align, assuming left", from.parent.attrs.textAlign);
								justifyTypeSelect.value="left";
								break;
						}
					}

					function updateGuiToolBar() {
						updateLineTypeSelect();
						updateTextStylingOptions();
						updatejustifytype();
					}

					editorElement.addEventListener("keydown", (e)=>{
						updateGuiToolBar();
					});

					editorElement.addEventListener("click", (e)=>{
						updateGuiToolBar();
					});

					//buttons
					editorWrapperRoot.querySelector(".fa.fa-rotate-left").addEventListener("click", (e)=>{
						editorInstance.commands.undo();
						updateEditorSave();
					});

					editorWrapperRoot.querySelector(".fa.fa-rotate-right").addEventListener("click", (e)=>{
						editorInstance.commands.redo();
						updateEditorSave();
					});

					editorLinetypeSelect.addEventListener("change", (e)=>{
						//console.log(e.target.value);

						//editor.chain().focus().toggleHeading({ level: 1 }).run()
						switch (e.target.value) {
							case "paragraph":
								editorInstance.chain().focus().setParagraph().run();
								break;
							case "heading1":
								editorInstance.chain().focus().setHeading({ level: 1 }).run();
								break;
							case "heading2":
								editorInstance.chain().focus().setHeading({ level: 2 }).run();
								break;
							case "heading3":
								editorInstance.chain().focus().setHeading({ level: 3 }).run();
								break;
							case "heading4":
								editorInstance.chain().focus().setHeading({ level: 4 }).run();
								break;
							case "bulletlist":
								//no set method
								if(!editorInstance.isActive('bulletList')) {
									editorInstance.chain().focus().toggleBulletList().run();
								}
								break;
							case "orderedlist":
								//no set method
								if(!editorInstance.isActive('orderedList')) {
									editorInstance.chain().focus().toggleOrderedList().run();
								}
								break;
							case "quote":
								editorInstance.chain().focus().setBlockquote().run();
								break;
							case "code":
								editorInstance.chain().focus().setCodeBlock().run()
								break;
							default:
								console.log("unknown linetype selected");
						}

						updateEditorSave();
					});

					editorWrapperRoot.querySelector(".fa.fa-bold").addEventListener("click", (e)=>{
						editorInstance.chain().focus().toggleBold().run();

						updateEditorSave();
					});

					editorWrapperRoot.querySelector(".fa.fa-italic").addEventListener("click", (e)=>{
						editorInstance.chain().focus().toggleItalic().run();

						updateEditorSave();
					});

					editorWrapperRoot.querySelector(".fa.fa-underline").addEventListener("click", (e)=>{
						editorInstance.chain().focus().toggleUnderline().run();

						updateEditorSave();
					});

					editorWrapperRoot.querySelector(".fa.fa-link").addEventListener("click", (e)=>{
						const fields = [
							{
								type: "input",
								id: "a_url",
								label: "URL",
								pattern: `https?:\/\/(?:(?:[a-zA-Z\u00a1-\uffff0-9]+-?)*[a-zA-Z\u00a1-\uffff0-9]+)(?:\.(?:[a-zA-Z\u00a1-\uffff0-9]+-?)*[a-zA-Z\u00a1-\uffff0-9]+)*(?:\.(?:[a-zA-Z\u00a1-\uffff]{2,}))(?::\d{2,5})?(?:\/[^\s]*)?`,
								value: "",
							},
							{
								type: "input",
								id: "a_text",
								label: "Display Text",
								value: "",
							},
							{
								type: "input",
								id: "a_class",
								label: "Class",
								value: "",
							},
							{
								type: "select",
								id: "a_target",
								label: "Open in",
								value: "",
								options: [
									{
										value: "",
										text: "Current Window",
									},
									{
										value: "_blank",
										text: "New Window/Tab",
									}
								],
							}
						];

						const modal = createModal(fields);
						modal.addEventListener("modalFormAdd", (e)=>{
							editorInstance.chain().focus().insertContent({
								type: 'text',
								text: document.getElementById(fields[1].id).value,
								marks: [
									{
										type: 'link',
										attrs: {
											href: document.getElementById(fields[0].id).value,
											target: document.getElementById(fields[3].id).value=="_blank" ? "_blank" : null,
											class: document.getElementById(fields[2].id).value!="" ? document.getElementById(fields[2].id).value : null,
										},
									},
								],
							}).run();

							updateEditorSave();
						});
					});

					editorWrapperRoot.querySelector(".fa.fa-anchor").addEventListener("click", (e)=>{
						const fields = [
							{
								type: "input",
								id: "anchor_id",
								label: "ID",
								value: "",
							},
						];

						const modal = createModal(fields);
						modal.addEventListener("modalFormAdd", (e)=>{
							editorInstance.chain().focus().insertContent({
								type: 'text',
								text: "​", //need to make tiptap happy
								marks: [
									{
										type: 'link',
										attrs: {
											id: `${document.getElementById(fields[0].id).value}`,
											target: null,
											href: "#", //need to make tiptap happy
											class: "internal_anchor",
										},
									},
								],
							}).run();

							updateEditorSave();
						});
					});

					editorWrapperRoot.querySelector(".fa.fa-puzzle-piece").addEventListener("click", (e)=>{
						console.log("azdd clazz");

						const fields = [
							{
								type: "input",
								id: "class",
								label: "Class",
								value: "",
							},
						];

						const modal = createModal(fields);
						modal.addEventListener("modalFormAdd", (e)=>{
							console.log("form add", document.getElementById(fields[0].id).value);

							const from = editorInstance.state.selection.$from;
							console.log(from);

							const currentClasses = from.parent.attrs.class || "";
							const newClass = document.getElementById(fields[0].id).value;
							const updatedClass = currentClasses ? `${currentClasses} ${newClass}`.trim() : newClass;

							const depth = from.depth;

							// The position of the parent node
							const parentPos = from.before(depth);

							// Now you can use parentPos with setNodeMarkup
							const tr = editorInstance.state.tr.setNodeMarkup(parentPos, null, {
								...from.parent.attrs,
								class: updatedClass
							});
							editorInstance.view.dispatch(tr);

							updateEditorSave();
						});
					});

					editorWrapperRoot.querySelector(".text-style.dropdown-wrapper select").addEventListener("change", (e)=>{
						switch (e.target.value) {
							/* case "lowercase":
								console.log("unimplemented");
								break;
							case "uppercase":
								console.log("unimplemented");
								break;
							case "capitalize":
								console.log("unimplemented");
								break; */
							case "strikethrough":
								editorInstance.chain().focus().setStrike().run();
								break;
							case "subscript":
								editorInstance.chain().focus().setSubscript().run();
								break;
							case "superscript":
								editorInstance.chain().focus().setSuperscript().run();
								break;
							default:
								console.log("unknown text style chosen");
						}

						updateEditorSave();

						if(e.target.value!="unknown") {
							e.target.closest("select").value="unknown";
						}
					});

					editorWrapperRoot.querySelector(".insert-options.dropdown-wrapper select").addEventListener("change", (e)=>{
						switch (e.target.value) {
							case "hr":
								editorInstance.chain().focus().setHorizontalRule().run();
								break;
							case "image":
								// get variables for openMediaSelector()
								let elementId = "<?php echo $this->id; ?>";
								let imagesPerPage = 50;
								let mimetypes = null;
								let tags = null;
								let listingEndpoint = '<?php echo Config::uripath();?>/image/list_images';
								
								// set up rich editor variables
								let lastEditor = document.querySelector(`#editor_toolbar_for_${elementId}`);
								let selected = document.getSelection(); 
								let saved = [selected.focusNode, selected.focusOffset];

								// launch image selector
								const mediaSelector = openMediaSelector(elementId, imagesPerPage, mimetypes, tags, listingEndpoint);
								
								mediaSelector.addEventListener("mediaItemSelected", (mediaE) => {
									editorInstance.chain().focus().setImage({
										src: mediaE.detail.url + "/web",
										alt: mediaE.detail.alt,
										title: mediaE.detail.title,
										class: "rich_image",
									}).run();

									updateEditorSave();
								});


								break;
							case "youtube":
								//https://www.youtube.com/watch?v=dQw4w9WgXcQ
								const fields = [
									{
										type: "input",
										id: "url",
										label: "Video Url",
										value: "",
									},
								];

								const modal = createModal(fields);
								modal.addEventListener("modalFormAdd", (e)=>{
									//console.log("form add", document.getElementById(fields[0].id).value);

									editorInstance.commands.setYoutubeVideo({
										src: document.getElementById(fields[0].id).value,
									});

									updateEditorSave();
								});
								break;
							case "table":
								console.log("implement me");
								break;
							default:
								console.log("unknown text style chosen");
						}

						updateEditorSave();

						if(e.target.value!="unknown") {
							e.target.closest("select").value="unknown";
						}
					});

					editorWrapperRoot.querySelector(".justifytype").addEventListener("change", (e)=>{
						switch (e.target.value) {
							case "left":
								editorInstance.chain().focus().setTextAlign('left').run();
								//maybe just unset instead?
								//editorinstance.chain().focus().unsetTextAlign().run();
								break;
							case "center":
								editorInstance.chain().focus().setTextAlign('center').run();
								break;
							case "right":
								editorInstance.chain().focus().setTextAlign('right').run();
								break;
							default:
								console.log("unknown aligntype selected");
						}

						updateEditorSave();
					});

					// bubbles controls
					editorWrapperRoot.addEventListener("click", (e)=>{
						if(e.target.matches(".link-bubble-bar div:has(.fa.fa-unlink)")) {
							editorInstance.chain().focus().unsetLink().run();
						} else if(e.target.matches(".link-bubble-bar div:has(.fa.fa-external-link)")) {
							let newTabTarget = editorInstance.getAttributes('link').target=="_blank";
							editorInstance.chain().extendMarkRange("link").updateAttributes('link', { target: newTabTarget ? null : "_blank" }).run();
						} else if(e.target.matches(".image-bubble-bar div:has(.fa.fa-align-left)")) {
							let classes = editorInstance.getAttributes('image').class;
							classes = classes.replace("pull-left", "");
							classes = classes.replace("pull-right", "");
							editorInstance.chain().updateAttributes('image', { class : `${classes} pull-left`}).run();
						} else if(e.target.matches(".image-bubble-bar div:has(.fa.fa-align-right)")) {
							let classes = editorInstance.getAttributes('image').class;
							classes = classes.replace("pull-left", "");
							classes = classes.replace("pull-right", "");
							editorInstance.chain().updateAttributes('image', { class : `${classes} pull-right`}).run();
						} else if(e.target.matches(".image-bubble-bar div:has(.fa.fa-align-center)")) {
							let classes = editorInstance.getAttributes('image').class;
							classes = classes.replace("pull-left", "");
							classes = classes.replace("pull-right", "");
							editorInstance.chain().updateAttributes('image', { class : `${classes}`}).run();
						}
					});
				</script>
				<div style="clear: both;"></div>
			</section>
		<?php
	}

	public function load_from_config($config) {
		parent::load_from_config($config);

	}

	public function validate() {
		// TODO: enhance validation
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}
