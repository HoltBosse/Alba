<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_parsedown extends Field {

	public function display() {
		$wrapper_id = uniqid();

		?>
			<style>
				/* wrapper */
				.pd_wrapper {
					box-shadow: inset 0 0.0625em 0.125em rgb(10 10 10 / 5%);
					max-width: 100%;
					width: 100%;
					background-color: #fff;
					border-color: #dbdbdb;
					border-radius: 4px;
					color: #363636;
					-webkit-appearance: none;
					align-items: center;
					border: 1px solid;
					/* display: inline-flex; */
					font-size: 1rem;
					/* height: 2.5em; */
					/* justify-content: flex-start; */
					line-height: 1.5;
					padding-bottom: calc(0.5em - 1px);
					/* padding-left: calc(0.75em - 1px);
					padding-right: calc(0.75em - 1px); */
					padding-top: calc(0.5em - 1px);
					/* position: relative;
					vertical-align: top; */
				}

				/* tab header stuff */
				.pd_tab_header_row {
					display: flex;
					gap: 1rem;
					padding-left: calc(0.75em - 1px);
					padding-right: calc(0.75em - 1px);
					/* padding-bottom: calc(0.5em - 1px); */
					border-bottom: 1px solid;
				}

				.pd_tab_title {
					padding-top: calc(0.5em - 1px);
					padding-left: calc(0.75em - 1px);
					padding-right: calc(0.75em - 1px);
					padding-bottom: calc(0.5em - 1px);
					cursor: pointer;
				}

				.pd_tab_title.active {
					border-left: 1px solid;
					border-right: 1px solid;
					border-top: 1px solid;
					box-shadow: 0 4px 2px -2px #121212;
					border-top-left-radius: 4px;
					border-top-right-radius: 4px;
				}

				.pd_text_controls {
					margin-left: auto;
					display: flex;
					gap: 0.25rem;
				}

				.pd_text_option {
					padding-top: calc(0.5em - 1px);
					padding-left: calc(0.75em - 1px);
					padding-right: calc(0.75em - 1px);
					padding-bottom: calc(0.5em - 1px);
					cursor: pointer;
				}

				/* tab content stuff */
				.pd_tab_content {
					display: none;
					padding-top: calc(0.5em - 1px);
					padding-left: calc(0.75em - 1px);
					padding-right: calc(0.75em - 1px);
				}

				.pd_tab_content.active {
					display: block;
				}

				.pd_tab_content textarea.input {
					min-height: 2.5rem;
					height: 10rem;
					min-width: 100%;
				}

				.preview_content {
					margin-left: 1rem; /* fxes bulma removing space for lists and stuff */
				}

				.preview_content ul {
					list-style: disc; /* actually want bulma to not clean slate things for once */
				}
			</style>
			<section class="pd_wrapper" id="<?php echo $wrapper_id; ?>">
				<div class="pd_tab_header_row">
					<p class="pd_tab_title active" data-tab_content="write_content">Write</p>
					<p class="pd_tab_title" data-tab_content="preview_content">Preview</p>
					<div class="pd_text_controls">
						<i class="pd_text_option fas fa-heading"></i>
						<i class="pd_text_option fas fa-bold"></i>
						<i class="pd_text_option fas fa-italic"></i>
						<i class="pd_text_option fas fa-quote-right"></i> <?php //fa-block-quote ?>
						<i class="pd_text_option fas fa-code"></i>
						<i class="pd_text_option fas fa-link"></i>
						<i class="pd_text_option fas fa-list"></i>
						<i class="pd_text_option fas fa-list"></i> <?php //fa-list-ol - find better option ?>
					</div>
				</div>
				<div class="pd_content_header_row">
					<div class="pd_tab_content write_content active">
						<textarea class="input pd_parsedown_content"></textarea>
					</div>
					<div class="pd_tab_content preview_content">
						<p>some content here</p>
					</div>
				</div>
			</section>
			<script>
				let editor = document.getElementById("<?php echo $wrapper_id; ?>");
				editor.addEventListener("click", (e)=>{
					if(e.target.classList.contains("pd_tab_title") && !e.target.classList.contains("active")) {
						editor.querySelector(".pd_tab_header_row").querySelector(".active").classList.remove("active");
						editor.querySelector(".pd_content_header_row").querySelector(".active").classList.remove("active");

						e.target.classList.add("active");
						editor.querySelector("." + e.target.dataset.tab_content).classList.add("active");

						if(e.target.dataset.tab_content == "preview_content") {
							/* todo: add uripath */
							fetch("/api/parsedown?markup=" + encodeURIComponent(editor.querySelector(".pd_parsedown_content").value)).then((response) => response.json()).then((data) => {
								console.log(data);
								editor.querySelector(".preview_content").innerHTML = decodeURIComponent(data.data.html.replace(/\+/g, ' '));
							});
						}
					}
					if(e.target.classList.contains("pd_text_option")) {
						if(window.getSelection().toString() != "") {
							console.log("selected text");
							/* check that its in the right spot */
						} else {
							console.log("non selected text");
						}
					}
				});
			</script>
		<?php
	}



	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->maxlength = $config->maxlength ?? 99999;
		$this->filter = $config->filter ?? 'RAW';
		$this->minlength = $config->minlength ?? 0;
		$this->missingconfig = $config->missingconfig ?? false;
		$this->type = $config->type ?? 'error!!!';
		$this->default = $config->default ?? '<p></p>';
	}

	public function validate() {
		// not a real field, just displays stuff :)
		return true;
	}
}