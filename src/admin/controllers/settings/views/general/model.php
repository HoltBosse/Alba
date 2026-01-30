<?php

Use HoltBosse\Alba\Core\{CMS, Configuration, Mail};
Use HoltBosse\Form\{Form, Input};
Use Respect\Validation\Validator as v;

// any variables created here will be available to the view

$segments = CMS::Instance()->uri_segments;

$general_options_form = new Form(__DIR__ . "/general_options.json");
// create configuration based on form
$general_options_config = new Configuration($general_options_form); 
// load from db
$general_options_config->load_from_db();

$submitted = Input::getvar('form_general_options');

if ($submitted) {
	// update form with submitted values
	$general_options_form->setFromSubmit();
	// create new config object based on updated form and save
	$general_options_config = new Configuration($general_options_form); 
	
	$saved = $general_options_config->save();
	if ($saved) {
		CMS::Instance()->queue_message('Options saved','success',$_ENV["uripath"]."/admin/settings/general");
	}
	else {
		CMS::Instance()->queue_message('Error saving general options','danger',$_ENV["uripath"]."/admin/settings/general");
	}
}

$sendTestEmail = Input::getvar('send_test_email', v::StringVal());

if($sendTestEmail=="true") {
	$mail = new Mail();
	$mail->addAddress(CMS::Instance()->user->email);
	$mail->subject = "Test Email from " . $_ENV["sitename"];
	$mail->html = "This is a test email sent from the admin settings page of " . $_ENV["sitename"] . ". If you have received this email, your email settings are working correctly.";
	$status = $mail->send();

	CMS::Instance()->queue_message(($status ? 'Test email sent successfully, please check your inbox' : 'Failed to send test email: ' . $mail->ErrorInfo),'info',$_ENV["uripath"]."/admin/settings/general");
}
