<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<style>
#template_layout_container {
	margin:2rem auto;
	padding:0.5rem;
	border:2px solid rgba(0,0,0,0.2);
	max-width:65rem;
	box-shadow: 0 0 2vw rgba(0,0,0,0.1);
}
#content_type_wrap {
	margin:0rem;
	padding:0rem;
	border:1px dashed rgba(0,0,0,0.0);
	max-height:0;
	overflow: hidden;
	transition:all 1s ease;
	opacity:0;
	display: flex;
    /* justify-content: space-around; */
}
#content_type_controller_views {
	padding-right:1rem;
	margin-right:1rem;
	border-right:1px dashed rgba(0,0,0,0.2);
}
#content_type_wrap.active {
	margin:1rem;
	padding:1rem;
	border:1px dashed rgba(0,0,0,0.2);
	max-height:100vh;
	opacity:1;
}

.fields-horizontal {
	display:flex;
}
.fields-horizontal > * {
	padding-left:1em;
	flex-grow: 1;
  flex-shrink: 1;
  flex-basis: 0;
}
.fields-horizontal > *:first-child {
	padding-left:0;
}
.width33 {
	width:33%;
	flex-grow: 0.33;
}
.lighter_note {
	font-size:80%;
	opacity:0.6;
	padding-left:1rem;
}
span.position_name {
	font-weight:bold;
}
span.position_name_label {
	display:none;
}
div.position_tag_wrap {
	display:none;
}
div.position_tag_wrap.active {
	display:block;
}
.template_layout_widget_wrap.active > .addoverride {
	display:none;
}
.template_layout_widget_wrap > .removeoverride {
	display:none;
}
.template_layout_widget_wrap.active > .removeoverride {
	display:inline-block;
}
.template_layout_widget_wrap > .addoverride {
	display:inline-block;
}
.tags span.droppable {
	display:inline-block;
}
.tags .draggable_widget:hover {
	cursor:move;
}

/* #content_type_controller_views {
	margin:0rem;
	padding:0rem;
	border:1px dashed rgba(0,0,0,0.0);
	max-height:0;
	overflow: hidden;
	transition:all 1s ease;
	opacity:0;
}
#content_type_controller_views.active {
	margin:1rem;
	padding:1rem;
	border:1px dashed rgba(0,0,0,0.2);
	max-height:100vh;
	opacity:1;
} */

</style>

<?php
//CMS::pprint_r ($page);
?>
<h1 class='title is-1'>
	<?php if ($page->id):?>
	Edit Page &ldquo;<?php echo $page->title;?>&rdquo;
	<?php else:?>
	New Page 
	<?php endif; ?>
</h1>

<form method="POST" onSubmit="return validate_view_options();" action="<?php echo Config::$uripath . "/admin/pages/save";?>" id="page_form">
		<input name="id" type="hidden" value="<?php echo $page->id;?>"/>

<a href='#' class='toggle_siblings'>show/hide required fields</a>
<div class='toggle_wrap <?php if ($page->id) { echo " hidden ";}?>'>		
<div class='fields-horizontal'>
	<div class="field ">
		<label class="label">Title</label>
		<div class="control has-icons-left has-icons-right">
			<input required name="title" class="input iss-success" type="text" placeholder="Page Title" value="<?php echo $page->title;?>">
			<span class="icon is-small is-left">
			<i class="fas fa-heading"></i>
			</span>
			<!-- <span class="icon is-small is-right">
				<i class="fas fa-check"></i>
			</span> -->
		</div>
	<!-- <p class="help is-success">This username is available</p> -->
	</div>

	<div class="field ">
		<label class="label">URL Segment</label>
		<div class="control has-icons-left has-icons-right">
			<input name="alias" class="input iss-success" type="text" placeholder="URL Segment" value="<?php echo $page->alias;?>">
			<span class="icon is-small is-left">
			<i class="fas fa-signature"></i>
			</span>
			<!-- <span class="icon is-small is-right">
				<i class="fas fa-check"></i>
			</span> -->
		</div>
	<p class="help">Alphanumeric characters only, no spaces.</p> 
	</div>


	<div class="field">
		<label class="label">Parent Page</label>
		<div class="control has-icons-left has-icons-right">
			<div class="select">
				<select id="parent" name="parent">
					<option value="-1">None</option>
					<?php $all_pages = Page::get_all_pages();?>
					<?php foreach ($all_pages as $a_page):?>
						<?php
							// skip if self!
							if ($a_page->id==$page->id) {continue;}
						?>
						<option 
							<?php if ($page->parent == $a_page->id) { echo " selected ";}?>
							value="<?php echo $a_page->id;?>" >
								<?php $depth = Page:: get_page_depth($a_page->id); for ($n=0; $n<$depth; $n++) { echo " - ";}?>
								<?php echo $a_page->title;?>
						</option>
					<?php endforeach; ?>
				</select>
				<span class="icon is-small is-left">
					<i class="fas fa-project-diagram"></i>
				</span>
			</div>
		</div>
	<!-- <p class="help is-success">This username is available</p> -->
	</div>

	<div class="field">
		<label class="label">Template</label>
		<div class="control has-icons-left has-icons-right">
			<div class="select">
				<select name="template">
					<option <?php if ($page->template_id==0) echo "selected"; ?> value='0'>Default (<?php echo $default_template->title; ?>)</option>
					<?php foreach ($all_templates as $a_template):?>
						<option <?php if ($a_template->id == $page->template_id) {echo "selected";}?> value="<?php echo $a_template->id;?>" ><?php echo $a_template->title;?></option>
					<?php endforeach; ?>
				</select>
				<span class="icon is-small is-left">
					<i class="fas fa-object-group"></i>
				</span>
				<!-- <span class="icon is-small is-right">
					<i class="fas fa-check"></i>
				</span> -->
			</div>
		</div>
	</div>

