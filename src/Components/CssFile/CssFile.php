<?php

namespace HoltBosse\Alba\Components\CssFile;

use HoltBosse\Alba\Core\{Component, CMS, File};
use \Exception;

class CssFile extends Component {
    public string $filePath;
    public bool $injectIntoHead = true;
    public bool $renderOnce = false;

    /** @var array<string, bool> */
    private static array $renderedFiles = [];

    public function display(): void {
        // Check if renderOnce is enabled and file has already been rendered
        if ($this->renderOnce && isset(self::$renderedFiles[$this->filePath])) {
            return;
        }

        $cssContents = File::getContents($this->filePath);
        $cssWithStyleTags = "<style>\n" . $cssContents . "\n</style>";
        if($this->injectIntoHead) {
            CMS::Instance()->head_entries[] = $cssWithStyleTags;
        } else {
            echo $cssWithStyleTags;
        }

        // Mark this file as rendered
        if ($this->renderOnce) {
            self::$renderedFiles[$this->filePath] = true;
        }
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        if(!isset($config->filePath) || !is_file($config->filePath)) {
            throw new Exception("CssFile component requires a valid filePath in config");
        }

        $this->filePath = $config->filePath;
        $this->injectIntoHead = $config->injectIntoHead ?? true;
        $this->renderOnce = $config->renderOnce ?? false;

        return $this->hookObjectIntance();
    }
}