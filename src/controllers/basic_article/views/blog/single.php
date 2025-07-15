<?php

Use HoltBosse\Alba\Core\{CMS, Content, Tag};

$blog = $blog_content_items[0]; 
$tags = Tag::get_tags_for_content($blog->id, 1); 
?>
<!-- <h4 class='title is-4'><?php echo $blog->title; ?></h4> -->
<div class='single_blog_wrap'>
    <h5 class='title is-5'><?php echo date("F jS, Y", strtotime($blog->start));?></h5>
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
    <?php echo $blog->markup; ?>
</div>