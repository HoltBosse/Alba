<?php
defined('CMSPATH') or die; // prevent unauthorized access
// default view is blog listing
// if single blog entry detected, single.php view is loaded
?>
<h1 class='title is-1'><?php echo CMS::Instance()->page->title;?></h1>
<?php if (sizeof($blog_content_items)==0):?>
<p>No blog entries found!</p>
<?php endif; ?>
<?php if (!$blog_content_item):?>
	<?php if ($filter_tag):?>
	<h5>Found <?php echo sizeof($blog_content_items);?> article(s) tagged &ldquo;<?php echo $filter_tag->title;?>&rdquo;</h5>
	<?php endif; ?>
	<div class='blog_list_wrap'>
	<?php foreach ($blog_content_items as $blog):?>
		<?php $tags = Tag::get_tags_for_content($blog->id, 1); ?>
		<div class='blog_wrap_item'>
			<div class='title_and_date'>
				<h4 class='title is-4'><?php echo $blog->title; ?></h4>
				<h5 class='title is-5'><?php echo date("F jS, Y", strtotime($blog->start));?></h5>
			</div>
			<div class='blog_tag_list_wrap'>
				<ol class='blog_tag_list'>
					<?php foreach ($tags as $tag):?>
						<?php if ($tag->public):?>
						<li>
							<a href='<?php echo CMS::Instance()->page->get_url();?>/tag/<?php echo $tag->alias;?>' class='blog_list_tag_link'><?php echo $tag->title;?></a>
						</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ol>
			</div>
			<p class='preview'><?php echo $blog->f_og_description ?></p>
			<a class='readmore' href='<?php echo CMS::Instance()->page->get_url() . "/" . $blog->alias;?>'>Read More</a>
			
		</div>
	<?php endforeach; ?>
	</div>
<?php else:?>
	<?php	include_once('single.php');?>
<?php endif; ?>