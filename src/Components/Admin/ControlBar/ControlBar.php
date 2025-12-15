<?php

namespace HoltBosse\Alba\Components\Admin\ControlBar;

use HoltBosse\Alba\Core\Component;

class ControlBar extends Component {
    public ?Component $middleButton = null;
    public ?Component $endBar = null;

    public function display(): void {
        ?>
            <style>
                <?php echo file_get_contents(__DIR__ . '/style.css'); ?>
            </style>
            <script>
                <?php echo file_get_contents(__DIR__ . '/script.js'); ?>
            </script>
            <div <?php echo $this->renderAttributes(); ?>>
                <button title="Save and exit" class="button is-primary" type="submit">Save</button>
                <?php $this->middleButton ? $this->middleButton->display() : null; ?>
                <button class="button is-warning" type="button" onclick="fixedControllerBarGoBack();">Cancel</button>
                <?php $this->endBar ? $this->endBar->display() : null; ?>
            </div>
        <?php
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        if(isset($config->middleButton)) {
            $this->middleButton = $config->middleButton;
        }

        if(isset($config->endBar)) {
            $this->endBar = $config->endBar;
        }

        $this->classList[] = "fixed-control-bar";

        return $this;
    }
}