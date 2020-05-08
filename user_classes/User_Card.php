<?php
defined('CMSPATH') or die; // prevent unauthorized access

// user space class for Card stuff

class User_Card {
	function __construct($content_item) {
        $this->content_item = $content_item;
    }

	function render () {
		CMS::pprint_r ($this);
		?>
			<a href='<?php echo Config::$uripath . "/projects/" . $project->id;?>'>
				<div class='project_wrap <?php if ($project->f_description==true) {echo "wider";}?>'>
					<?php if (property_exists($project,'client_title')):?>
						<h2 class='project-title'><?php echo $project->client_title . " - " . $project->title; ?></h2>
					<?php else: ?>
						<h2 class='project-title'><?php echo $project->title; ?></h2>
					<?php endif; ?>
					<div class='description_wrap'>
						<?php if (property_exists($project,'f_description')):?>
							<p><?php echo $project->f_description;?></p>
						<?php endif; ?>
					</div>
				</div>
			</a>
		<?php
	}

	

	
}