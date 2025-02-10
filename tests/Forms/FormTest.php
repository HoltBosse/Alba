<?php

beforeAll(function () {
   Class Config {
      public static function __callStatic($name, $args) {
         return false;
      }
   }

   define("CMSPATH", realpath(dirname(__FILE__)) . "/../..");
   require_once(CMSPATH . "/core/autoloader.php");

   $curpath = realpath(dirname(__FILE__));
   require_once(CMSPATH . "/core/form.php");
});

test('sum', function () {
   $result = 1+2;

   $thing = new Form();
 
   expect($result)->toBe(3);
});

test('sum2', function () {
   $result = 1+2;

   //$thing = new Form();
 
   expect($result)->toBe(3);
});