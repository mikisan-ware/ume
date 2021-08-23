<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/07/17
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\util\ume\NORMAL;

$project_root = realpath(__DIR__ . "/../../../../");
require "{$project_root}/vendor/autoload.php";
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);

class NORMAL_Test extends TestCase
{
    use TestCaseTrait;

    public function setUp(): void
    {
        $this->dto  = new Dto();
        $this->dto->settings->empty_value   = "";
    }
    
    /**
     * 実体参照文字列のデコードテスト
     */
    public function test_decode_single()
    {
        $key        = "test";
        $conditions = [
            "name" => "ページ", "type" => "text", "min" => 0, "max" => 64, 
            "auto_correct" => true, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "decode" => UME::HTML, "require" => false
        ];
        
        NORMAL::parse($this->$dto, $key, $conditions);
    }
    
}
