<?php

Use HoltBosse\Alba\Core\{CMS, Component};
Use HoltBosse\Form\Form;
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;
Use HoltBosse\Alba\Components\Admin\ControlBar\ControlBar as AdminControlBar;

?>

<?php if ($new_content):?>
	<h1 class='title'>New Redirect</h1>
<?php else:?>
	<h1 class='title'>Editing Redirect</h1>
<?php endif; ?>

<hr>

<form method="POST" action="" enctype="multipart/form-data">


<div class=' '>
	<div class='flex'>
		<?php $required_details_form->display(); ?>
	</div>
</div>


<hr>

<style>
div.flex {display:flex; flex-wrap:wrap;}
div.flex > * {padding-bottom:2rem;}
/* div.flex > div:first-child {padding-left:0;} */
div.flex > * {min-width:2rem;}
</style>


<?php (new AdminControlBar())->loadFromConfig((object)[])->display(); ?>


</form>

