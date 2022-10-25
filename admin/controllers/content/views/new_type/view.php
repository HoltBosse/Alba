<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<style>
#user_fields, #user_fields .field {
	margin:1rem auto;
	padding:1rem;
	border:1px dashed rgba(0,0,0,0.2);
}
</style>

<script>
	document.addEventListener("DOMContentLoaded", function(){
		document.getElementById('field_buttons').addEventListener('click',function(e){
			if (e.target.classList.contains('add_new_field')) {
				// clicked add new field button
				//alert(e.target.id)
				classname = e.target.dataset.classname;
				// classname should exist as a global window variable
				// as output by the php classes inject_designer_javascript function
				// add this template to the #user_fields div
				existing_html = document.getElementById('user_fields').innerHTML;
				new_html = existing_html += window[classname].designer_template;
				//document.getElementById('user_fields').append(window[classname].designer_template);
				document.getElementById('user_fields').innerHTML = new_html;
			}
		});
	});
</script>


<h1 class='title is-1'>New Content Type</h1>
<p class='help'>Note: This section is not yet functional in any real sense. :) All new content types must be added in the DB and /controllers folder respectively.</p>

<form method="POST" action="<?php echo Config::uripath() . "/admin/content/save_type";?>" id="new_content_type_form">
	<div class="field">
		<label class="label">Title</label>
		<div class="control has-icons-left has-icons-right">
			<input required name="title" class="input iss-success" type="text" placeholder="Page Title" value="">
			<span class="icon is-small is-left">
			<i class="fas fa-heading"></i>
			</span>
			<!-- <span class="icon is-small is-right">
				<i class="fas fa-check"></i>
			</span> -->
		</div>
	<!-- <p class="help is-success">This username is available</p> -->
	</div>

	<div class="field">
		<label class="label">Description</label>
		<div class="control has-icons-left has-icons-right">
			<textarea name="description" class="textarea iss-success" type="text" placeholder="Description"></textarea>
			
			<!-- <span class="icon is-small is-right">
				<i class="fas fa-check"></i>
			</span> -->
		</div>
	
	</div>

	<hr>

	<div id="user_fields_container">
		
		<h2 class="title header">Custom Fields</h2>
		<p class='help info'>Some fields are automatically available, such as title, author, publish dates, status etc, and as such do not need to be added below.</p>
		<div id="field_buttons">
			<label class="label">Add New Field</label>
			<div class="control has-icons-left has-icons-right">
				
				<?php foreach ($all_fields as $field_info):?>
					<?php
						// create empty object of field class (for general info) 
						$field_obj = new $field_info->classname();
						// create javascript template code for class as required
						$field_obj->inject_designer_javascript();
					?>
					<button type="button" id='add_new_<?php echo $field_info->classname;?>' class="button is-secondary add_new_field" data-classname="<?php echo $field_info->classname;?>" data-fieldid="<?php echo $field_info->id;?>" ><?php echo $field_info->title;?></button>
				<?php endforeach; ?>
			
			</div>
		</div>
	

		<div id='user_fields'>
			
		</div>
	</div>

	<div class="control">
		<button type="submit" class="button is-primary">Save</button>
	</div>

</form>