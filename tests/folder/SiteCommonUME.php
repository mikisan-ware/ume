<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/02
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\pine\app;

use \mikisan\core\basis\dto\Dto;

abstract class SiteCommonUME extends \mikisan\core\basis\ume\BaseUME
{
    public function __construct(Dto $dto)
    {
        parent::__construct($dto);
        
        $this->site_common_types();
    }
    
    private function site_common_types(): void
    {
        $this->register_types([
            // mail
            "mail_jp, mail" =>  [
                "type"      => UME::TYPE_STRING,
                "correct"   => function($value){ return mb_convert_kana($value, "a", "UTF-8"); },
                "rule"      => function($value){ return $this->isValidEmailFormat($value); },
                "error"     => function($key, $conditions){ return "[{$conditions["name"]}] はメールアドレスの書式が間違っています。"; }
            ],
            // phone
            "phone_jp, phone" => [
                "type"      => UME::TYPE_STRING,
                "correct"   => function($value)
                {
                    $value  = mb_convert_kana($value, "n", "UTF-8");
                    $value  = $this->adjustHyphen($value);
                    return $value;
                },
                "rule"      => function($value)
                {
                    $phoneValid = "/\A(?!^(090|080|070))(?=^\d{2,5}?-\\d{1,4}?-\d{4}$)[\d-]{12}|";
                    $phoneValid .= "(?=^(090|080|070)-\d{4}-\d{4}$)[\d-]{13}|";
                    $phoneValid .= "(?=^0120-\d{2,3}-\d{3,4})[\d-]{12}|";
                    $phoneValid .= "^0800-\d{3}-\d{4}\z/u";
                    return preg_match($phoneValid, $value);
                },
                "error"     => function($key, $conditions){ return "[{$conditions["name"]}] は電話番号の書式が間違っています。"; }
            ],
            // zip
            "zip_jp, zip" => [
                "type"      => UME::TYPE_STRING,
                "correct"   => function($value)
                {
                    $value  = mb_convert_kana($value, "a", "UTF-8");
                    $value  = $this->adjustHyphen($value);
                    return $value;
                },
                "rule"      => function($value){ return preg_match("/\A\d{3}-\d{4}\z/", $value); },
                "error"     => function($key, $conditions){ return "[{$conditions["name"]}] は郵便番号の書式が間違っています。"; }
            ]
        ]);
    }
    
    /**
     * RFCに準拠しない日本の携帯メールアドレスにも対応したバリデーター
     * 
     * @copyright (c) http://wada811.blogspot.com/2013/03/best-email-format-check-regex-in-php.html
     * 
     * @param string    $email
     * @param boolean   $supportPeculiarFormat    // RFC準拠を強制するか？
     * @return boolean
     */
    protected function isValidEmailFormat($email, $supportPeculiarFormat = true): bool
    {
        $wsp              = '[\x20\x09]'; // 半角空白と水平タブ
        $vchar            = '[\x21-\x7e]'; // ASCIIコードの ! から ~ まで
        $quoted_pair      = "\\\\(?:{$vchar}|{$wsp})"; // \ を前につけた quoted-pair 形式なら \ と " が使用できる
        $qtext            = '[\x21\x23-\x5b\x5d-\x7e]'; // $vchar から \ と " を抜いたもの。\x22 は " , \x5c は \
        $qcontent         = "(?:{$qtext}|{$quoted_pair})"; // quoted-string 形式の条件分岐
        $quoted_string    = "\"{$qcontent}+\""; // " で 囲まれた quoted-string 形式。
        $atext            = '[a-zA-Z0-9!#$%&\'*+\-\/\=?^_`{|}~]'; // 通常、メールアドレスに使用出来る文字
        $dot_atom         = "{$atext}+(?:[.]{$atext}+)*"; // ドットが連続しない RFC 準拠形式をループ展開で構築
        $local_part       = "(?:{$dot_atom}|{$quoted_string})"; // local-part は dot-atom 形式 または quoted-string 形式のどちらか
        // ドメイン部分の判定強化
        $alnum            = '[a-zA-Z0-9]'; // domain は先頭英数字
        $sub_domain       = "{$alnum}+(?:-{$alnum}+)*"; // hyphenated alnum をループ展開で構築
        $domain           = "(?:{$sub_domain})+(?:[.](?:{$sub_domain})+)+"; // ハイフンとドットが連続しないように $sub_domain をループ展開
        $addr_spec        = "{$local_part}[@]{$domain}"; // 合成
        // 昔の携帯電話メールアドレス用
        $dot_atom_loose   = "{$atext}+(?:[.]|{$atext})*"; // 連続したドットと @ の直前のドットを許容する
        $local_part_loose = $dot_atom_loose; // 昔の携帯電話メールアドレスで quoted-string 形式なんてあるわけない。たぶん。
        $addr_spec_loose  = "{$local_part_loose}[@]{$domain}"; // 合成
        
        // 昔の携帯電話メールアドレスの形式をサポートするかで使う正規表現を変える
        $regexp = ($supportPeculiarFormat)
                        ? $addr_spec_loose
                        : $addr_spec
                        ;
        return preg_match("/\A{$regexp}\z/", $email);
    }
}
