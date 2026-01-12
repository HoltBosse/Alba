<?php

namespace HoltBosse\Alba\Components\Admin\StateButtonGroup;

use HoltBosse\Alba\Core\Component;
use HoltBosse\Alba\Components\Collection\Collection;
use HoltBosse\Alba\Components\Button\{Button, ButtonType};
use HoltBosse\Alba\Components\Wrap\Wrap;

class StateButtonGroup extends Component {
    public string $location;
    public array $buttons=["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"];

    public function display(): void {
        (new Wrap())->loadFromConfig((object)[
            "id"=>$this->id,
            "classList"=>array_merge(["buttons","has-addons"], $this->classList),
            "innerComponent"=>(new Collection())->loadFromConfig((object)[
                "items"=>array_map(function($button, $class) {
                    $btn = new Button();
                    $btn->loadFromConfig((object)[
                        "text"=>ucwords($button),
                        "type"=>ButtonType::button,
                        "classList"=>["button", "is-$class","is-small","is-outlined"],
                        "attributes"=>[
                            "formaction"=>$_ENV["uripath"] . "/admin/$this->location/action/$button",
                            "type"=>"submit"
                        ]
                    ]);
                    if($button=="delete") {
                        $btn->attributes["onclick"] = "return window.confirm(\"Are you sure?\") ";;
                    }
                    return $btn;
                }, array_keys($this->buttons), $this->buttons)
            ]),
        ])->display();
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        if(isset($config->location)) {
            $this->location = $config->location;
        }

        if(isset($config->buttons)) {
            $this->buttons = $config->buttons;
        }

        return $this->hookObjectIntance();
    }
}