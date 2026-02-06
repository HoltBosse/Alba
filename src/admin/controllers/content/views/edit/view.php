<?php

	Use HoltBosse\Alba\Core\{CMS, Content, Hook, Component};
	Use HoltBosse\Form\{Form, Input};
	Use HoltBosse\DB\DB;
	Use HoltBosse\Alba\Components\Admin\ControlBar\ControlBar as AdminControlBar;
	Use HoltBosse\Alba\Components\Html\Html;
	Use HoltBosse\Alba\Components\CssFile\CssFile;

	(new CssFile())->loadFromConfig((object)[
		"filePath"=>__DIR__ . "/style.css",
	])->display();

?>

<?php if ($new_content):?>
	<h1 class='title'>New Content</h1>
<?php else:?>
	<?php echo "<script>var content_id=" . $content_id . "</script>"; ?>
	<?php if ($version_count>0):?>
		<a href='<?php echo $_ENV["uripath"];?>/admin/content/versions/<?php echo $content_id;?>' class='btn button cta pull-right'>Versions</a>
	<?php endif; ?>
	<h1 class='title'>Editing &ldquo;<?php echo Input::stringHtmlSafe($content->title); ?>&rdquo; - <?php echo Content::get_content_type_title($content->content_type);?></h1>
	
<?php endif; ?>

<hr>

<form method="POST" action="" enctype="multipart/form-data">
<input type="hidden" name="http_referer_form" value="<?php echo $_SERVER['HTTP_REFERER'];?>">

<div class='toggle_wrap '>
	<div class='flex'>
		<?php $required_details_form->display(); ?>
	</div>
</div>

<hr>

<?php $content_form->display(); ?>

<hr>

<?php
	$otherButton = '<button title=\'Save and keep working!\' class="button is-info" name="quicksave" value="quicksave" type="submit">Quick Save</button>';
	if($content_id) {
		$endBarHtml = '<i class="fa-solid fa-code-compare"></i>';
	} else {
		$endBarHtml = "";
	}

	(new AdminControlBar())->loadFromConfig((object)[
		"middleButton"=>(new Html())->loadFromConfig((object)[
			"html"=>$otherButton,
			"wrap"=>false
		]),
		"endBar"=>(new Html())->loadFromConfig((object)[
			"html"=>$endBarHtml,
			"wrap"=>false
		])
	])->display();
?>

<div class="version-menu">
	<h5 class="title is-5">Version History</h5>
	<?php
		$versions = DB::fetchall(
			"SELECT ua.id, ua.date, u.username, u.email
			FROM user_actions ua
			LEFT JOIN users u on ua.userid=u.id
			WHERE ua.type='contentupdate'
			AND REPLACE(JSON_EXTRACT(json, '$.content_id'), '\"', '')=?
			AND REPLACE(JSON_EXTRACT(json, '$.content_type'), '\"', '')=?
			ORDER BY ua.date DESC",
			[$content_id, $content_type]
		);

		echo "<div class='version-list'>";

			foreach($versions as $version) {
				$niceDate = date("F j Y, g:ia", strtotime($version->date));
				$pureUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

				echo "
					<a href='{$pureUrl}?revision=$version->id' class='revision-entry'>
						<p>$niceDate</p>
						<p>$version->username ($version->email)</p>
					</a>
				";
			}

		echo "</div>";
	?>
</div>


</form>

<?php

echo "<script>";
	echo file_get_contents(__DIR__ . "/script.js");
echo "</script>";