<?php
$blog = $blog_content_items[0];
?>
<h4 class='title is-4'><?php echo $blog->title; ?></h4>
<h5 class='title is-5'><?php echo date("F jS, Y", strtotime($blog->start));?></h5>
<?php echo $blog->f_markup; ?>