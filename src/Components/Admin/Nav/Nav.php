<?php

namespace HoltBosse\Alba\Components\Admin\Nav;

use HoltBosse\Alba\Core\{Component, Hook, Access};
Use HoltBosse\Alba\Components\Button\{Button, ButtonType};
Use HoltBosse\Alba\Components\Wrap\Wrap;
Use HoltBosse\Alba\Components\Collection\Collection;
Use HoltBosse\Alba\Components\Html\Html;

class Nav extends Component {
    // @phpstan-ignore missingType.iterableValue
    public array $navigation = [];

    // @phpstan-ignore missingType.iterableValue
    private function GetLink(array $link): Component {
        return (new Button())->loadFromConfig((object)[
            "text"=>ucwords($link["label"]),
            "type"=>ButtonType::anchor,
            "url"=>$_ENV["uripath"] . $link["url"],
            "classList"=>["navbar-item"]
        ]);
    }

    // @phpstan-ignore missingType.iterableValue
    private function getMenu(array $menu): Component {
        $items = [];
        foreach($menu["links"] as $label=>$url) {
            if($label=="hr" || $url=="hr") {
                $items[] = (new Html())->loadFromConfig((object)[
                    "html"=>"<hr class='dropdown-divider'>",
                    "wrap"=>false
                ]);
            } else {
                $pathParts = explode("/", ltrim((string) parse_url($url, PHP_URL_PATH), "/"));
                if (Access::can_access(Access::getAdminAccessRule($pathParts[1]) ?? null)) {
                    $link = [
                        "label" => $label,
                        "url" => $url
                    ];
                    $items[] = $this->GetLink($link);
                }
            }
        }

        return (new Wrap())->loadFromConfig((object)[
            "classList"=>["navbar-item", "has-dropdown", "is-hoverable"],
            "innerComponent"=>(new Collection())->loadFromConfig((object)[
                "items"=>[
                    (new Button())->loadFromConfig((object)[
                        "classList"=>["navbar-link"],
                        "text"=>ucwords($menu["label"]),
                        "type"=>ButtonType::anchor,
                    ]),
                    (new Wrap())->loadFromConfig((object)[
                        "classList"=>["navbar-dropdown"],
                        "innerComponent"=>(new Collection())->loadFromConfig((object)[
                            "items"=>$items
                        ])
                    ])
                ]
            ]),
        ]);
    }

    public function display(): void {
        foreach($this->navigation as $label=>$config) {
            //setting this explicitly to null to solve the following warning spam: Undefined array key "$key" in $filepath
            if (Access::can_access(Access::getAdminAccessRule($label) ?? null)) {
                if($config["type"]=="addition_menu") {
                    $this->getMenu($config["menu"])->display();
                } elseif($config["type"]=="addition_link") {
                    $this->GetLink($config["link"])->display();
                }
            }
        }
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        $this->navigation = $config->navigation ?? [];
        $this->navigation = Hook::execute_hook_filters('render_admin_nav', $this->navigation);

        return $this->hookObjectIntance();
    }
}