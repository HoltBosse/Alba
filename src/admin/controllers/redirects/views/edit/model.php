<?php

Use HoltBosse\Alba\Core\{CMS};
Use HoltBosse\Form\Form;
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;

$segments = CMS::Instance()->uri_segments;

$new_content = false;

if (sizeof($segments)==3 && is_numeric($segments[2])) {
	$redirect_id = $segments[2];
	$redirect = DB::fetch('SELECT * FROM redirects WHERE id=?',$redirect_id);
	
}
elseif(sizeof($segments)==3 && $segments[2]=='new') {
	$new_content = true;
}
else {
	CMS::Instance()->queue_message('Unknown content operation','danger',$_ENV["uripath"].'/admin/redirects');
	exit(0);
}

$required_details_obj = json_decode(file_get_contents(__DIR__ . '/required_fields_form.json'));

$domains = DB::fetchAll("SELECT value FROM `domains`", [], ["mode"=>PDO::FETCH_COLUMN]);
$domainOptions = [];
foreach($domains as $index=>$domain) {
	$domainOptions[] = (object) [
		"value"=>$index,
		"text"=>$domain,
	];
}

// prep forms
$required_details_form = new Form($required_details_obj);

// check if submitted or show defaults/data from db
if ($required_details_form->isSubmitted()) {

	// update forms with submitted values
	$required_details_form->setFromSubmit();

	// validate
	if ($required_details_form->validate()) {
		// forms are valid, save info
		
		$state = $required_details_form->getFieldByName('state')->default;
		$note = $required_details_form->getFieldByName('note')->default;
		$old_url = $required_details_form->getFieldByName('old_url')->default;
		$new_url = $required_details_form->getFieldByName('new_url')->default;
		$updated_by = CMS::Instance()->user->id;
		$header = $required_details_form->getFieldByName('header')->default;
		$domain = $_SESSION["current_domain"];

		if ($new_content) {
			$params = [$state,$note,$old_url,$new_url,$updated_by,$header,$domain];
			$saved = DB::exec("INSERT INTO `redirects` (`state`,note,old_url,new_url,updated_by,header,domain) VALUES (?,?,?,?,?,?,?)", $params);
		}
		else {
			$params = [$state,$note,$old_url,$new_url,$updated_by,$header,$domain,$redirect_id];
			$saved = DB::exec("UPDATE `redirects` SET `state`=?, note=?, old_url=?, new_url=?, updated_by=?, header=?, domain=? WHERE id=?", $params);
		}
	
		if ($saved) {
			if ($quicksave) {
				$redirect_to = $_SERVER['HTTP_REFERER'];
				$msg = "Quicksave successful";
			}
			else {
				$redirect_to = $_ENV["uripath"] . "/admin/redirects/";
				$msg = "Redirect saved";
			}
			CMS::Instance()->queue_message($msg, 'success', $redirect_to);
		}
		else {
			CMS::Instance()->queue_message('Invalid form','danger',$_SERVER['HTTP_REFERER']);
		}
		
	}
	else {
		CMS::Instance()->queue_message('Invalid form','danger',$_SERVER['REQUEST_URI']);	
	}
	//CMS::Instance()->queue_message('content saved','success',$_ENV["uripath"] . '/admin/content/show');
}
else {
	// set defaults if needed
	if (!$new_content) {
		$required_details_form->getFieldByName('state')->default = $redirect->state;
		$required_details_form->getFieldByName('note')->default = $redirect->note;
		$required_details_form->getFieldByName('old_url')->default = $redirect->old_url;
		$required_details_form->getFieldByName('new_url')->default = $redirect->new_url;
	}
}
