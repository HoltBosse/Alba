<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use \Exception;
Use HoltBosse\Alba\Components\Image\Image as ComponentImage;

class Image {
	public $id;
    public $filename;
    public $width;
    public $height;
    public $title;
    public $alt;
    public $mimetype;
    public static $image_sizes = [
		"thumb"=>200,
		"web"=>1920
	];
    // modified also available, but almost certainly never needed front-end
    
    public function __construct($id) {
        if (!is_numeric($id)) {
            throw new Exception('Cannot create image object from non-numerical id');
        }
        else {
            $this->id = $id;
            $db_image = DB::fetch('select * from media where id=?', $this->id);
            $this->filename = $db_image->filename;
            $this->width = $db_image->width;
            $this->height = $db_image->height;
            $this->title = $db_image->title;
            $this->alt = $db_image->alt;
            $this->mimetype = $db_image->mimetype;
        }       
    }
    
    #[\Deprecated(message: "use new oop component", since: "3.20.0")]
    public function render($size="", $class="", $output_immediately=true, $attributes=[]) {
        // size and class used in v <= 2.4.77
        // $w attribute supercedes $size
        // kept for back compat - class param and class passed via attribute are combined
        // handle attributes
        $class = $class . " " . ($attributes['class'] ?? ''); 
        $w = $attributes['w'] ?? null;
        if (!$w) {
            if ($size && !is_numeric($size)) {
                // no w attribute, string size - figure out or default to 1920
                $w = $this::$image_sizes[$size] ?? '1920';
            }
            elseif ($size) {
                // no $w attr, but got numeric $size
                $w = $size;
            }
            else {
                $w = $this->width; // default to og size if no $size or $w attr
            }
        }
        $q = $attributes['q'] ?? null;
        $fmt = $attributes['fmt'] ?? null;
        $loading = $attributes['loading'] ?? "lazy"; // use eager for headings
        $width_param = $this->width;
        $height_param = $this->height;
        if ($w && is_numeric($w)) {
            if ($w < $this->width) {
                $width_param = $w;
                // scale height
                $height_param = floor(($w/$this->width)*$this->height);
            }
        }

        $imageComponent = (new ComponentImage())->loadFromConfig((object)[
            "imageId"=>$this->id,
            "fixedWidth"=>$w,
            "quality"=>$q,
            "fmt"=>$fmt,
            "classList"=>explode(" ", trim($class)),
            "attributes"=>[
                "width"=>$width_param,
                "height"=>$height_param,
                "loading"=>$loading,
                "alt"=>$this->alt,
                "title"=>$this->title
            ]
        ]);

        if ($output_immediately) {
            $imageComponent->display();
        }
        else {
            ob_start();
            $imageComponent->display();
            $markup = ob_get_clean();

            return $markup;
        }
    }

    public static function makeThumb(string $src, string $dest, int $desired_width, File $file): bool {
        /* read the source image */
        if ($file->mimetype=='image/jpeg') {
            $source_image = imagecreatefromjpeg($src);
        } elseif ($file->mimetype=='image/webp') {
            $source_image = imagecreatefromwebp($src);
        } else {
            $source_image = imagecreatefrompng($src);
        }
        $width = imagesx($source_image);
        $height = imagesy($source_image);
        /* find the "desired height" of this thumbnail, relative to the desired width  */
        $desired_height = (int) floor($height * ($desired_width / $width)); //floor returns a float, cast to int, cause php is weird
        /* create a new, "virtual" image */
        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);
        /* copy source image at a resized size */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
        /* create the physical thumbnail image to its destination */
        if ($file->mimetype=='image/jpeg') {
            return imagejpeg($virtual_image, $dest);
        } elseif ($file->mimetype=='image/webp') {
            return imagewebp($virtual_image, $dest);
        } else {
            return imagepng($virtual_image, $dest);
        }
    }

    public static function correctImageOrientation(File $file): bool {
        if($file->mimetype != 'image/jpeg') {
            throw new Exception('Image orientation correction only available for JPEG images');
        }

        $filename = $file->filepath;

        if (function_exists('exif_read_data')) {
            $exif = exif_read_data($filename);
            if($exif && isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                /* if($orientation != 1 || true){ */
                    $img = imagecreatefromjpeg($filename);
                    $deg = 0;
                    switch ($orientation) {
                        case 3:
                            $deg = 180;
                            break;
                        case 6:
                            $deg = 270;
                            break;
                        case 8:
                            $deg = 90;
                            break;
                    }
                    if ($deg) {
                        $img = imagerotate($img, $deg, 0);        
                    }
                    // then rewrite the rotated image back to the disk as $filename 
                    return imagejpeg($img, $filename, 75);
                /* } */ // if there is some rotation necessary
            } // if have the exif orientation info
        } // if function exists  
        return false;
    }

    public static function processUploadedFiles(array $files, array $alts, array $titles, array $tags, string $directory): object {
        $image_types_data = File::$image_types;
        $uploaded_files = [];
        $n=0;
        $img_ids = [];
        foreach ($files["error"] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                $tmp_name = $files["tmp_name"][$key];
                // basename() may prevent filesystem traversal attacks;
                // further validation/sanitation of the filename may be appropriate
                // make filename unique-ish
                // TODO: per user images or per-date a la wordpress for unode / performance reasons?
                $name = uniqid() . "_" . basename($files["name"][$key]);
                $dest = $directory . "/" . $name;
                move_uploaded_file($tmp_name, $dest);
                $file = new File($dest);
                if($file->mimetype=="image/jpeg") {
                    Image::correctImageOrientation($file);
                }
                $uploaded_files[] = $name;
                //  get file info and put in db
                $title = $titles[$n];
                $alt = $alts[$n];
                $in_db_ok = DB::exec(
                    "INSERT INTO media (width, height, title, alt, filename, mimetype, domain) VALUES (?,?,?,?,?,?,?)",
                    [$file->width, $file->height, $title, $alt, $file->filename, $file->mimetype, ($_SESSION["current_domain"] ?? CMS::getDomainIndex($_SERVER['HTTP_HOST']))]
                );
                $img_ids[] = DB::getLastInsertedId();
                if ($in_db_ok) {
                    foreach(json_decode($tags[$n]) as $tag_id) {
                        DB::exec("insert into tagged (content_id, tag_id, content_type_id) values (?,?,-1)", [$img_ids[sizeof($img_ids)-1], $tag_id]);
                    }
                }
                else {
                    // TODO: handle db insert error - shouldn't happen, but need to return an appropriate JSON string to be handled
                }
            }
            $n++;
        }
        $uploaded_files = implode(",",$uploaded_files);

        foreach($img_ids as $id) {
            Actions::add_action("mediacreate", (object) [
                "affected_media"=>$id,
            ]);
        }


        // return json to javascript uploaded and
        CMS::Instance()->queue_message('Images uploaded','success');
        //echo '{"success":1,"msg":"Images uploaded","files":"'.$uploaded_files.'","ids":"'.implode(",",$img_ids).'"}';

        return (object) [
            "success"=>1,
            "msg"=>"Images uploaded",
            "files"=>$uploaded_files,
            "ids"=>implode(",",$img_ids),
            "tags"=>json_encode($tags),
            "urls"=>implode(",", array_map(function($c){ return $_ENV["uripath"] . "/image/$c"; }, $img_ids)),
        ];
    }
}