<?php

namespace HoltBosse\Alba\Components\Button;

use HoltBosse\Alba\Core\Component;
use HoltBosse\Form\Input;
Use HoltBosse\Form\{FormBuilderAttribute, FormBuilderDataType};

class Button extends Component {
    #[FormBuilderAttribute(fieldType: "Input", dataType: FormBuilderDataType::String, required: true)]
    public string $text = "Button";
    public ButtonType $type = ButtonType::anchor;
    #[FormBuilderAttribute(fieldType: "Input", dataType: FormBuilderDataType::String, required: true)]
    public ?string $url = null;

    public function display(): void {
        ?>
            <<?php echo $this->type->value; ?> <?php echo $this->renderAttributes(); ?>>
                <?php echo Input::stringHtmlSafe($this->text); ?>
            </<?php echo $this->type->value; ?>>
        <?php
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        if(isset($config->url)) {
            $this->attributes['href'] = $config->url;
        }

        if (isset($config->text)) {
            $this->text = $config->text;
        }
        if (isset($config->url)) {
            $this->url = $config->url;
        }
        if (isset($config->type)) {
            $this->type = $config->type;
        }

        return $this->hookObjectIntance();
    }
}