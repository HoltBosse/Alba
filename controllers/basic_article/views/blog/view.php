<?php
defined('CMSPATH') or die; // prevent unauthorized access
// default view is blog listing
// if single blog entry detected, single.php view is loaded
?>
<h1 class='title is-1'><?php echo CMS::Instance()->page->title;?></h1>
<?php if (sizeof($blog_content_items)==0):?>
<p>No blog entries found!</p>
<?php endif; ?>
<?php if (sizeof($blog_content_items)>1):?>
	<?php foreach ($blog_content_items as $blog):?>
		<hr>
		<h4 class='title is-4'><?php echo $blog->title; ?>
		<h5 class='title is-5'><?php echo date("F jS, Y", strtotime($blog->start));?>
		<a class='readmore' href='<?php echo Config::$uripath . '/' . implode('/',CMS::Instance()->uri_path_segments) . '/' . $blog->alias;?>'>Read More</a>
		<?php //echo $blog->f_markup; ?>
		<hr>
	<?php endforeach; ?>
<?php else:?>
	<?php	include_once('single.php');?>
<?php endif; ?>