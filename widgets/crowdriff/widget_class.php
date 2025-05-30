<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Widget_crowdriff extends Widget {
	public function render() {
		$images = DB::fetchAll(
			"SELECT *
			FROM media
			WHERE id IN (
				SELECT content_id
				FROM tagged
				WHERE tag_id=?
				AND content_type_id=-1
			)",
			$this->options[0]->value
		);
		//CMS::pprint_r ($images);
		$image_array = array_values((array)$images);
		shuffle($image_array);
		$count = sizeof($image_array);

		if ($count<6||$count>12) {
			echo "<!--HOLT BOSSE CROWDRIFFalike must have 6-12 images -->";
		}
		if ($count>12) {
			$image_array = array_slice($image_array, 0, 12);
			$count = sizeof($image_array);
		}
		?>


		<script>
			<?php echo file_get_contents(CMSPATH . "/widgets/crowdriff/script.js"); ?>
		</script>
		<style>
			<?php echo file_get_contents(CMSPATH . "/widgets/crowdriff/style.css"); ?>
		</style>
		<?php 
			$fullwidthclass=" fullwidth ";
		?>
		<section id="crowdriff" class="<?php echo $fullwidthclass; ?>">
			<div class='contains'>
				<?php 
				if ($count==6) {
					$rows=[3,3];
				}
				if ($count==7) {
					$rows=array(3,4);
					if (random_int(0,1)==1) {
						$rows=array(4,3);
					}
				}
				if ($count==8) {
					$rows=[4,4];
				}
				if ($count==9) {
					$rows=[3,3,3];
				}
				if ($count==10) {
					$rows=[4,3,3];
				}
				if ($count==11) {
					$rows=[4,4,3];
					$i = random_int(0,2);
					if ($i==1) {
						$rows=array(4,3,4);
					}
					if ($i==2) {
						$rows = array(3,3,4);
					}
				}
				if ($count==12) {
					$rows=[4,4,4];
					// or $rows=[3,3,3,3]
				}
				//echo "<pre>"; print_r ($image_array); echo "</pre>";
				$c=0;
				for ($r=0; $r<sizeof($rows); $r++) {
					echo "<div class='crowdriff_row'>";
					$total=100;
					for ($col=0; $col<$rows[$r]; $col++) {
						//echo "<h1>r {$r} col {$col}</h1>";
						if ($rows[$r]==3) {
							$width = random_int(25,35);
						}
						if ($rows[$r]==4) {
							$width = random_int(20,30);
						}
						if ($col==$rows[$r]-1) {
							$width=$total; // last column, so just remainded
						}
						$total-=$width;
		
						// check for thumb - create if doesn't exist
						//echo "<h1>{$orig}</h1>";
						//echo "<pre>"; print_r ($image_array->image); echo "</pre>";
		
						
						echo "<a target='_blank' href='#' style='width:".$width."%;' class='crowdriff_masonry_item'>";
							
							echo "<div class='crowdriff_image_wrap'>";
								//echo "<div class='crowdriff_image_container' style='background-image:url(" . $image_array[$c]->image . ")'></div>";
								echo "<div data-hiq='". Config::$uripath . '/image/' . $image_array[$c]->id ."/web' class='crowdriff_image_container' style='background-image:url(" . Config::$uripath . '/image/' . $image_array[$c]->id . '/thumb' . ")'></div>";
							echo "</div>";
						echo "</a>";
						$c++;
					}
					echo "</div>";
				}
					
				?>
			</div>
		</section>
		<?php
	}
}