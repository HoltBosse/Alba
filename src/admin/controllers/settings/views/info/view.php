<?php

Use HoltBosse\Alba\Core\{CMS, Mail, Component};
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
Use HoltBosse\Alba\Components\Admin\MessageBox\MessageBox;

function embedded_phpinfo(): void {
    ob_start();
    phpinfo();
    $phpinfo = ob_get_contents();
    ob_end_clean();
    $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
    echo "
        <style type='text/css'>
            #phpinfo {}
            #phpinfo pre {margin: 0; font-family: monospace;}
            #phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
            #phpinfo a:hover {text-decoration: underline;}
            #phpinfo table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc; color: black;}
            #phpinfo .center {text-align: center;}
            #phpinfo .center table {margin: 1em auto; text-align: left;}
            #phpinfo .center th {text-align: center !important;}
            #phpinfo td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
            #phpinfo h1 {font-size: 150%;}
            #phpinfo h2 {font-size: 125%;}
            #phpinfo .p {text-align: left;}
            #phpinfo .e {background-color: #ccf; width: 300px; font-weight: bold;}
            #phpinfo .h {background-color: #99c; font-weight: bold;}
            #phpinfo .v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
            #phpinfo .v i {color: #999;}
            #phpinfo img {float: right; border: 0;}
            #phpinfo hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
        </style>
        <div id='phpinfo'>
            $phpinfo
        </div>
        ";
}

(new TitleHeader())->loadFromConfig((object)[
    "header"=>"System Information",
])->display();

(new MessageBox())->loadFromConfig((object)[
    "heading" => 'Native Zip',
    "text" => $native_zip ? 'Native zip handling is available. This is required for automatic update installation.' : 'Native zip handling is <em>not</em> available. This is required for automatic update installation.',
    "classList" => [$native_zip ? 'is-success' : 'is-warning'],
])->display();

(new MessageBox())->loadFromConfig((object)[
    "heading" => 'PHP fopen Allowed',
    "text" => $allow_fopen ? 'fopen is available. This is required for automatic updates and Google reCAPTCHA.' : 'fopen is <em>not</em> available. This is required for automatic updates and Google reCAPTCHA.',
    "classList" => [$allow_fopen ? 'is-success' : 'is-warning'],
])->display();

(new MessageBox())->loadFromConfig((object)[
    "heading" => 'GD Graphics Library Available',
    "text" => $gd_available ? 'GD is available. This is required for image manipulation.' : 'GD is <em>not</em> available. This is required for image manipulation.',
    "classList" => [$gd_available ? 'is-success' : 'is-warning'],
])->display();

(new MessageBox())->loadFromConfig((object)[
    "heading" => 'Virtual Available',
    "text" => $virtual_available ? 'virtual is available. This enables fast file-serving in PHP on Apache.' : 'virtual is <em>not</em> available. This enables fast file-serving in PHP on Apache, but is not required.',
    "classList" => [$virtual_available ? 'is-success' : 'is-warning'],
])->display();

(new MessageBox())->loadFromConfig((object)[
    "heading" => 'MySQL Dump Available',
    "text" => $mysqldump_available ? 'MySQL Dump is available. This is required for backups.' : 'MySQL Dump is <em>not</em> available. This is required for backups.',
    "classList" => [$mysqldump_available ? 'is-success' : 'is-warning'],
])->display();

(new MessageBox())->loadFromConfig((object)[
    "heading" => 'CURL Available',
    "text" => $curl_available ? 'CURL is available.' : 'CURL is <em>not</em> available.',
    "classList" => [$curl_available ? 'is-success' : 'is-warning'],
])->display();

?>

<hr>

<h2 class='title is-2'>PHP Info</h2>

<?php embedded_phpinfo(); ?>