<?php

namespace HoltBosse\Alba\Components\Admin\Table;

use HoltBosse\Alba\Core\{Component, Hook, Access};
Use HoltBosse\Alba\Components\Button\{Button, ButtonType};
Use HoltBosse\Alba\Components\Wrap\Wrap;
Use HoltBosse\Alba\Components\Collection\Collection;
Use HoltBosse\Alba\Components\Html\Html;

class TableField extends Component {
    public string $label;
    public bool $sortable;
    public string $rowAttribute;
    public string $hideAttribute; //defaults to rowAttribute if not set
    public string $rendererAttribute;
    public mixed $value;
    public ?Component $renderer = null;
    public array $tdAttributes = [];
    public int $columnSpan = 1;

    public function display(): void {
        $this->renderer->{$this->rendererAttribute} = $this->value;
        $this->renderer->display();
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        $this->label = $config->label ?? "";
        $this->sortable = $config->sortable ?? false;

        if(!isset($config->rowAttribute) || empty($config->rowAttribute)) {
            throw new \Exception("TableField requires a rowAttribute to be set.");
        } else {
            $this->rowAttribute = $config->rowAttribute;
        }

        $this->hideAttribute = $config->hideAttribute ?? $this->rowAttribute;

        $this->rendererAttribute = $config->rendererAttribute ?? "defaultvalue";
        $this->renderer = $config->renderer ?? null;
        $this->tdAttributes = $config->tdAttributes ?? [];
        $this->columnSpan = $config->columnSpan ?? 1;

        return $this->hookObjectIntance();
    }

    public function renderRowAttributes(): string {
        $attributesString = "";

        if($this->tdAttributes) {
            foreach($this->tdAttributes as $key=>$value) {
                $attributesString .= " $key='$value' ";
            }
        }

        return $attributesString;
    }
}