<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Widget_crowdriff extends Widget {
	public function render() {
		//CMS::pprint_r ($this);
		$query = "select * from media where id in (select content_id from tagged where tag_id=? and content_type_id=-1)";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($this->options[0]->value));
		$images = $stmt->fetchAll();
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
			window.addEventListener('load', (event) => {
				//console.log('page is fully loaded');
				var cri = document.querySelectorAll('.crowdriff_image_container');
				cri.forEach(im => {
					// setup lazy load to trigger after
					// hiq image has loaded fully
					var hiq = im.dataset.hiq;
					var imgel = new Image();
					imgel.src = hiq;
					imgel.thumb = im;
					imgel.onload = function(e){
						console.log(e);
						this.thumb.style.backgroundImage = "url('" + hiq + "')";
					}
				});
			});
		</script>
		<style>
			#crowdriff .contain {
				max-width: 2500px;
				margin: 0 auto;
			}
			#crowdriff {
				clear:both;
			}
			
			#crowdriff .contain div {

			}
			.crowdriff_image_wrap {
				overflow:hidden;
				position:relative;
			}
			.crowdriff_masonry_item {
				display:inline-block;
				padding:0.3em;
				position:relative;
			}
			
			.crowdriff_image_container {
				width: 100%;
				height: 15vh;
				transition:all 0.2s ease;
				background-size:cover;
				background-position:center;
			}

			.fullwidth .crowdriff_image_container {
				height:25vh;
			}

			.crowdriff_masonry_item:hover .crowdriff_image_container {
				transform:scale(1.1);
			}
			.crowdriff_row {
			}
			.crowdriff_info_popup {
				position:absolute;
				width:100%;
				background:rgba(0,0,0,0.7);
				height:25vh;
				opacity:0;
				z-index:0;
				transition:all 0.5s ease;
				padding:1em;
				overflow:hidden;
				color:white;
			}
			.crowdriff_masonry_item:hover .crowdriff_info_popup {
				height:25vh;
				display:block;
				opacity:1;
				z-index:2;
			}
			.crowdriff_info_popup p {
				color:white;
				font-size:90%;
			}
			
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