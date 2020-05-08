<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$content_type = new Content();
$all_content_types = $content_type->get_all_content_types();