</div> <!-- end div fields-horizontal -->


</div> <!-- end toggle wrap -->

<div id='page_seo_og_options'>
	<hr>
	<?php //CMS::pprint_r ($page); ?>
	<h4 class='title is-4 is-heading'>SEO / Social</h4>
	<p>If the page content is a collection or has no SEO/Opengraph entries of its own, then these values will be used. It's best to set them here just to be safe.</p>
	<br>
	<div class='fields-horizontal'>
		<?php $page->page_options_form->display_front_end(); ?>
	</div>
</div>


<hr>

<div id='content_type_section' class='fields-horizontal'>
	<div class="field  width33">
		<label class="label">Main Content</label>
		<div class="control has-icons-left has-icons-right">
		 
		 	<div class="select">
				<select id="content_type" name="content_type">
					<option value="-1">None</option>
					<?php foreach ($all_content_types as $a_content_type):?>
						
						<option 
							<?php if ($page->content_type == $a_content_type->id) { echo " selected ";}?>
							data-controller_location="<?php echo $a_content_type->controller_location;?>" 
							value="<?php echo $a_content_type->id;?>" >
								<?php echo $a_content_type->title;?>
						</option>
					<?php endforeach; ?>
				</select>
				<span class="icon is-small is-left">
					<i class="fas fa-object-group"></i>
				</span>
				<!-- <span class="icon is-small is-right">
					<i class="fas fa-check"></i>
				</span> -->
			</div>
		</div>
	<p class="help">Choose main content and presentation options.<br>Leaving this blank is fine, but all visible content on this page will just be widgets!</p> 
	</div>

	<?php if ($page->content_type):?>

		<div id="content_type_wrap" class="<?php echo $page->content_type . " "; if ($page->content_type>0) {echo " active ";}?>">
			
			<div id="content_type_controller_views">
				<h6 class='heading title is-6'>CHOOSE VIEW</h6>
				<div class='control'>
					<div class='select'>
						<select  id='content_type_controller_view' name='content_type_controller_view'>
							<option value=''>Choose View:</option>
							<?php
							$all_views = CMS::Instance()->pdo->query('select * from content_views where content_type_id=' . $page->content_type)->fetchAll();
							foreach ($all_views as $view) {
								$view_selected = "";
								if ($page->view==$view->id) {
									$view_selected = ' selected ';
								}
								echo "<option {$view_selected} value='".$view->id."' data-view_location=" . $view->location . " data-content_type_id=" . $view->content_type_id . ">" . $view->title . "</option>";
							}
							?>
						</select>
					</div>
				</div>
			</div>

			<div id="content_type_controller_view_options">
				<h6 class='heading title is-6'>VIEW OPTIONS</h6>
				<?php 
					if ($page->view>0) {
						$content_loc = Content::get_content_location($page->content_type);
						$view_loc = Content::get_view_location($page->view);
						// OLD method
						//include_once (CMSPATH . "/controllers/" . $content_loc . "/views/" . $view_loc . "/options.php");
						// NEW uses json forms
						$options_form = new Form(CMSPATH . "/controllers/" . $content_loc . "/views/" . $view_loc . "/options_form.json");
						// set options form values from json stored in view_configuration
						$options_form->deserialize_json($page->view_configuration);
						$options_form->display_front_end();
					}
					else {
						echo "<p>Choose a view first to see display options.</p>";
					}
				?>
			</div>

			

		</div>

	<?php endif; ?>

