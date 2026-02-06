<?php

	Use HoltBosse\Alba\Core\{CMS, Config, Component, Content, Page, Template};
	Use HoltBosse\DB\DB;
	Use HoltBosse\Form\{Form, Input};
	Use HoltBosse\Form\Fields\Input\Input as Field_Text;
	Use HoltBosse\Form\Fields\Select\Select as Field_Select;
	Use Respect\Validation\Validator as v;
	Use HoltBosse\Alba\Components\Admin\ControlBar\ControlBar as AdminControlBar;
	Use HoltBosse\Alba\Components\Html\Html;
	Use HoltBosse\Alba\Components\CssFile\CssFile;

	(new CssFile())->loadFromConfig((object)[
		"filePath"=>__DIR__ . "/style.css",
	])->display();

//CMS::pprint_r ($page);
?>
<h1 class='title is-1'>
	<?php if ($page->id):?>
	Edit Page &ldquo;<?php echo Input::stringHtmlSafe($page->title);?>&rdquo;
	<?php else:?>
	New Page 
	<?php endif; ?>
</h1>

<form method="POST" onSubmit="return validate_view_options();" action="<?php echo $_ENV["uripath"] . "/admin/pages/save";?>" id="page_form">
		<input name="id" type="hidden" value="<?php echo $page->id;?>"/>
		<input name="state" type="hidden" value="<?php echo $page->state;?>"/>

<div class='toggle_wrap'>		
<div class='fields-horizontal core-page-fields'>
	<?php
		//for better or for worse, currently these two fields are saved in the db htmlspecialchars encoded :/ - so we decode it here before passing to text field which re encodes

		$titleField = new Field_Text();
		$titleField->loadFromConfig((object) [
			"name"=>"title",
			"id"=>"title",
			"type"=>"Text",
			"label"=>"Title",
			"input_type"=>"text",
			"required"=>true,
			"maxlength"=>250,
			"minlength"=>0,
			"filter"=>V::StringVal()->Length(0,250),
			"placeholder"=>"Page Title",
            "icon_status"=>true,
            "icon_parent_class"=>"has-icons-left",
            "icon_markup"=> "<span class='icon is-small is-left'><i class='fas fa-heading'></i></span>",
			"default"=>htmlspecialchars_decode($page->title),
		]);
		$titleField->display();

		$aliasField = new Field_Text();
		$aliasField->loadFromConfig((object) [
			"name"=>"alias",
			"id"=>"alias",
			"type"=>"Text",
			"label"=>"URL Segment",
			"input_type"=>"text",
			"required"=>false,
			"maxlength"=>250,
			"minlength"=>0,
			"filter"=>V::StringVal()->Length(0,250),
			"placeholder"=>"URL Segment",
            "icon_status"=>true,
            "icon_parent_class"=>"has-icons-left",
            "icon_markup"=> "<span class='icon is-small is-left'><i class='fas fa-heading'></i></span>",
			"default"=>htmlspecialchars_decode($page->alias),
			"description"=>"Alphanumeric characters only, no spaces.",
		]);
		$aliasField->display();
	?>

	<?php
		$pageCache = [];
		$allPages = Page::get_all_pages_by_depth();
		$pageOptions = [];

		foreach($allPages as $pageInstance) {
			$pageCache[$pageInstance->id] = $pageInstance;

			$currentPage = $pageInstance;
			$title = $pageInstance->title;

			while($currentPage->parent!=-1) {
				$title = $pageCache[$currentPage->parent]->title . " > " . $title;
				$currentPage = $pageCache[$currentPage->parent];
			}

			$pageOptions[] = (object) [
				"text" => $title,
				"value" => $pageInstance->id,
			];
		}

		$pageOptions = array_merge([(object) ["text"=>"None", "value"=>-1]], $pageOptions);

		$parentField = new Field_Select();
		$parentField->loadFromConfig((object) [
			"name"=>"parent",
			"id"=>"parent",
			"type"=>"Select",
			"label"=>"Parent Page",
			"required"=>true,
			"filter"=>v::AlwaysValid(),
			"select_options"=>$pageOptions,
			"default"=>($page->parent ? $page->parent : -1),
			"slimselect"=>true,
		]);
		$parentField->display();
	?>

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
	<div class=''>
		<?php
			$page->page_options_form->display();
		?>
	</div>
