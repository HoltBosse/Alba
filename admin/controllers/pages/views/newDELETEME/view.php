<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<style>
#template_layout_container {
	margin:2rem auto;
	padding:1rem;
	border:2px solid rgba(0,0,0,0.2);
}
</style>

<h1 class='title is-1'>
	New Page
</h1>
<form method="POST" action="<?php echo Config::uripath() . "/admin/users/save";?>" id="new_user_form">
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
		<label class="label">URL Segment</label>
		<div class="control has-icons-left has-icons-right">
			<input required name="alias" class="input iss-success" type="text" placeholder="URL Segment" value="">
			<span class="icon is-small is-left">
			<i class="fas fa-signature"></i>
			</span>
			<!-- <span class="icon is-small is-right">
				<i class="fas fa-check"></i>
			</span> -->
		</div>
	<p class="help">Used as the pages identifier in the URL. If blank, it will be based on the title. Alphanumeric characters only, no spaces.</p> 
	</div>

	<div class="field">
		<label class="label">Parent</label>
		<div class="control has-icons-left has-icons-right">
			<input required name="parent" class="input iss-success" type="parent" placeholder="Parent" value="">
			<span class="icon is-small is-left">
			<i class="fas fa-project-diagram"></i>
			</span>
			<!-- <span class="icon is-small is-right">
				<i class="fas fa-check"></i>
			</span> -->
		</div>
	<!-- <p class="help is-success">This username is available</p> -->
	</div>

	<div class="field">
		<label class="label">Controller</label>
		<div class="control has-icons-left has-icons-right">
			<input required name="controller" class="input iss-success" type="text" placeholder="controller" value="">
			<span class="icon is-small is-left">
			<i class="fas fa-th-list"></i>
			</span>
			<!-- <span class="icon is-small is-right">
				<i class="fas fa-check"></i>
			</span> -->
		</div>
	<p class="help">A controller is for complex content, such as a blog, or events.</p> 
	</div>

	<div class="field">
		<label class="label">Template</label>
		<div class="control has-icons-left has-icons-right">
			<div class="select">
				<select name="template">
					<?php foreach ($all_templates as $a_template):?>
						<option <?php if ($a_template->id = $default_template) {echo "selected";}?> value="<?php echo $a_template->id;?>" ><?php echo $a_template->title;?></option>
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
	<!-- <p class="help is-success">This username is available</p> -->
	
	</div>
	<label class="label" for="template_layout_container">Widget Assignments</label>
	<div id="template_layout_container">
		<?php include_once($layout_path); ?>
	</div>

	<div class="control">
		<button type="submit" class="button is-primary">Save</button>
	</div>
	
</form>