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
use \mikisan\core\basis\ume\VALUE;
use \mikisan\core\exception\UMEException;
//
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";
require_once __DIR__ . "/UMETestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class VALUE_Test extends TestCase
{
    use TestCaseTrait;
    use UMETestCaseTrait;
    
    protected   $ume;

    public function setUp(): void
    {
        $this->ume              = new ChildUME();
    }
    
    /***************************************************************************
     * text バリデートテスト
     */
    public function test_do_validate_text()
    {
        $key        = "test";
        $conditions = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        //
        $value      = "あいうえおー漢字－１２３４５６７８９０ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame($value, $result);
    }
    
    public function test_do_validate_not_required_empty_value()
    {
        $key        = "test";
        $conditions = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $value      = "";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame(false, $resobj->on_error);
        $this->assertSame("", $result);
    }
    
    public function test_do_validate_force_required_empty_value()
    {
        $key        = "test";
        $conditions = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => UME::FORCE_REQUIRED
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $value      = "";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame(true, $resobj->on_error);
        $this->assertSame("[$label] は必須項目です。", $resobj->VE[0]);
        $this->assertSame("", $result);
    }
    
    /**
     * text バリデートテスト choice内
     */
    public function test_do_validate_text_choice()
    {
        $key        = "test";
        $conditions = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "あいう|かきく",
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        //
        $value      = "あいう";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame($value, $result);
    }
    
    /**
     * text バリデートテスト choice外
     */
    public function test_do_validate_text_not_choice_1()
    {
        $key        = "test";
        $conditions = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "あいう|かきく",
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        //
        $value      = "あいうえ";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame("あいうえ", $result);
    }
    
    
    /**
     * text バリデートテスト choice外
     */
    public function test_do_validate_text_not_choice_2()
    {
        $key        = "test";
        $conditions = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "あいう|かきく",
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        //
        $value      = "さしす";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame("さしす", $result);
    }
    
    /**
     * text バリデートテスト trim
     */
    public function test_do_validate_text_trim()
    {
        $key        = "test";
        $conditions = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        //
        $value      = "  \r\n\0あいうえおー漢字－１２３ＡＢＣABC123!#$%&()=[]\0*\r\n  ";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame("あいうえおー漢字－１２３ＡＢＣABC123!#$%&()=[]*", $result);
    }
    
    /**
     * text バリデートテスト strip_null
     */
    public function test_do_validate_text_strip_null()
    {
        $key        = "test";
        $conditions = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        //
        $value      = "あいうえおー漢字－\0１２３ＡＢＣ\0\0ABC123!#$%&()=[]*";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame("あいうえおー漢字－１２３ＡＢＣABC123!#$%&()=[]*", $result);
    }
    
    /***************************************************************************
     * int バリデートテスト（オートコレクト有り）
     */
    public function test_do_validate_int_auto_correct()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        //
        $value      = "１２３123";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame(123123, $result);
    }
    
    /**
     * int バリデートテスト（オートコレクト無し）
     */
    public function test_do_validate_int_no_correct()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => false, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        //
        $value      = "１２３123";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame("１２３123", $result);
        $this->assertSame("[テスト] は整数でなければなりません。", $resobj->VE[0]);
    }
    
    public function test_do_validate_int_choice()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => [123, 456],
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        //
        $value      = "１２３";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame(123, $result);
    }
    
    
    /***************************************************************************
     * digit バリデートテスト（オートコレクト有り）
     */
    public function test_do_validate_digit_auto_correct()
    {
        $key        = "test";
        $conditions = [
            "type" => "digit", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        //
        $value      = "１２３123";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame("123123", $result);
    }
    
    /**
     * int バリデートテスト（オートコレクト無し）
     */
    public function test_do_validate_digit_no_correct()
    {
        $key        = "test";
        $conditions = [
            "type" => "digit", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => false, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        //
        $value      = "１２３123";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame("１２３123", $result);
        $this->assertSame("[テスト] には半角数字以外が含まれています。", $resobj->VE[0]);
    }
    
    public function test_do_validate_digit_choice()
    {
        $key        = "test";
        $conditions = [
            "type" => "digit", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "123|456",
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $type       = $conditions["type"];
        $resobj     = $this->get_resobj();
        //
        $value      = "１２３";
        $result     = VALUE::validate($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame("123", $result);
    }
    
}
