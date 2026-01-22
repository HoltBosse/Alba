<?php

namespace HoltBosse\Alba\Components\CssFile;

use HoltBosse\Alba\Core\{Component, CMS};
use \Exception;

class CssFile extends Component {
    public string $filePath;
    public bool $injectIntoHead = true;

    public function display(): void {
        $cssContents = file_get_contents($this->filePath);
        if ($cssContents === false) {
            throw new Exception("Failed to read CSS file: " . $this->filePath);
        }
        $cssWithStyleTags = "<style>\n" . $cssContents . "\n</style>";
        if($this->injectIntoHead) {
            CMS::Instance()->head_entries[] = $cssWithStyleTags;
        } else {
            echo $cssWithStyleTags;
        }
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        if(!isset($config->filePath) || !is_file($config->filePath)) {
            throw new Exception("CssFile component requires a valid filePath in config");
        }

        $this->filePath = $config->filePath;
        $this->injectIntoHead = $config->injectIntoHead ?? true;

        return $this->hookObjectIntance();
    }
}