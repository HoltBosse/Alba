<?php

use HoltBosse\Alba\Components\CssFile\CssFile;
use HoltBosse\Alba\Core\{CMS, Page};
use HoltBosse\Form\Input;
use Respect\Validation\Validator as v;

$body = "";

$components = Input::getvar("component", v::arrayType()->each(v::stringType())->notEmpty(), []);
$configs = Input::getvar("config", v::arrayType()->each(v::stringType())->notEmpty(), []);

/* if(sizeof($_POST) > 0) {
    CMS::pprint_r($components);
    CMS::pprint_r($configs);
} */

if(sizeof($components) !== sizeof($configs)) {
    die("Invalid input");
}

if(sizeof($components) > 0) {
    foreach($components as $index=>$component) {
        $rawConfig = $configs[$index];
        $config = json_decode($rawConfig);
        $normalizedConfig = (object) array_combine(array_column($config, 'name'), array_column($config, 'value'));

        ob_start();
            // @phpstan-ignore method.notFound
            $componentInstance = (new $availableComponentsFlat[$component])->loadFromConfig($normalizedConfig);

            $componentInstance->attributes["data-component"] = $component;
            $componentInstance->attributes["data-config"] = $rawConfig;
            $componentInstance->attributes["data-rendered"] = "true";
            
            $componentInstance->display();
        $body .= ob_get_clean();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <?php
            CMS::Instance()->page = new Page();
            echo CMS::Instance()->render_head();

            foreach (CMS::Instance()->head_entries as $he) {
                echo $he;
            }

            (new CssFile())->loadFromConfig((object)[
                "filePath"=>__DIR__ . "/css/reset.css",
                "injectIntoHead"=>false,
            ])->display();
            (new CssFile())->loadFromConfig((object)[
                "filePath"=>__DIR__ . "/css/boxmodel.css",
                "injectIntoHead"=>false,
            ])->display();
        ?>
    </head>
    <body>
        <?php echo $body; ?>
    </body>
</html>

<?php

die;