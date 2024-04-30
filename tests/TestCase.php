<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function getCMSPATH() {
        return realpath(dirname(__FILE__) . "/..");
    }

    public function initCMSPATH() {
        define ("CMSPATH", $this->getCMSPATH());
    }
}
