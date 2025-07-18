# Alba
A PHP/MySQL based Content Management System. Designed with developer flexibility in mind.

![image](https://user-images.githubusercontent.com/23583515/177893353-88814756-a371-4537-a3f0-5a9dada85efb.png)

## Requirements
* PHP 8.2 + (with mod_rewrite)
* MySQL/MariaDB 5.0+

## Installation
1. create project with composer
2. require this package and install
3. run `./vendor/bin/alba`
4. Follow prompts in terminal

That's it, now browse to /admin to start. See [wiki](https://github.com/HoltBosse/Alba/wiki) for more info.

## For dev work/testing

### PHPSTAN test command

`./vendor/bin/phpstan`

### Biomejs testing

see https://biomejs.dev/guides/manual-installation. install in your path and run `biome lint`