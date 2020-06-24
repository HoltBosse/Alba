<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>
<h1 class='title is-1'>
	Home
</h1>

<section id='content' class='content'>
	<p>Welcome to <span title="Holt Bosse Content Management System"><?php echo Config::$sitename; ?></span>
	<p>Here's a quick explanation of how things are organised:</p>
	<ul>
		<li>
			<strong>Pages</strong> - this is the map of your entire site. The URL and content of each page on your site is decided here.
		</li>
		<li>
			<strong>Content</strong> - this is where you create the main content of your site. 
		</li>
		<li>
			<strong>Widgets</strong> - these are anything that needs to appear on more than one page or in a specific position within one or more pages. This includes things like menus, slideshow, contact forms etc.
		</li>
		<li>
			<strong>Tags</strong> - content or images can be tagged with tags you make here. You are in full control of which tags can be applied to which content/media types.
		</li>
		<li>
			<strong>Images</strong> - upload and organise the images used by your site.
		</li>
	</ul>

	<hr>
	
	<form method="POST" action="">
	<?php
	$test_form->display_front_end();
	?>
	<button class='submit button' type='submit'>Submit</button> 
	</form>
</section>