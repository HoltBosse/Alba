<?php

namespace HoltBosse\Alba\Components\Admin\MessageBox;

use HoltBosse\Alba\Core\Component;

class MessageBox extends Component {
    public string $heading;
    public string $text;

    public function display(): void {
        echo "<article {$this->renderAttributes()}>
            <div class='message-header'>
                <p>$this->heading</p>
                <button class='delete' aria-label='delete'></button>
            </div>
            <div class='message-body'>
                $this->text
            </div>
        </article>";
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        $this->heading = $config->heading ?? "";
        $this->text = $config->text ?? "";
        $this->classList[] = "message";

        return $this->hookObjectIntance();
    }
}