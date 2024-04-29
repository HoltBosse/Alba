<?php
defined('CMSPATH') or die; // prevent unauthorized access

// SEE WIKI FOR CODE TO PROCESS UPLOADED FILES

// TODO: test in repeatable
class Field_FileUploader extends Field {

	public $select_options;
	public $mime_type;
	public $placeholder;
	public $max_size;
	public $post_max_size;
	public $multiple;
	public $input_type;
	public $max;
	public $minlength;
	public $maxlength;
	public $attribute_list;

	function __construct($default_content="") {
		$this->id = "";
		$this->name = "";
		$this->select_options=[];
		$this->mime_type=[];
		$this->default = $default_content;
		$this->content_type="";
		$this->placeholder="";

		$this->max_size = $this->get_bytes(ini_get("upload_max_filesize"));
		$this->post_max_size = $this->get_bytes(ini_get("post_max_size"));
	}

	// for converting .ini config value to bytes. see https://www.php.net/manual/en/function.ini-get.php
	public function get_bytes($val) {
		$val = strtolower($val);
		$val = trim($val);
		$last = substr($val, -1);
		if (!is_numeric($last)) {
			$val = substr($val, 0, -1);
			$val = intval($val);
			switch ($last) {	// absence of break runs code in consecutive cases
				case "g":
					$val *= 1024;
				case "m":
					$val *= 1024;
				case "k":
					$val *= 1024;
			}
		}
		return $val;
	}

	public function display() {
		$hidden = "";
		$required="";
		if ($this->required) {$required=" required ";}
		if ($this->multiple) {$multiple="multiple='true'";}
		//$name = "{$this->name}[]";		// makes name an array whether multiple=true/false, replaces get_rendered_name() function which may break repeatable form functionality
		$this->name = $this->get_rendered_name($this->multiple);
		if (!strpos($this->name, "[]")) {
			$this->name = substr_replace($this->name, "[]", -2, 0);
		}
		if (property_exists($this,'attribute_list')) {
			$attributes = explode(' ',$this->attribute_list);
			if (in_array('hidden',$attributes)) {
				$hidden = "hidden";
			}
		}
		echo "<div class='field {$required} {$hidden}'>";
			echo "<label class='label'>{$this->label}</label>";
			echo "<div class='control'>";
				
				if ($this->input_type=='date') {
					if ($this->default>0) {
						$this->default = date("Y-m-d", strtotime($this->default));
					}
					else {
						$this->default = "";
					}
				}
				$minmax="";
				if (property_exists($this,'min')) {
					$minmax=" min='{$this->min}' max='{$this->max}' ";
				}
				$placeholder = $this->placeholder ?? "";

				$accept_string = "";
				foreach($this->mime_type as $m) {
					$accept_string .= $m . ',';
				}				
				
				echo "<input type='{$this->input_type}' value='{$this->default}' accept='{$accept_string}' {$multiple} {$this->name} {$required} id='{$this->id}' >";
			echo "</div>";
			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}

		echo "</div>";
		// script for client side file validation
		?>
		<script type="text/javascript">
			
			window.onload = function() {

				// add change listener to file input field
				//let file_input = document.getElementById("<?php echo $this->id ?>");
				// handle potentially working (!) in a repeatable form
				let file_input = document.querySelector("[name='<?php echo $this->name ?>']");
				file_input.addEventListener('change', function(event) {

					function failValidation(error) {
						alert(error);			// send alert
						file_input.value='';	// clear selected file(s)
						return;					// exit checks
					}

					let files = file_input.files;

					// check to see if there are even files to perform checks on
					if (files.length > 0) {		// if there are files set to upload

						// check if single file upload and more than one file selected
						let multiple = <?php echo($this->multiple); ?>;
						if (!multiple && files.length > 1) {
							failValidation("You may only upload one file.");
						}

						// iterate checks over all files
						for (let i = 0; i < files.length; i++) {

							// check file size
							let file = files[i];
							let max_size = <?php echo $this->max_size ?>;
							if (file.size > max_size) {
								failValidation("Maximum file size exceeded for <?php echo $this->name ?> upload. Please upload a file that is smaller than " + Math.round(max_size/(1024*1024)) + " MB.");
							}

							let mime_types = <?php echo(json_encode($this->mime_type))?>;
							// check file type
							if (!mime_types.includes(file.type)) {
								failValidation("Your <?php echo $this->name ?> submission is not an allowed file type. Please try again.");
							};

							// could implement required check but browser does natively

							// here file has made it past checks
						};

						// here all files have made it past checks
					}
				});
			}

		</script>
		<?php
	}

	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->multiple = $config->multiple ?? "";
		$this->mime_type = $config->mime_type ?? [];
		if ($config->max_size < $this->max_size) { $this->max_size = $config->max_size; }	// config overwrites only values smaller than php.ini upload_max_filesize max_size set in constructor
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->maxlength = $config->maxlength ?? 999;
		$this->filter = $config->filter ?? 'RAW';
		$this->minlength = $config->minlength ?? 0;
		$this->missingconfig = $config->missingconfig ?? false;
		$this->type = $config->type ?? 'error!!!';
		$this->input_type = $config->input_type ?? 'file';
		$this->default = $config->default ?? $this->default;
		$this->attribute_list = $config->attribute_list ?? "";
		$this->placeholder = $config->placeholder ?? "";
		$this->logic = $config->logic ?? '';
	}

	public function validate() {

		// validation built to always handle array even if only one file in array
		$num_files = count($_FILES[$this->name]['name']);

		// loop over all files
		//$post_total_size = 0;
		for ($i = 0; $i < $num_files; $i++) {

			// if file was uploaded
			if (is_uploaded_file($_FILES[$this->name]['tmp_name'][$i])) {

				$file_w_path = ini_get('upload_tmp_dir') . '/' . basename($_FILES[$this->name]['tmp_name'][$i]);
				
				// validate file type
				$file_mime_type = mime_content_type($file_w_path);
				if (!in_array($file_mime_type, $this->mime_type)) {	// parse list of accepted mime_types to see if temporary file has one
					return false;	// file is not an accepted file type, fail validation
				}

				// validate file size (previous code overides max_size to php.ini upload_max_filesize)
				$file_size = filesize($file_w_path);
				if ($file_size > $this->max_size) {
					return false;	// file is larger than allowed, fail validation
				}

				// TODO: Further investigate what happens when file uploads exceed allowed size of php.ini post_max_size and build error checking
				// validate that file's combined filesize does not push post request over php.ini post_max_size
				// $post_total_size += $file_size;
				// if ($post_total_size > $this->post_max_size) {
				// 	return false;
				// }

			}
			// if file(s) were not uploaded
			else {
				
				// validate that no file(s) are required
				if ($this->required) {
					return false;		// file was required and not uploaded, fail validation
				}
			}
		}

		return true;	// no tests failed, pass validation
	}
}
