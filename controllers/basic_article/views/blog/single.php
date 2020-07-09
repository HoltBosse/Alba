<?php
$blog = $blog_content_items[0];
?>
<h4 class='title is-4'><?php echo $blog->title; ?>
<h5 class='title is-5'><?php echo date("F jS, Y", strtotime($blog->start));?>
<?php echo $blog->f_markup; ?>