# Alba
A PHP/MySQL based Content Management System. Designed with developer flexibility in mind.

![image](https://user-images.githubusercontent.com/23583515/177893353-88814756-a371-4537-a3f0-5a9dada85efb.png)

## Requirements
* PHP 8.0 + (with mod_rewrite)
* MySQL/MariaDB 5.0+

Native PHP zip + 'fopen' PHP function availablity required for automatic updates.

## Installation
1. Download .zip and extract into working directory
2. run `composer install`
3. Follow prompts in terminal

That's it, now browse to /admin to start. See [wiki](https://github.com/HoltBosse/Alba/wiki) for more info.

## For dev work/testing

### PHPSTAN test command

`composer albatest`

### Biomejs testing

see https://biomejs.dev/guides/manual-installation. install in your path and run `biome lint`
