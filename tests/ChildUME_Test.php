<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ルーター
 * Start Date  : 2021/07/17
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\util\ume\BaseUME;
use \mikisan\core\util\ume\ChildUME;

$project_root = realpath(__DIR__ . "/../../../../");
require "{$project_root}/vendor/autoload.php";
require "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src");
Autoload::register(__DIR__ . "/../tests");

class ChildUME_Testclass extends TestCase
{
    use TestCaseTrait;

    //put your code here
}
