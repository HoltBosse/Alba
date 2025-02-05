<?php defined('CMSPATH') or die; // prevent unauthorized access

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
            "links"=>array_merge(
                DB::fetchall("SELECT title, CONCAT('/admin/content/all/', id) FROM content_types", [], ["mode"=>PDO::FETCH_KEY_PAIR]),
                ["hr"=>"hr"],
                ["check flat tables"=>"/admin/content/check"],
            )
        ]
    ],
    "widgets"=>[
        "type"=>"addition_menu",
        "menu"=>[
            "label"=>"widgets",
            "links"=>array_merge(
                DB::fetchall("SELECT title, CONCAT('/admin/widgets/show/', id) FROM widget_types", [], ["mode"=>PDO::FETCH_KEY_PAIR]),
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
                DB::fetchall("SELECT title, CONCAT('/admin/plugins/edit/', id) FROM plugins", [], ["mode"=>PDO::FETCH_KEY_PAIR]),
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
                DB::fetchall("SELECT title, CONCAT('/admin/categories/all/', id) FROM content_types", [], ["mode"=>PDO::FETCH_KEY_PAIR]),
                ["hr"=>"hr"],
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
            ]
        ]
    ],
];