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
use \mikisan\core\basis\ume\CORRECTOR;
//
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

$project_root = realpath(__DIR__ . "/../../../../");
require "{$project_root}/vendor/autoload.php";
require_once "{$project_root}/tests/TestCaseTrait.php";
Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class CORRECTOR_Test extends TestCase
{
    use TestCaseTrait;
    
    protected   $types;
    
    public function setUp(): void
    {
        $ume            = new ChildUME();
        $this->types    = $ume->get_types();
    }
    
    /**
     * digitオートコレクトテスト
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
        $value  = "１２３４５６７８９０";
        $expect = "1234567890";
        $result = CORRECTOR::do($this->types[$type], $value, $conditions);
        $this->assertSame($expect, $result);
        //
        $value  = "あいうえおー漢字－１２３４５６７８９０ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $expect = "あいうえおー漢字－1234567890ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $result = CORRECTOR::do($this->types[$type], $value, $conditions);
        $this->assertSame($expect, $result);
    }
    
    /**
     * alphabetオートコレクトテスト
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
        $value  = "ａｂｃｄｅｆｇｈｉｊｋｌｍｎｏｐｑｒｓｔｕｖｗｘｙｚＡＢＣＤＥＦＧＨＩＪＫＬＭＮＯＰＱＲＳＴＵＶＷＸＹＺ";
        $expect = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $result = CORRECTOR::do($this->types[$type], $value, $conditions);
        $this->assertSame($expect, $result);
        //
        $value  = "あいうえおー漢字－１２３４５６７８９０ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $expect ="あいうえおー漢字－１２３４５６７８９０ABCDEFGhijklmn<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $result = CORRECTOR::do($this->types[$type], $value, $conditions);
        $this->assertSame($expect, $result);
    }
    
    /**
     * intオートコレクトテスト
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
        $value  = "１２３４５６７８９０";
        $expect = "1234567890";
        $result = CORRECTOR::do($this->types[$type], $value, $conditions);
        $this->assertSame($expect, $result);
        //
        $value  = "あいうえおー漢字－１２３４５６７８９０ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）－＝「」＊!#$%&()=[]*";
        $expect ="あいうえお-漢字-1234567890ＡＢＣＤＥＦＧｈｉｊｋｌｍｎ<script>alert(\"XSS!\");@＠ABC12345=！＃＄％＆（）-＝「」＊!#$%&()=[]*";
        $result = CORRECTOR::do($this->types[$type], $value, $conditions);
        $this->assertSame($expect, $result);
    }
    
}
