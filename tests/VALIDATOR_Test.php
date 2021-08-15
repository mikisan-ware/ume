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
//
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

$project_root = realpath(__DIR__ . "/../../../../");
require "{$project_root}/vendor/autoload.php";
require_once "{$project_root}/tests/TestCaseTrait.php";
Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class VALIDATOR_Test extends TestCase
{
    use TestCaseTrait;
    
    protected   $ume;
    protected   $response;

    public function setUp(): void
    {
        $this->ume              = new ChildUME();
        $this->response         = new \stdClass();
        $this->response->VE     = [];
    }
    
    /**
     * digitバリデートテスト
     */
    public function test_do_digit()
    {
        $key        = "test";
        $conditions = [
            "type" => "digit", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $type   = $conditions["type"];
        //
        $value  = "1234567890";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $this->response);
        $this->assertSame(true, $result);
        //
        $value  = "あいうえおー漢字－１２３４５６７８９０ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $expect = "あいうえおー漢字－1234567890ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $this->response);
        $this->assertSame(false, $result);
        $this->assertSame("[テスト] には数字以外が含まれています。", $this->response->VE["test"]);
        //
        $failcases  = ["１２３４５６７８９０", "-1234567890", "1,234,567,890", "1234-567890", "Ā", "ɑ", "#", "@", "Ⅲ", "⑤", "七"];
        foreach($failcases as $value)
        {
            $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $this->response);
            $this->assertSame(false, $result);
            $this->assertSame("[テスト] には数字以外が含まれています。", $this->response->VE["test"]);
        }
    }
    
    /**
     * alphabetバリデートテスト
     */
    public function test_do_alphabet()
    {
        $key        = "test";
        $conditions = [
            "type" => "alphabet", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $type   = $conditions["type"];
        //
        $value = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $this->response);
        $this->assertSame(true, $result);
        //
        $value  = "あいうえおー漢字－１２３４５６７８９０ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $expect ="あいうえおー漢字－１２３４５６７８９０ABCDEFGhijklmn<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $this->response);
        $this->assertSame(false, $result);
        //
        $failcases  = ["ａｂｃｄｅｆｇ", "ＡＢＣＤＥＦＧ", "Ā", "ɑ", "#", "@", "＠", "Ω"];
        foreach($failcases as $value)
        {
            $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $this->response);
            $this->assertSame(false, $result);
            $this->assertSame("[テスト] には英字以外が含まれています。", $this->response->VE["test"]);
        }
    }
    
    /**
     * intバリデートテスト
     */
    public function test_do_int()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $type   = $conditions["type"];
        //
        $value  = "1234567890";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $this->response);
        $this->assertSame(true, $result);
        //
        $value  = "-1234567890";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $this->response);
        $this->assertSame(true, $result);
        //
        $value  = "あいうえおー漢字－１２３４５６７８９０ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $expect ="あいうえお-漢字-1234567890ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）-＝「」＊!#$%&()=[]*";
        $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $this->response);
        $this->assertSame(false, $result);
        //
        $failcases  = ["１２３４５６７８９０", "-0123456789", "1,234,567,890", "1234-567890", "Ā", "ɑ", "#", "@", "Ⅲ", "⑤", "七"];
        foreach($failcases as $value)
        {
            $result = VALIDATOR::do($this->ume, $value, $key, $conditions, $this->response);
            $this->assertSame(false, $result);
            $this->assertSame("[テスト] は整数でなければなりません。", $this->response->VE["test"]);
        }
    }
    
}
