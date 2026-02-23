<?php
namespace HoltBosse\Alba\Widgets\FormInstance;

Use HoltBosse\Alba\Core\{Widget, Form, CMS, Mail, Configuration, Page};
Use \Exception;
Use HoltBosse\DB\DB;

class FormInstance extends Widget {
	public function render() {
		$normalizedOptions = array_combine(array_column($this->options, 'name'), array_column($this->options, 'value'));
		//echo $normalizedOptions["forminstance"];

        if(!is_numeric($normalizedOptions["forminstance"])) {
            throw new \Exception("FormInstance widget requires a valid form instance ID as 'forminstance' option.");
        }

        $formDetails = DB::fetch("SELECT * FROM form_instances WHERE id=? AND state>=0", [$normalizedOptions["forminstance"]]);

        $form = new Form($_ENV["root_path_to_forms"] . "/forms/form_instance_" . $normalizedOptions["forminstance"] . ".json");

        if($form->isSubmitted()) {
            $form->setFromSubmit();

            if($form->validate()) {
                $form->saveToDB();
                
                if(!empty($formDetails->emails)) {
                    $mail = new Mail();

                    $emails = explode(",", $formDetails->emails);
                    foreach($emails as $email) {
                        $user = DB::fetch("SELECT * FROM users WHERE id=?", [trim($email)]);

                        $mail->addAddress(trim($user->email), $user->username ?? ($formDetails->title . " Recipient"));
                    }

                    $mail->subject = "New form submission: " . $formDetails->title;

                    $adminLogoId = Configuration::get_configuration_value('general_options','admin_logo');
                    $logoSrc = "https://" . $_SERVER['SERVER_NAME'] . "/image/" . $adminLogoId;

                    $mail->html = $form->createEmailHtml($logoSrc);
                    $mail->html = str_replace("Please visit admin to view the image.", "See Attachments", $mail->html);

                    foreach($form->fields as $field) {
                        if($field instanceof \HoltBosse\Alba\Fields\Image\Image) {
                            if(is_numeric($field->default) && $field->public_accessible==true) {
                                $image = new \HoltBosse\Alba\Core\Image($field->default);
                                $filePath = $_ENV["images_directory"] . "/processed/" . $image->filename;
                                $mail->addAttachment($filePath, $image->title ?: basename($image->filename));
                            }
                        }
                    }

                    $mail->send();
                }

                $redirectToPage = new Page();
                $redirectToPage->load_from_id($formDetails->submit_page);

                CMS::Instance()->queue_message('Form submitted successfully','success', $redirectToPage->get_url());
            } else {
                $badFields = $form->getFailedValidationFields();                
                $badFields = array_values(array_filter($badFields, function($field) use ($form) {
                    return $form->fields[$field]->save;
                }));

                $niceBadFields = array_map(function($field) use ($form) {
                    return $form->fields[$field]->label !='' ? $form->fields[$field]->label : $form->fields[$field]->name;
                }, $badFields);

                $niceBadFields = sizeof($niceBadFields) > 0 ? $niceBadFields : ["Unknown fields"];

                CMS::Instance()->queue_message("Errors were found in the following fields: " . implode(", ", $niceBadFields) . ". Please correct them and try again.","danger", $_SERVER["HTTP_REFERER"] ?? $_SERVER["REQUEST_URI"]);
                die; //just in case to stop further processing
            }
        } else {
            echo "<form method='post'>";
                $form->display();
                echo "<button type='submit' class='btn btn-primary button is-primary'>Submit</button>";
            echo "</form>";
        }
	}
}