</div>


<hr>

<div id='content_type_section' class='fields-horizontal'>
	<div class="field">
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
							$all_views = DB::fetchAll('select * from content_views where content_type_id=?', [$page->content_type]);
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
		</div>
	<?php endif; ?>
</div> <!-- content_type_section -->
<br>

<?php if ($page->content_type):?>
	<div id="content_type_controller_view_options" style="width: 100%;">
		<h6 class='heading title is-6'>VIEW OPTIONS</h6>
		<?php 
			if ($page->view>0) {
				$content_loc = Content::get_content_location($page->content_type);
				$view_loc = Content::get_view_location($page->view);
				// OLD method
				//include_once (CMSPATH . "/controllers/" . $content_loc . "/views/" . $view_loc . "/options.php");
				// NEW uses json forms
				$options_form_filepath = Content::getContentControllerPath($content_loc) . "/views/" . $view_loc . "/options_form.json";
				if(!file_exists($options_form_filepath)) {
					$template = new Template((int) $page->template_id);
					$templatepath = Template::getTemplatePath($template->folder);
					$controller_folder = DB::fetch("SELECT * FROM content_types WHERE id=?", (int) $page->content_type)->controller_location;
					$options_form_filepath = $templatepath . "/overrides/" . $controller_folder . "/" . $view_loc . "/options_form.json";
				}
				if (is_file($options_form_filepath)) {
					$options_form = new Form($options_form_filepath);
					// set options form values from json stored in view_configuration
					$options_form->deserializeJson($page->view_configuration);
					$options_form->display();
				}
				else {
					echo "<p>No options for this view.</p>";
				}
			}
			else {
				echo "<p>Choose a view first to see display options.</p>";
			}
		?>
	</div>

<?php endif; ?>
<hr>

	


	<label class="label" for="template_layout_container">Widget Assignments</label>
	<div id="template_layout_container">
		<?php include_once($layout_path); ?>
	</div>

	<?php
		$otherButton = '<button title=\'Save and keep working!\' class="button is-info" name="quicksave" value="quicksave" type="submit">Quick Save</button>';

		(new AdminControlBar())->loadFromConfig((object)[
            "middleButton"=>(new Html())->loadFromConfig((object)[
                "html"=>$otherButton,
                "wrap"=>false
            ]),
        ])->display();
	?>
	
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
		<div class='widget_title_filter_wrap field is-grouped'>
			<label class='label' for='widget_title_filter'>Search:</label>
			<input class='input' id='widget_title_filter' name='widget_title_filter'></input>
			<button onClick='document.querySelector("#widget_title_filter").value=""; update_widget_title_filter();' class='is-small button'>Clear</button>
		</div>
		<hr>
	  	<div class='widget_buttons '>
			<?php $all_published_widgets = DB::fetchAll('SELECT w.*, wt.title AS widget_type FROM widgets w, widget_types wt WHERE wt.id = w.type AND w.state >= 0');
			foreach ($all_published_widgets as $widget):?>
			<div class='widget_controls_wrap '>
				<div class='widget_title_and_type'>
					<?php echo Input::stringHtmlSafe($widget->title); ?>
					<span  class='widget_info help'><?php echo Input::stringHtmlSafe($widget->widget_type); ?></span>
				</div>
				<div class='widget_actions'>
					<button onClick="preview_widget(this); return false;" type='button' data-widgetid='<?php echo $widget->id;?>' class='button widget_preview button'>Preview</button>
					<button data-widgettitle='<?php echo $widget->title;?>' data-widgetid='<?php echo $widget->id;?>' class='button  is-primary add_widget_to_override' type='button'>Add</button>	
				</div>
			</div>
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
	window.pageid = <?php echo $page->id ?>;
	<?php echo file_get_contents(__DIR__ . "/script.js"); ?>	
</script>