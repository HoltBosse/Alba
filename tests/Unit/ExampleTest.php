<?php

test('example', function () {
    expect(true)->toBeTrue();
});

test('sum', function () {
    $result = 1+2;
  
    expect($result)->toBe(3);
});

test('loltest', function () {
    $result = 1+2;

    //define ("CMSPATH", $this->getCMSPATH());
    $this->initCMSPATH();
    //echo CMSPATH;
    require_once(CMSPATH . "/core/form.php");
    $form = new Form((object) [
        "id"=>"thisisatestform",
        "fields"=>[],
    ]);
    print_r($form);
  
    expect($result)->toBe(3);
});