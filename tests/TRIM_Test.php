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
use \mikisan\core\basis\ume\TRIM;
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class TRIM_Test extends TestCase
{
    use TestCaseTrait;
    
    protected $filters;

    public function setUp(): void
    {
        $ume            = new ChildUME();
        $this->filters  = $ume->get_filters();
    }
    
    /**
     * TRIM_ALL のテスト
     */
    public function test_do_trim_all()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value      = "\n\v  \n\t\r\n\n\0\nあいう\n\v  \n\t\r\n\n\0\nお漢字\n\v  \n\t\r\n\n\0\n";
        
        $result     = TRIM::do($value, $conditions);
        $this->assertSame("あいう\n\v  \n\t\r\n\n\0\nお漢字", $result);
    }
    
    /**
     * TRIM_LEFT のテスト
     */
    public function test_do_trim_left()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_LEFT, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value      = "\n\v  \n\t\r\n\n\0\nあいう\n\v  \n\t\r\n\n\0\nお漢字\n\v  \n\t\r\n\n\0\n";
        
        $result     = TRIM::do($value, $conditions);
        $this->assertSame("あいう\n\v  \n\t\r\n\n\0\nお漢字\n\v  \n\t\r\n\n\0\n", $result);
    }
    
    /**
     * TRIM_RIGHT のテスト
     */
    public function test_do_trim_right()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_RIGHT, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value      = "\n\v  \n\t\r\n\n\0\nあいう\n\v  \n\t\r\n\n\0\nお漢字\n\v  \n\t\r\n\n\0\n";
        
        $result     = TRIM::do($value, $conditions);
        $this->assertSame("\n\v  \n\t\r\n\n\0\nあいう\n\v  \n\t\r\n\n\0\nお漢字", $result);
    }
    
    /**
     * TRIM_NONE のテスト
     */
    public function test_do_trim_none()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_NONE, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value      = "\n\v  \n\t\r\n\n\0\nあいう\n\v  \n\t\r\n\n\0\nお漢字\n\v  \n\t\r\n\n\0\n";
        
        $result     = TRIM::do($value, $conditions);
        $this->assertSame("\n\v  \n\t\r\n\n\0\nあいう\n\v  \n\t\r\n\n\0\nお漢字\n\v  \n\t\r\n\n\0\n", $result);
    }
    
}
