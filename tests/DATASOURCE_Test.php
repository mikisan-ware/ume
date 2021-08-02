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
use \mikisan\core\basis\ume\Dto;
use \mikisan\core\basis\ume\DATASOURCE;
use \mikisan\core\exception\UMEException;

$project_root = realpath(__DIR__ . "/../../../../");
require "{$project_root}/vendor/autoload.php";
require "{$project_root}/tests/TestCaseTrait.php";
require "{$project_root}/core/basis/ume/src/BaseUME.php";
Autoload::register(__DIR__ . "/../src", true);

class DATASOURCE_Test extends TestCase
{
    use TestCaseTrait;
    
    private $dto;
    
    public function setUp(): void
    {
        $this->dto  = new Dto();
        $this->dto->settings->empty_value   = "";
    }
    
    public function test_get()
    {
        $key        = "test";
        $conditions = [
            "name" => "テスト", "type" => "text", "min" => 0, "max" => 64, 
            "auto_correct" => true, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $_GET["test"] = "あいうえお";
        $_POST["test"] = "かきくけこ";
        $_COOKIE["test"] = "さしすせそ";
        //
        $conditions["method"]   = "get";
        $this->assertEquals("あいうえお", DATASOURCE::get($this->dto, $key, $conditions));
        //
        $conditions["method"]   = "post";
        $this->assertEquals("かきくけこ", DATASOURCE::get($this->dto, $key, $conditions));
        //
        $conditions["method"]   = "cookie";
        $this->assertEquals("さしすせそ", DATASOURCE::get($this->dto, $key, $conditions));
    }
    
    public function test_get_not_set()
    {
        $key        = "test";
        $conditions = [
            "name" => "テスト", "type" => "text", "min" => 0, "max" => 64, 
            "auto_correct" => true, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $_GET["test"] = null;
        $this->dto->settings->empty_value   = "None";
        //
        $conditions["method"]   = "get";
        $this->assertEquals("None", DATASOURCE::get($this->dto, $key, $conditions));
    }
    
}
