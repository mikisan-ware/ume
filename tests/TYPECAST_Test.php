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
use \mikisan\core\basis\ume\TYPECAST;
//
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);
Autoload::forceload("mikisan\core\basis\ume\BaseUME");

class TYPECAST_Test extends TestCase
{
    use TestCaseTrait;
    
    protected   $ume;
    protected   $response;

    public function setUp(): void {}
    
    /**
     * TYPE_INTEGERキャストテスト
     */
    public function test_do_type_integer()
    {
        $type   = UME::TYPE_INTEGER;
        //
        $value  = "1234567890";
        $result = TYPECAST::do($type, $value);
        $this->assertSame(1234567890, $result);
        //
        $value  = "abcあいう漢字１２３４５６";
        $result = TYPECAST::do($type, $value);
        $this->assertSame(0, $result);
        //
        $value  = "123456abcあいう漢字";
        $result = TYPECAST::do($type, $value);
        $this->assertSame(123456, $result);
    }
    
    /**
     * TYPE_REALキャストテスト
     */
    public function test_do_type_real()
    {
        $type   = UME::TYPE_REAL;
        //
        $value  = "123.45";
        $result = TYPECAST::do($type, $value);
        $this->assertSame(123.45, $result);
        //
        $value  = "abcあいう漢字１２３．４５";
        $result = TYPECAST::do($type, $value);
        $this->assertSame(0.0, $result);
        //
        $value  = "123.45abcあいう漢字";
        $result = TYPECAST::do($type, $value);
        $this->assertSame(123.45, $result);
    }
    
    /**
     * TYPE_STRINGキャストテスト
     */
    public function test_do_type_string()
    {
        $type   = UME::TYPE_STRING;
        //
        $value  = 12345;
        $result = TYPECAST::do($type, $value);
        $this->assertSame("12345", $result);
        //
        $value  = 123.45;
        $result = TYPECAST::do($type, $value);
        $this->assertSame("123.45", $result);
        //
        $value  = "abcあいう漢字１２３．４５";
        $result = TYPECAST::do($type, $value);
        $this->assertSame($value, $result);
        //
        $value  = "123.45abcあいう漢字";
        $result = TYPECAST::do($type, $value);
        $this->assertSame($value, $result);
    }
    
}
