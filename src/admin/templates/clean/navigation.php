<?php

Use HoltBosse\DB\DB;
Use HoltBosse\Alba\Core\{CMS, Content};

$navigation = [
    "system"=>[
        "type"=>"addition_menu",
        "menu"=>[
            "label"=>"system",
            "links"=>[
                "general settings"=>"/admin/settings/general",
                "check for updates"=>"/admin/settings/updates",
                "system information"=>"/admin/settings/info",
                "backups"=>"/admin/settings/backups",
                "redirects"=>"/admin/redirects",
                "audit log"=>"/admin/audit",
                "manage images"=>"/admin/images/show",
            ]
        ]
    ],
    "users"=>[
        "type"=>"addition_link",
        "link"=>[
            "label"=>"users",
            "url"=>"/admin/users",
        ]
    ],
    "pages"=>[
        "type"=>"addition_link",
        "link"=>[
            "label"=>"pages",
            "url"=>"/admin/pages",
        ]
    ],
    "content"=>[
        "type"=>"addition_menu",
        "menu"=>[
            "label"=>"content",
            "links"=>array_filter(
                array_map(
                    function($input) {
                        if(Content::isAccessibleOnDomain($input[0]->id)) {
                            return $input[0]->link;
                        } else {
                            return null;
                        }
                    },
                    DB::fetchAll("SELECT title, CONCAT('/admin/content/all/', id) AS link, controller_location, id FROM content_types", [], ["mode"=>PDO::FETCH_GROUP])
                ),
                function($input) {
                    return !is_null($input);
                }
            ),
        ]
    ],
    "widgets"=>[
        "type"=>"addition_menu",
        "menu"=>[
            "label"=>"widgets",
            "links"=>array_merge(
                DB::fetchAll("SELECT title, CONCAT('/admin/widgets/show/', id) FROM widget_types", [], ["mode"=>PDO::FETCH_KEY_PAIR]),
                ["hr"=>"hr"],
                ["all widgets"=>"/admin/widgets/show"],
            )
        ]
    ],
    "plugins"=>[
        "type"=>"addition_menu",
        "menu"=>[
            "label"=>"plugins",
            "links"=>array_merge(
                DB::fetchAll("SELECT title, CONCAT('/admin/plugins/edit/', id) FROM plugins", [], ["mode"=>PDO::FETCH_KEY_PAIR]),
                ["hr"=>"hr"],
                ["all plugins"=>"/admin/plugins/show"],
            )
        ]
    ],
    "tags"=>[
        "type"=>"addition_link",
        "link"=>[
            "label"=>"tags",
            "url"=>"/admin/tags",
        ]
    ],
    "categories"=>[
        "type"=>"addition_menu",
        "menu"=>[
            "label"=>"categories",
            "links"=>array_merge(
                DB::fetchAll("SELECT title, CONCAT('/admin/categories/all/', id) FROM content_types", [], ["mode"=>PDO::FETCH_KEY_PAIR]),
                ["hr"=>"hr"],
                ["all Categories"=>"/admin/categories/all"],
                ["hr2"=>"hr"],
                ["tags"=>"/admin/categories/all/-3"],
            )
        ]
    ],
    "forms"=>[
        "type"=>"addition_menu",
        "menu"=>[
            "label"=>"forms",
            "links"=>[
                "submissions"=>"/admin/forms/submissions",
                "forms"=>"/admin/forms/all",
            ]
        ]
    ],
];