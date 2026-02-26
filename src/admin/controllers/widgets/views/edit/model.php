<?php

Use HoltBosse\Alba\Core\{CMS, Component, Hook, Page, Widget};
Use HoltBosse\Form\{Form, Input};
Use HoltBosse\DB\DB;


$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)==3 && is_numeric($segments[2])) {
	// edit existing widget id is segment 2
	$widget_id = (int) $segments[2];

	$widgetExists = DB::fetch('SELECT * FROM widgets WHERE id=?', $widget_id);
	if(!$widgetExists) {
		CMS::Instance()->queue_message('Failed to load widget id: ' . $widget_id, 'danger',$_ENV["uripath"].'/admin/widgets/show');
	}

	// create temp base class widget to get type
	$temp_widget = new Widget();
	$temp_widget->load($widget_id);
	$type_info = Widget::get_widget_type($temp_widget->type_id);
	// create actual new widget of class determined by type
	$widget_class_name = Widget::getWidgetClass($type_info->location);
	$widget = new $widget_class_name();
	$widget->load($widget_id);
	$new_widget = false;
}
elseif(sizeof($segments)==4 && $segments[2]=='new' && is_numeric($segments[3])) {
	// get widget type and create object of correct class
	$type_info = Widget::get_widget_type((int) $segments[3]);
	$widget_class_name = Widget::getWidgetClass($type_info->location);
	$widget = new $widget_class_name();
	$new_widget = true;
	$widget->load(-1, $segments[3]);
}
else {
	CMS::Instance()->queue_message('Unknown widget operation','danger',$_ENV["uripath"].'/admin/widgets/show');
	exit(0);
}

if(!Widget::isAccessibleOnDomain($widget->type_id, $_SESSION["current_domain"])) {
	CMS::raise_404();
}

// prep forms
$required_details_form = new Form(__DIR__ . '/required_fields_form.json');
$position_options_form = new Form(__DIR__ . '/position_options_form.json');
$position_options_form->fields["position_pages"]->domain = $widget->domain;
$widget_options_form = new Form($widget->form_data);

// old way - TODO: delete?
//$required_details_form->load_json(CMSPATH . '/widgets/required_fields_form.json');
//$widget_options_form->load_json(CMSPATH . '/widgets/' . $widget->type->location . '/widget_config.json');

// check if submitted or show defaults/data from db
if ($required_details_form->isSubmitted()) {

	// update forms with submitted values
	$required_details_form->setFromSubmit();
	$widget_options_form->setFromSubmit();
	$position_options_form->setFromSubmit();

	$pageSelectorType = $position_options_form->getFieldByName("position_control")->default;
	$pageFilters = $position_options_form->getFieldByName("position_pages")->default;
	$templatePosition = $position_options_form->getFieldByName("global_position")->default;

	//assume 0 by default
	$pagesToCheck = $pageFilters;
	if($pageSelectorType==1) {
		$allPages = array_column(Page::get_all_pages(), "id");

		$pagesToCheck = array_filter($allPages, function($input) use ($pageFilters) {
			if(in_array($input, $pageFilters)) {
				return false;
			}

			return true;
		});
	} elseif($pageSelectorType!=0) {
		//2 or any other future option, do nothing
		$pagesToCheck = [];
	}

	//we can get null back....
	$pagesToCheck = array_filter($pagesToCheck, function($input) {
		if(!is_numeric($input)) {
			return false;
		}

		return true;
	});

	$pagesToCheck = array_values($pagesToCheck); //reindex

	if(sizeof($pagesToCheck)>0) {
		$pagesToCheckSqlString = implode(",", $pagesToCheck);

		$params = [];
		$widgetOverrideCheck = "";
		if(is_numeric($widget->id)) {
			$widgetOverrideCheck = " AND NOT FIND_IN_SET(?, widgets)";
			$params[] = $widget->id;
		}

		$hasOverrides = DB::fetchAll(
			"SELECT *
			FROM page_widget_overrides
			WHERE page_id IN ($pagesToCheckSqlString)
			AND widgets IS NOT NULL
			AND widgets !=''
			$widgetOverrideCheck",
			$params
		);

		if(sizeof($hasOverrides)>0) {
			CMS::Instance()->queue_message('One or more of the selected pages and position has widget overrides, not adding on those page(s)!','warning',);	
		}
	}
	
	// validate
	if ($required_details_form->validate() && $widget_options_form->validate() && $position_options_form->validate()) {
		// forms are valid, save info
		$widget->save($required_details_form, $widget_options_form, $position_options_form);
	}
	else {
		CMS::Instance()->queue_message('Error saving widget','danger',$_SERVER['REQUEST_URI']);	
	}
	//CMS::Instance()->queue_message('Widget saved','success',$_ENV["uripath"] . '/admin/widgets/show');
}
else {
	// set defaults if needed
	if (!$new_widget) {
		$required_details_form->getFieldByName('state')->default = $widget->state;
		$required_details_form->getFieldByName('title')->default = $widget->title;
		$required_details_form->getFieldByName('note')->default = $widget->note;
		// set options from json in db
		
		if(!$widget->hasCustomBackend()) {
			foreach ($widget->options as $option) {
				//echo "$key => $value\n";
				if ($widget_options_form->fieldExists($option->name)) {
					$widget_options_form->getFieldByName($option->name)->default = $option->value;
				} else {
					// do nothing, leave default from form json
				}
			}
		}
		// set position defaults
		$position_options_form->getFieldByName('position_control')->default = $widget->position_control;
		$position_options_form->getFieldByName('global_position')->default = $widget->global_position;

		$position_page_field_default = $widget->page_list;
		if (!is_array($widget->page_list)) {
			$position_page_field_default = explode(',',$widget->page_list);
		}
		$position_options_form->getFieldByName('position_pages')->default = $position_page_field_default;
	}
	
}
