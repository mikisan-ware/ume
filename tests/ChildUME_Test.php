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
use \mikisan\pine\app\ChildUME;

$project_root = realpath(__DIR__ . "/../../../../");
require "{$project_root}/vendor/autoload.php";
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/../tests", true);

class ChildUME_Testclass extends TestCase
{
    use TestCaseTrait;
    
    private $ume;

    public function setUp(): void
    {
        $this->ume  = new ChildUME();
    }
    
    public function test_register_rules()
    {
        $rule   = [
          "page" => [
                "name" => "ページ", "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
                "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
                "method" => UME::GET, "require" => false
            ]  
        ];
        
        $this->callMethod(get_class($this->ume), "register_rules", [$rule]);
        $result = $this->ume->get_rules();
        
        $this->assertArrayHasKey("page", $result);
    }
    
}
