<h1 class='title is-1'>
	Home
</h1>

<style>
	#content.content > ul > li > strong > a {
		color: unset;
		text-decoration: underline;
	}
</style>
<section id='content' class='content'>
	<p>Welcome to <?php echo $_ENV["sitename"]; ?></p>
	<p>Here's a quick explanation of how things are organised:</p>
	<ul>
		<li>
			<strong><a href="/admin/pages">Pages</a></strong> - this is the map of your entire site. The URL and content of each page on your site is decided here.
		</li>
		<li>
			<strong><a href="/admin/content/all/1">Content</a></strong> - this is where you create the main content of your site. 
		</li>
		<li>
			<strong><a href="/admin/widgets/show">Widgets</a></strong> - these are anything that needs to appear on more than one page or in a specific position within one or more pages. This includes things like menus, slideshow, contact forms etc.
		</li>
		<li>
			<strong><a href="/admin/tags">Tags</a></strong> - content or images can be tagged with tags you make here. You are in full control of which tags can be applied to which content/media types.
		</li>
		<li>
			<strong><a href="/admin/images/show">Images</a></strong> - upload and organise the images used by your site.
		</li>
	</ul>

	<hr>
</section>