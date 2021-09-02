<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/29
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\basis\ume\VALIDATE;
use \mikisan\core\exception\UMEException;
//
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class VALIDATE_Test extends TestCase
{
    use TestCaseTrait;
    
    protected   $class_name = "\\mikisan\\core\\basis\\ume\\VALIDATE";

    public function setUp(): void
    {
        $this->ume              = new ChildUME();
    }
    
    private function get_response(): \stdClass
    {
        $response               = new \stdClass();
        $response->has_error    = false;
        $response->on_error     = false;
        $response->VE           = [];
        $response->offset       = [];
        $response->src          = [];
        $response->dist         = [];
        
        return $response;
    }
    
    public function test_get_serial_numbers_1()
    {
        $key    = "param_1_2_3";
        $prefix = "param";
        $result = $this->callMethod($this->class_name, "get_serial_numbers", [$key, $prefix]);
        $this->assertCount(3, $result);
        $this->assertSame(1, $result[0]);
        $this->assertSame(2, $result[1]);
        $this->assertSame(3, $result[2]);
    }
    
    public function test_get_serial_numbers_2()
    {
        $key    = "param_7_5";
        $prefix = "param";
        $result = $this->callMethod($this->class_name, "get_serial_numbers", [$key, $prefix]);
        $this->assertCount(2, $result);
        $this->assertSame(7, $result[0]);
        $this->assertSame(5, $result[1]);
    }
    
    public function test_do_hierarchy_get()
    {
        $_GET["param_1_2_3"]    = "hoge";
        $_GET["param_1_2_4"]    = "hage";
        $_GET["param_1_3_1"]    = "abc";
        $_GET["param_1_3_7"]    = "def";
        $key                    = "param_%_%_%";
        $matches    = [];
        preg_match("/\A([^%]+)_(%_)*%(\[\])?\z/u", $key, $matches);
        $prefix                 = $matches[1];
        $conditions = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $response   = $this->get_response();
        //
        VALIDATE::normal($this->ume, $key, $conditions, $response);
        $this->assertCount(1, $response->dist[$prefix]);
        $this->assertSame(false, $response->has_error);
        //
        $this->assertSame("hoge", $response->dist[$prefix][1][2][3]);
        $this->assertSame("hage", $response->dist[$prefix][1][2][4]);
        $this->assertSame("abc", $response->dist[$prefix][1][3][1]);
        $this->assertSame("def", $response->dist[$prefix][1][3][7]);
    }
    
    public function test_do_hierarchy_get_with_validateion_error()
    {
        $_GET["param_1_2_3"]    = "1234";
        $_GET["param_1_2_4"]    = "５６７８";
        $_GET["param_1_3_1"]    = "abc";
        $_GET["param_1_3_7"]    = "九〇一二";
        $key                    = "param_%_%_%";
        $matches    = [];
        preg_match("/\A([^%]+)_(%_)*%(\[\])?\z/u", $key, $matches);
        $prefix                 = $matches[1];
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $response   = $this->get_response();
        //
        VALIDATE::normal($this->ume, $key, $conditions, $response);
        $this->assertSame(true, $response->has_error);
        //
        $this->assertSame(1234, $response->dist[$prefix][1][2][3]);
        $this->assertSame(5678, $response->dist[$prefix][1][2][4]);
        $this->assertSame("abc", $response->dist[$prefix][1][3][1]);
        $this->assertSame("九〇一二", $response->dist[$prefix][1][3][7]);
        //
        $this->assertCount(2, $response->VE[$prefix]);
        $this->assertSame("[データ値:1:3:1] は整数でなければなりません。", $response->VE[$prefix][0]);
        $this->assertSame("[データ値:1:3:7] は整数でなければなりません。", $response->VE[$prefix][1]);
    }
    
    public function test_do_hierarchy_get_with_illegal_method()
    {
        $_GET["param_1_2_3"]    = "1234";
        $_GET["param_1_2_4"]    = "５６７８";
        $_GET["param_1_3_1"]    = "abc";
        $_GET["param_1_3_7"]    = "九〇一二";
        $key                    = "param_%_%_%";
        $matches    = [];
        preg_match("/\A([^%]+)_(%_)*%(\[\])?\z/u", $key, $matches);
        $prefix                 = $matches[1];
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::PATCH, "require" => true
        ];
        $response   = $this->get_response();
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("バリデーション設定内に記述された、キー [{$prefix}] の method [{$conditions["method"]}] は定義されていません。");
        VALIDATE::normal($this->ume, $key, $conditions, $response);
    }
    
    public function test_do_hierarchy_post()
    {
        $_POST["param_1_2_3"]    = "hoge";
        $_POST["param_1_2_4"]    = "hage";
        $_POST["param_1_3_1"]    = "abc";
        $_POST["param_1_3_7"]    = "def";
        $key                    = "param_%_%_%";
        $matches    = [];
        preg_match("/\A([^%]+)_(%_)*%(\[\])?\z/u", $key, $matches);
        $prefix                 = $matches[1];
        $conditions = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::POST, "require" => true
        ];
        $response   = $this->get_response();
        //
        VALIDATE::normal($this->ume, $key, $conditions, $response);
        $this->assertCount(1, $response->dist[$prefix]);
        $this->assertSame(false, $response->has_error);
        //
        $this->assertSame("hoge", $response->dist[$prefix][1][2][3]);
        $this->assertSame("hage", $response->dist[$prefix][1][2][4]);
        $this->assertSame("abc", $response->dist[$prefix][1][3][1]);
        $this->assertSame("def", $response->dist[$prefix][1][3][7]);
    }
    
    public function test_do_hierarchy_cookie()
    {
        $_COOKIE["param_1_2_3"]    = "hoge";
        $_COOKIE["param_1_2_4"]    = "hage";
        $_COOKIE["param_1_3_1"]    = "abc";
        $_COOKIE["param_1_3_7"]    = "def";
        $key                    = "param_%_%_%";
        $matches    = [];
        preg_match("/\A([^%]+)_(%_)*%(\[\])?\z/u", $key, $matches);
        $prefix                 = $matches[1];
        $conditions = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::COOKIE, "require" => true
        ];
        $response   = $this->get_response();
        //
        VALIDATE::normal($this->ume, $key, $conditions, $response);
        $this->assertCount(1, $response->dist[$prefix]);
        $this->assertSame(false, $response->has_error);
        //
        $this->assertSame("hoge", $response->dist[$prefix][1][2][3]);
        $this->assertSame("hage", $response->dist[$prefix][1][2][4]);
        $this->assertSame("abc", $response->dist[$prefix][1][3][1]);
        $this->assertSame("def", $response->dist[$prefix][1][3][7]);
    }
    
}
