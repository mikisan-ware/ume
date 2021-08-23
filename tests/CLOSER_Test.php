<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/15
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\basis\ume\CLOSER;
//
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class CLOSER_Test extends TestCase
{
    use TestCaseTrait;
    
    protected $closers;

    public function setUp(): void
    {
        $ume            = new ChildUME();
        $this->closers  = $ume->get_closers();
    }
    
    /**
     * base64文字列のデコードテスト
     */
    public function test_do_base64()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "auto_correct" => true, 
            "filter" => null, "closer" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value      = "あいうえお漢字<script>alert(\"XSS!\");ABC12345=!*";
        $closed     = base64_encode($value);
        
        $result     = CLOSER::do($this->closers, $value, $conditions);
        $this->assertSame($closed, $result);
    }
    
    /**
     * htmlspecialchars文字列のデコードテスト
     */
    public function test_do_htmlspec()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "auto_correct" => true, 
            "filter" => null, "closer" => "htmlspec", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value      = "あいうえお漢字<script>alert(\"XSS!\");ABC12345=!*";
        $closed     = htmlspecialchars($value, ENT_QUOTES);
        
        $result     = CLOSER::do($this->closers, $value, $conditions);
        $this->assertSame($closed, $result);
    }
    
    /**
     * htmlspecialcharsとbase64がネストされた文字列のデコードテスト
     */
    public function test_do_htmlspec_and_base64()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "auto_correct" => true, 
            "filter" => null, "closer" => "htmlspec|base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value      = "あいうえお漢字<script>alert(\"XSS!\");ABC12345=!*";
        $closed     = base64_encode(htmlspecialchars($value, ENT_QUOTES));
        
        $result     = CLOSER::do($this->closers, $value, $conditions);
        $this->assertSame($closed, $result);
    }
    
}
