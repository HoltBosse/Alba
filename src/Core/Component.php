<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use HoltBosse\Alba\Components\Pagination\Pagination;
Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;
Use HoltBosse\Alba\Components\Admin\ControlBar\ControlBar as AdminControlBar;
Use HoltBosse\Alba\Components\Admin\Nav\Nav as AdminNav;
Use HoltBosse\Alba\Components\StateButton\StateButton;
Use HoltBosse\Alba\Components\Html\Html;
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
Use HoltBosse\Form\Input;

class Component {
    public ?string $id = null;
    // @phpstan-ignore missingType.iterableValue
    public array $classList = [];
    // @phpstan-ignore missingType.iterableValue
    public array $attributes = [];

    public function display(): void {
        echo "<p " . $this->renderAttributes() . ">Component base class</p>";
    }

    public function loadFromConfig(object $config): self {
        $this->id = $config->id ?? null;
        $this->classList = $config->classList ?? [];
        $this->attributes = $config->attributes ?? [];

        return $this;
    }

    public function hookObjectIntance(): static {
        $classChunks = explode("\\", $this::class);
        $class = end($classChunks);

        return Hook::execute_hook_filters(strtolower($class) . "_instance_created", $this);
    }

    public function renderAttributes(): string {
        $attributesString = "";
        
        if($this->id) {
            $attributesString .= " id='" . Input::stringHtmlSafe($this->id) . "' ";
        }

        $classes = $this->classList ?? [];
        $additionalClasses = isset($this->attributes["class"]) ? explode(" ", $this->attributes["class"]) : [];
        unset($this->attributes["class"]);
        $classes = array_merge($classes, $additionalClasses);
        $classes = array_map(function($class) {
            return Input::stringHtmlSafe($class);
        }, $classes);

        if(sizeof($classes) > 0) {
            $attributesString .= " class='" . implode(" ", $classes) . "' ";
        }

        if($this->attributes) {
            foreach($this->attributes as $key=>$value) {
                $attributesString .= " $key='" . Input::stringHtmlSafe($value) . "' ";
            }
        }

        return $attributesString;
    }

    #[\Deprecated(message: "use new oop component", since: "3.19.0")] //@phpstan-ignore-line
    public static function create_pagination($item_count, $pagination_size, $cur_page) {
        (new Pagination())->loadFromConfig((object)[
            "id"=>"pagination_component",
            "itemCount"=>$item_count,
            "itemsPerPage"=>$pagination_size,
            "currentPage"=>$cur_page
        ])->display();
    }

    #[\Deprecated(message: "use new oop component", since: "3.19.0")] //@phpstan-ignore-line
    public static function addon_button_group($id, $location, $buttons=["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]) {
        (new AdminStateButtonGroup())->loadFromConfig((object)[
            "id"=>$id,
            "location"=>$location,
            "buttons"=>$buttons
        ])->display();
    }

    #[\Deprecated(message: "use new oop component", since: "3.19.0")] //@phpstan-ignore-line
    public static function addon_button_toolbar($addonButtonGroupArgs, $leftContent="") {
        (new AdminButtonToolBar())->loadFromConfig((object)[
            "stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
                "id"=>$addonButtonGroupArgs[0],
                "location"=>$addonButtonGroupArgs[1],
                "buttons"=>$addonButtonGroupArgs[2] ?? ["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]
            ]),
            "leftContent"=>(new Html())->loadFromConfig((object)[
                "html"=>$leftContent ? "<div>" . $leftContent . "</div>" : "<div></div>",
                "wrap"=>false
            ])
        ])->display();
    }

    #[\Deprecated(message: "use new oop component", since: "3.19.0")] //@phpstan-ignore-line
    public static function addon_page_title($header, $byline=null, $rightContent=null) {
        (new TitleHeader())->loadFromConfig((object)[
            "header"=>html_entity_decode($header),
            "byline"=>$byline ? html_entity_decode($byline) : null,
            "rightContent"=>(new Html())->loadFromConfig((object)[
                "html"=>$rightContent ? "<div>" . $rightContent . "</div>" : "<div></div>",
                "wrap"=>false
            ])
        ])->display();
    }

    #[\Deprecated(message: "use new oop component", since: "3.19.0")] //@phpstan-ignore-line
    public static function render_admin_nav($navigation) {
        (new AdminNav())->loadFromConfig((object)[
            "navigation"=>$navigation
        ])->display();
    }

    #[\Deprecated(message: "use new oop component", since: "3.19.0")] //@phpstan-ignore-line
    public static function create_fixed_control_bar($middleButtonHtml="", $endBarHtml="") {
        (new AdminControlBar())->loadFromConfig((object)[
            "middleButton"=>$middleButtonHtml!="" ? (new Html())->loadFromConfig((object)[
                "html"=>$middleButtonHtml,
                "wrap"=>false
            ]) : null,
            "endBar"=>$endBarHtml!="" ? (new Html())->loadFromConfig((object)[
                "html"=>$endBarHtml,
                "wrap"=>false
            ]) : null
        ])->display();
    }

    #[\Deprecated(message: "use new oop component", since: "3.19.0")] //@phpstan-ignore-line
    public static function state_toggle($id, $state, $folder, $states, $type) {
        (new StateButton())->loadFromConfig((object)[
            "itemId"=>$id,
            "state"=>$state,
            "multiStateFormAction"=>$_ENV["uripath"] . "/admin/" . $folder . "/action/togglestate",
            "dualStateFormAction"=>$_ENV["uripath"] . "/admin/" . $folder . "/action/toggle",
            "states"=>$states,
            "contentType"=>$type
        ])->display();
    }
}