</div> <!-- content_type_section -->
	<hr>

	


	<label class="label" for="template_layout_container">Widget Assignments</label>
	<div id="template_layout_container">
		<?php include_once($layout_path); ?>
	</div>

	<div class='fixed-control-bar'>
		<button class='button is-primary' type='submit'>Save</button>
		<button class='button is-warning' type='button' onclick="window.history.back();">Cancel</button>
	</div>
	
</form>

<div id='widget_selector_modal' class="modal">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">Choose Widget To Add</p>
      <button class="delete" aria-label="close"></button>
    </header>
    <section class="modal-card-body">
      <!-- Content ... -->
	  	<div class=''>
			<?php $all_published_widgets = CMS::Instance()->pdo->query('select * from widgets where state>=0')->fetchAll();
			foreach ($all_published_widgets as $widget):?>
				<button data-widgettitle='<?php echo $widget->title;?>' data-widgetid='<?php echo $widget->id;?>' class='button add_widget_to_override is-outline' type='button'><?php echo $widget->title; ?></button>
			<?php endforeach; ?>
		</div>
    </section>
    <!-- <footer class="modal-card-foot">
      <button class="button is-success">Save changes</button>
      <button class="button">Cancel</button>
    </footer> -->
  </div>
</div>

<script>

	function validate_view_options() {
		view_options = document.getElementById('content_type_controller_view_options');
		return true;
	}


	content_type = document.getElementById("content_type");
	content_type_controller_view = document.getElementById('content_type_controller_view');
	//window.content_type_id = content_type.value;
	//window.loaded_content_type_id = content_type.value;
	//content_type_wrap = document.getElementById("content_type_wrap");
	

	// switch views based on content type
	content_type.addEventListener('change',function(e){
		content_type_value = e.target.value;
		if (content_type_value) {
			window.new_url = "<?php echo Config::$uripath;?>/admin/pages/edit/<?php echo $page->id;?>/" + content_type_value + '/-1';
			serialize_form('page_form'); // save form to localstorage so user doesn't have to retype any main fields
			window.location = window.new_url;
			//alert(window.new_url);
		}
	});

	// switch options based on view choice if available
	if (content_type_controller_view) {
		content_type_controller_view.addEventListener('change',function(e){
			view_value = e.target.value;
			if (view_value) {
				window.new_url = "<?php echo Config::$uripath;?>/admin/pages/edit/<?php echo $page->id;?>/" + content_type.value + '/' + view_value;
				serialize_form('page_form'); // save form to localstorage so user doesn't have to retype any main fields
				window.location = window.new_url;
			}
		});
	}


	// TODO - fix multiselects for localstorage

	function unserialize_form(id) {
		var form_json = window.localStorage.getItem(id);
		if (!form_json) {
			console.log('No saved details from change of content_type / view');
			return false;
		}
		var form = document.getElementById(id);
		if (!form) {
			return false;
		} 
		form_data = JSON.parse(form_json);
		form_data.forEach(form_item => {
			console.log('Looking for form element with name: ', form_item.field_name);
			matching_form_element = document.querySelector('[name="' + form_item.field_name + '"]');
			if (matching_form_element) {
				console.log('Inserting stored item: ', form_item);
				matching_form_element.value = form_item.field_value;
			}
			else {
				console.log('Error deserializing form. No element with name matching: ',form_item.field_name);
			}
		});
		window.localStorage.removeItem(id);
	}

	function serialize_form(id) {
		var form = document.getElementById(id);
		if (!form) {
			return false;
		}
		// Setup our serialized data
		var serialized = [];
		// Loop through each field in the form
		for (var i = 0; i < form.elements.length; i++) {
			var field = form.elements[i];
			// Don't serialize fields without a name, submits, buttons, file and reset inputs, and disabled fields
			if (!field.name || field.disabled || field.type === 'file' || field.type === 'reset' || field.type === 'submit' || field.type === 'button') continue;
			// If a multi-select, get all selections
			if (field.type === 'select-multiple') {
				for (var n = 0; n < field.options.length; n++) {
					if (!field.options[n].selected) continue;
					serialized.push({"field_name":field.name,"field_value":field.options[n].value});
				}
			}
			// Convert field data to a query string
			else if ((field.type !== 'checkbox' && field.type !== 'radio') || field.checked) {
				serialized.push({"field_name":field.name,"field_value":field.value});
			}
		}
		serialized_json = JSON.stringify(serialized);
		window.localStorage.setItem(id,serialized_json);
	}

	// restore required fields from localstorage if available
	unserialize_form('page_form');

	// widget override modal

	var add_widget_override_buttons = document.querySelectorAll('.add_override_widget');
	add_widget_override_buttons.forEach(btn => {
		btn.addEventListener('click',function(e){
			// set currently working on tag wrap
			window.cur_position_tag_wrap = e.target.closest('.position_tag_wrap');
			// show modal
			var modal=document.getElementById('widget_selector_modal');
			modal.classList.add('is-active');
		});
	}); 

	document.querySelector('.modal .delete').addEventListener('click',function(e){
		e.preventDefault();
		e.stopPropagation();
		var modal=document.getElementById('widget_selector_modal');
		modal.classList.remove('is-active');
	});

	function update_all_position_widgets_inputs() {
		// update every array position_widgets_input value in every position
		// with id list based on tags inside
		// MUST be done at least before submitting form
		var all_position_widgets_inputs = document.querySelectorAll('.position_widgets_input');
		all_position_widgets_inputs.forEach(w_list_input => {
			var widgets_in_position = w_list_input.parentNode.querySelectorAll('.draggable_widget');
			var widget_array = [];
			widgets_in_position.forEach(w => {
				var widget_id = w.dataset.tagid;
				widget_array.push(widget_id);
			});
			var widget_array_string = widget_array.toString();
			w_list_input.value = widget_array_string;
		});
	}

	function add_widget_to_override_list (widget_id, widget_title) {
		// already know last known widget list 
		// via window.cur_position_tag_wrap - set on 'add widget' button press
		var markup = `
			<span data-tagid='${widget_id}' draggable='true' ondragover='dragover_tag_handler(event)' ondragend='dragend_tag_handler(event)' ondragstart='dragstart_tag_handler(event)' class='draggable_widget  is-warning tag'>${widget_title}<span class='delete is-delete'>X</span></span>	
		`;
		//console.log(window.cur_position_tag_wrap);
		window.cur_position_tag_wrap.querySelector('.tags').innerHTML+=markup;
		// update input csv position_widgets_input with new id
		update_all_position_widgets_inputs();
	}

	// handle widget button modal click
	document.querySelector('.modal').addEventListener('click',function(e){
		if (e.target.classList.contains('add_widget_to_override')) {
			// get id and add to list
			var widget_id = e.target.dataset.widgetid;
			var widget_title = e.target.dataset.widgettitle;
			add_widget_to_override_list (widget_id, widget_title);
			// remove modal
			//console.log('adding widget ', widget_id);
			e.target.closest('.modal').classList.remove('is-active');
		}
	});
	
	// drag drop tags in template overrides

	window.tagdrag = null;

	function dragover_tag_handler(e) {
		// get nearest tag because drop target is any element inside our droppable element not just parent droppable itself
		var nearest_tag = e.target.closest('.tag');
		if ( isBefore( tagdrag, nearest_tag ) ) nearest_tag.parentNode.insertBefore( tagdrag, nearest_tag )
  		else nearest_tag.parentNode.insertBefore( tagdrag, nearest_tag.nextSibling )
		update_all_position_widgets_inputs();
	}

	function dragstart_tag_handler(e) {
		e.dataTransfer.setData("text/plain", e.target.dataset.tagid);
		e.dataTransfer.effectAllowed = "move"
 		e.dataTransfer.dropEffect = "move";
		window.tagdrag = e.target;
	}

	function dragend_tag_handler(e) {
		window.tagdrag = null;
	}

	function isBefore( el1, el2 ) {
		var cur;
		if (el1===el2) {
			//console.log('self');
			return false;
		}
		if ( el2.parentNode === el1.parentNode ) {
			for ( cur = el1.previousSibling; cur; cur = cur.previousSibling ) {
				if (cur === el2) return true
			}
		} 
		else {
			console.warn('isBefore failed - elements not siblings');
			return false;
		}
		return false;
	}

	// handle override / remove override clicks
	function toggle_override(e) {
		var override_panels = e.target.parentNode.querySelectorAll('.position_tag_wrap');
		override_panels.forEach(panel => {
			panel.classList.toggle('active');
		});
		e.target.closest('.template_layout_widget_wrap').classList.toggle('active');
	}

	// handle other clicks such as tag delete, override toggle etc
	document.getElementById('template_layout_container').addEventListener('click',function(e){
		if (e.target.classList.contains('addoverride')) {
			toggle_override(e);
		}
		if (e.target.classList.contains('removeoverride')) {
			if (confirm('Are you sure? This will clear ALL overrides for this page/position once saved.')) {
				toggle_override(e);
				var override_tags = e.target.parentNode.querySelectorAll('.override_tags_wrap .tag');
				override_tags.forEach(t => {
					t.remove();
				});
				update_all_position_widgets_inputs();
			}
		}
		if (e.target.classList.contains('delete')) {
			e.target.closest('.tag').remove();
			update_all_position_widgets_inputs();
		}
	});
	
</script>