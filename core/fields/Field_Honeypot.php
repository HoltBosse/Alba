<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Honeypot extends Field {

	public $html;
	public $save;
	public $maxlength;
	public $autocomplete;

	public function display() {
		// autocomplete attribute set to nonsense which is calculated to be same as 'off' without being explicit
		// tabindex is required as -1 for accessibility reasons, so might tip off some bots, but can't hurt screen readers etc
		// set value to be ' ' (space) - allows us to use 'required' client-side, but whitespace might be more enticing to replace 
		// set display/style attributes via js as another layer of obfuscation
		// position: take up no space in document flow
		// clipPath: sneaky invisible method that might hide from most bots
		?>
		<input required placeholder="Important information" type='text' tabindex="-1" autocomplete="<?php echo $this->autocomplete;?>" id='<?php echo $this->id;?>' <?php echo $this->get_rendered_name();?> value=' '/>
		<script>
			let hp = document.getElementById('<?php echo $this->id;?>') ?? null;
			if (hp) {
				hp.style.position = 'absolute'; 
				hp.style.clipPath = 'circle(0)'; 
			}
		</script>
		<?php
	}

	public function load_from_config($config) {
		parent::load_from_config($config);
		
		$this->filter = $config->filter ?? 'STRING';
		$this->default = $config->default ?? $this->default;
		$this->fake_thanks_url ?? null;
		$this->autocomplete = $config->autocomplete ?? "nothingtoseehere";
	}

	public function validate() {
		// hopefully a fake thanks page has been set up to avoid tipping off bots that they have been foiled
		// if not, just show error as if form failed
		if ($this->default!==" ") {
			// our default value of space has been altered, invalid form
			if ($this->fake_thanks_url ?? null) {
				CMS::Instance()->queue_message('Form Submitted!','success',$this->fake_thanks_url);
				return false;
			}
			else {
				return false;
			}
		}
		else {
			return true;
		}
	}
}