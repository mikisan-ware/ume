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
use \mikisan\core\basis\ume\VALIDATOR;
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

class VALIDATOR_Test extends TestCase
{
    use TestCaseTrait;
    use UMETestCaseTrait;
    
    protected   $ume;

    public function setUp(): void
    {
        $this->ume              = new ChildUME();
    }
    
    /**
     * digitバリデートテスト
     */
    public function test_do_digit()
    {
        $resobj     = $this->get_resobj();
        $key        = "test";
        $conditions = [
            "type" => "digit", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $type   = $conditions["type"];
        //
        $value  = "1234567890";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame(true, $result);
        //
        $value  = "あいうえおー漢字－１２３４５６７８９０ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $expect = "あいうえおー漢字－1234567890ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame(false, $result);
        $this->assertSame("[テスト] には半角数字以外が含まれています。", $resobj->VE[0]);
        //
        $failcases  = ["１２３４５６７８９０", "-1234567890", "1,234,567,890", "1234-567890", "Ā", "ɑ", "#", "@", "Ⅲ", "⑤", "七"];
        foreach($failcases as $value)
        {
            $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
            $this->assertSame(false, $result);
            $this->assertSame("[テスト] には半角数字以外が含まれています。", $resobj->VE[0]);
        }
    }
    
    /**
     * alphabetバリデートテスト
     */
    public function test_do_alphabet()
    {
        $resobj     = $this->get_resobj();
        $key        = "test";
        $conditions = [
            "type" => "alphabet", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $type   = $conditions["type"];
        //
        $value = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame(true, $result);
        //
        $value  = "あいうえおー漢字－１２３４５６７８９０ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $expect ="あいうえおー漢字－１２３４５６７８９０ABCDEFGhijklmn<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame(false, $result);
        //
        $failcases  = ["ａｂｃｄｅｆｇ", "ＡＢＣＤＥＦＧ", "Ā", "ɑ", "#", "@", "＠", "Ω"];
        foreach($failcases as $value)
        {
            $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
            $this->assertSame(false, $result);
            $this->assertSame("[テスト] には英字以外が含まれています。", $resobj->VE[0]);
        }
    }
    
    /**
     * intバリデートテスト
     */
    public function test_do_int()
    {
        $resobj     = $this->get_resobj();
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $type   = $conditions["type"];
        //
        $value  = "1234567890";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame(true, $result);
        //
        $value  = "-1234567890";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame(true, $result);
        //
        $value  = "あいうえおー漢字－１２３４５６７８９０ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $expect ="あいうえお-漢字-1234567890ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）-＝「」＊!#$%&()=[]*";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
        $this->assertSame(false, $result);
        //
        $failcases  = ["１２３４５６７８９０", "-0123456789", "1,234,567,890", "1234-567890", "Ā", "ɑ", "#", "@", "Ⅲ", "⑤", "七"];
        foreach($failcases as $value)
        {
            $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
            $this->assertSame(false, $result);
            $this->assertSame("[テスト] は整数でなければなりません。", $resobj->VE[0]);
        }
    }
    
    /**
     * rule に callable 以外が設定されている場合の例外処理
     */
    public function test_do_rule_exception()
    {
        $resobj     = $this->get_resobj();
        $key        = "test";
        $conditions = [
            "type" => "wrong_rule", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $type   = $conditions["type"];
        $value  = "1234567890";
        //
        $data_type  = gettype(["something", "wrong", "type", "parameter"]);
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("バリデーション定義 [{$conditions["type"]}] の rule は正しい callable として定義されていません。");
        //
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
    }
    
    /**
     * バリデーション実行後の返り値が bool ではない場合の例外
     */
    public function test_do_return_exception()
    {
        $resobj     = $this->get_resobj();
        $key        = "test";
        $conditions = [
            "type" => "wrong_return", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $type   = $conditions["type"];
        $value  = "1234567890";
        //
        $data_type  = "string";
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("バリデーション定義 [{$conditions["type"]}] の rule の返り値が bool 型ではありません。[type: {$data_type}]");
        //
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
    }
    
    
    /**
     * error に callable 以外が設定されている場合の例外処理
     */
    public function test_do_error_exception()
    {
        $resobj     = $this->get_resobj();
        $key        = "test";
        $conditions = [
            "type" => "wrong_error", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $type   = $conditions["type"];
        $value  = "1234567890";
        //
        $data_type  = gettype(["something", "wrong", "type", "parameter"]);
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("バリデーション定義 [{$type}] の error は正しい callable　として定義されていません。");
        //
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $resobj);
    }
    
}
