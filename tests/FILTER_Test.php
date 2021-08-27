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
use \mikisan\core\basis\ume\FILTER;
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class FILTER_Test extends TestCase
{
    use TestCaseTrait;
    
    protected $filters;

    public function setUp(): void
    {
        $ume            = new ChildUME();
        $this->filters  = $ume->getFilters();
    }
    
    /**
     * base64文字列のデコードテスト
     */
    public function test_do_base64()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $string     = "あいうえお漢字<script>alert(\"XSS!\");ABC12345=!*";
        $value      = base64_encode($string);
        
        $result     = FILTER::do($this->filters, $value, $conditions);
        $this->assertSame($string, $result);
    }
    
    /**
     * htmlspecialchars文字列のデコードテスト
     */
    public function test_do_htmlspec()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "htmlspec", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $string     = "あいうえお漢字<script>alert(\"XSS!\");ABC12345=!*";
        $value      = htmlspecialchars($string, ENT_QUOTES);
        
        $result     = FILTER::do($this->filters, $value, $conditions);
        $this->assertSame($string, $result);
    }
    
    /**
     * htmlspecialcharsとbase64がネストされた文字列のデコードテスト
     */
    public function test_do_htmlspec_and_base64()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64|htmlspec", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $string     = "あいうえお漢字<script>alert(\"XSS!\");ABC12345=!*";
        $value      = base64_encode(htmlspecialchars($string, ENT_QUOTES));
        
        $result     = FILTER::do($this->filters, $value, $conditions);
        $this->assertSame($string, $result);
    }
    
}
