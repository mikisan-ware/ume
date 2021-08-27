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
use \mikisan\core\basis\ume\UME;
use \mikisan\core\exception\UMEException;
use \mikisan\pine\app\Child2UME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class Child2UME_Test extends TestCase
{
    use TestCaseTrait;
    
    public function setUp(): void
    {
    }
    
    public function test_construct()
    {
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("バリデーションルール page の設定に、許可されていないキー something が使用されています。");
        //
        $ume    = new Child2UME();
    }
}
