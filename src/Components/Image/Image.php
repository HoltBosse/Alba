<?php

namespace HoltBosse\Alba\Components\Image;

use HoltBosse\Alba\Core\Component;
use HoltBosse\DB\DB;
use \stdClass;
use \Exception;

class Image extends Component {
    public int $imageId;
    public ?int $fixedWidth = null;
    public ?int $fixedHeight = null;
    public ?int $quality = null;
    public ?string $fmt = null;

    public function display(): void {
        ?>
            <img <?php echo $this->renderAttributes(); ?> />
        <?php
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        $this->imageId = $config->imageId ?? throw new Exception("Image component requires imageId in config");
        $dbImage = DB::fetch('SELECT * FROM media WHERE id=?', $this->imageId);

        $this->fixedWidth = $config->fixedWidth ?? null;
        $this->fixedHeight = $config->fixedHeight ?? null;
        $this->fmt = $config->fmt ?? null;
        $this->quality = $config->quality ?? null;
        $this->attributes["width"] = $this->attributes["width"] ?? $dbImage->width;
        $this->attributes["height"] = $this->attributes["height"] ?? $dbImage->height;
        $this->attributes["title"] = $this->attributes["title"] ?? $dbImage->title;
        $this->attributes["alt"] = $this->attributes["alt"] ?? $dbImage->alt;
        $this->attributes["loading"] = $this->attributes["loading"] ?? "lazy";
        $this->attributes["decode"] = $this->attributes["decode"] ?? "async";

        $link = $_ENV["uripath"] . "/image/" . $config->imageId;
        $params = [];
        if ($this->fixedWidth) {
            $params["w"] = $this->fixedWidth;
        }
        if ($this->fixedHeight) {
            $params["h"] = $this->fixedHeight;
        }
        if ($this->fmt) {
            $params["fmt"] = $this->fmt;
        }
        if ($this->quality) {
            $params["q"] = $this->quality;
        }
        if (count($params) > 0) {
            $link .= "?" . http_build_query($params);
        }

        $this->attributes["src"] = $this->attributes["src"] ?? $link;

        $this->classList[] = "rendered_img";

        return $this->hookObjectIntance();
    }
}