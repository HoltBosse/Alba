<?php
defined('CMSPATH') or die; // prevent unauthorized access
// default view is blog listing
// if single blog entry detected, single.php view is loaded
?>
<h1 class='title is-1'><?php echo CMS::Instance()->page->title;?></h1>
<?php if (sizeof($blog_content_items)==0):?>
<p>No blog entries found!</p>
<?php endif; ?>
<?php if (!$single_blog_content_item):?>
	<div class='blog_list_wrap'>
	<?php foreach ($blog_content_items as $blog):?>
		<div class='blog_wrap_item'>
			<h4 class='title is-4'><?php echo $blog->title; ?></h4>
			<h5 class='title is-5'><?php echo date("F jS, Y", strtotime($blog->start));?></h5>
			<a class='readmore' href='<?php echo CMS::Instance()->page->get_url() . "/" . $blog->alias;?>'>Read More</a>
			<?php //echo $blog->f_markup; ?>
		</div>
	<?php endforeach; ?>
	</div>
<?php else:?>
	<?php	include_once('single.php');?>
<?php endif; ?>