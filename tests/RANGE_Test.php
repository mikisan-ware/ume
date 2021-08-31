<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/17
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\basis\ume\RANGE;
use \mikisan\core\exception\UMEException;
//
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class RANGE_Test extends TestCase
{
    use TestCaseTrait;
    
    protected $ume;

    public function setUp(): void
    {
        $this->ume      = new ChildUME();
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
        $response->index        = "";
        return $response;
    }
    
    /**
     * TYPE_INTEGER
     */
    public function test_isInRange_int()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = 123;
        
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(true, $result);
    }
    
    /**
     * TYPE_INTEGER under min
     */
    public function test_isInRange_int_under()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "int", "min" => 200, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = 123;
        $labels             = $this->ume->getLabels();
        $label              = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame("[{$label}] は {$conditions["min"]} 以上の整数にしてください。", $response->VE[$key]);
    }
    
    /**
     * TYPE_INTEGER over max
     */
    public function test_isInRange_int_orver()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "int", "min" => 200, "max" => 1000,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = 1001;
        $labels             = $this->ume->getLabels();
        $label              = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame("[{$label}] は {$conditions["max"]} 以下の整数にしてください。", $response->VE[$key]);
    }
    
    /**
     * TYPE_REAL
     */
    public function test_isInRange_real()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "double", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = 123.45;
        
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(true, $result);
    }
    
    /**
     * TYPE_REAL under min
     */
    public function test_isInRange_real_under()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "double", "min" => 123.45, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = 123.44;
        $labels             = $this->ume->getLabels();
        $label              = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame("[{$label}] は {$conditions["min"]} 以上の実数にしてください。", $response->VE[$key]);
    }
    
    /**
     * TYPE_REAL over max
     */
    public function test_isInRange_real_orver()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "double", "min" => 200, "max" => 1000.45,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = 1000.46;
        $labels             = $this->ume->getLabels();
        $label              = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame("[{$label}] は {$conditions["max"]} 以下の実数にしてください。", $response->VE[$key]);
    }
    
    /**
     * TYPE_STRING
     */
    public function test_isInRange_string()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = "123.ab";
        
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(true, $result);
    }
    
    /**
     * TYPE_STRING under min
     */
    public function test_isInRange_string_under()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "text", "min" => 7, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = "123.ab";
        $labels             = $this->ume->getLabels();
        $label              = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame("[{$label}] は {$conditions["min"]} 文字以上にしてください。", $response->VE[$key]);
    }
    
    /**
     * TYPE_STRING over max
     */
    public function test_isInRange_string_orver()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "text", "min" => 7, "max" => 8,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = "1000.46abc";
        $labels             = $this->ume->getLabels();
        $label              = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame("[{$label}] は {$conditions["max"]} 文字以下にしてください。", $response->VE[$key]);
    }
    
    /**
     * TYPE_FILE
     */
    public function test_isInRange_file_1()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "file", "min" => 0, "max" => 1024,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = [];
        $value["name"]      = "テストファイル.jpeg";
        $value["type"]      = "image/jpeg";
        $value["real_type"] = "image/jpeg";
        $value["tmp_name"]  = "test.jpeg";
        $value["error"]     = 0;
        $value["size"]      = 1024;
        $labels             = $this->ume->getLabels();
        $label              = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(true, $result);
    }
    
    public function test_isInRange_file_2()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "file", "min" => 0, "max" => "1KiB",
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = [];
        $value["name"]      = "テストファイル.jpeg";
        $value["type"]      = "image/jpeg";
        $value["real_type"] = "image/jpeg";
        $value["tmp_name"]  = "test.jpeg";
        $value["error"]     = 0;
        $value["size"]      = 1024;
        $labels             = $this->ume->getLabels();
        $label              = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(true, $result);
    }
    
    /**
     * TYPE_FILE under min
     */
    public function test_isInRange_file_under()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "file", "min" => 10, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = [];
        $value["name"]      = "テストファイル.jpeg";
        $value["type"]      = "image/jpeg";
        $value["real_type"] = "image/jpeg";
        $value["tmp_name"]  = "test.jpeg";
        $value["error"]     = 0;
        $value["size"]      = 9;
        $labels             = $this->ume->getLabels();
        $label              = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame("[{$label}] のファイルサイズが小さ過ぎます。許容されているファイルサイズは 10 Bytes です。", $response->VE[$key]);
    }
    
    /**
     * TYPE_FILE over max
     */
    public function test_isInRange_file_orver_1()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "file", "min" => 0, "max" => 1024,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = [];
        $value["name"]      = "テストファイル.jpeg";
        $value["type"]      = "image/jpeg";
        $value["real_type"] = "image/jpeg";
        $value["tmp_name"]  = "test.jpeg";
        $value["error"]     = 0;
        $value["size"]      = 1025;
        $labels             = $this->ume->getLabels();
        $label              = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame("[{$label}] のファイルサイズが大き過ぎます。許容されているファイルサイズは 1024 Bytes です。", $response->VE[$key]);
    }
    
    /**
     * TYPE_FILE over max
     */
    public function test_isInRange_file_orver_2()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "file", "min" => 0, "max" => "1KiB",
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = [];
        $value["name"]      = "テストファイル.jpeg";
        $value["type"]      = "image/jpeg";
        $value["real_type"] = "image/jpeg";
        $value["tmp_name"]  = "test.jpeg";
        $value["error"]     = 0;
        $value["size"]      = 1025;
        $labels             = $this->ume->getLabels();
        $label              = $labels["ja_JP"][$key] ?? $key;
        //
        $result             = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
        //
        $this->assertSame(false, $result);
        $this->assertSame("[{$label}] のファイルサイズが大き過ぎます。許容されているファイルサイズは 1KiB です。", $response->VE[$key]);
    }
    
    /**
     * TYPE_FILE wrong size unit sepcified
     */
    public function test_isInRange_file_wrong_unit_sepcified()
    {
        $response           = $this->get_response();
        $key                = "test";
        $conditions         = [
            "type" => "file", "min" => 0, "max" => "1FiB",
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value              = [];
        $value["name"]      = "テストファイル.jpeg";
        $value["type"]      = "image/jpeg";
        $value["real_type"] = "image/jpeg";
        $value["tmp_name"]  = "test.jpeg";
        $value["error"]     = 0;
        $value["size"]      = 1025;
        $labels             = $this->ume->getLabels();
        $label              = $labels["ja_JP"][$key] ?? $key;
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("[{$label}] で指定されたファイルサイズの単位が不正です。[FIB]");
        //
        $result             = RANGE::isInRange($this->ume, $value, $key, $conditions, $response);
    }
    
    
}
