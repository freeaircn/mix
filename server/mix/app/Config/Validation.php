<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-29 23:32:38
 */

namespace Config;

use CodeIgniter\Validation\CreditCardRules;
use CodeIgniter\Validation\FileRules;
use CodeIgniter\Validation\FormatRules;
use CodeIgniter\Validation\Rules;

class Validation
{
    //--------------------------------------------------------------------
    // Setup
    //--------------------------------------------------------------------

    /**
     * Stores self-define rules.
     *
     */
    public $AuthLogin = [
        'phone'    => 'required|regex_match[/^[1][3,4,5,7,8][0-9]{9}$/]',
        'password' => 'required|alpha_numeric',
    ];

    public $AuthSMS = [
        'phone' => 'required|regex_match[/^[1][3,4,5,7,8][0-9]{9}$/]',
    ];

    public $AuthResetPassword = [
        'phone'    => 'required|regex_match[/^[1][3,4,5,7,8][0-9]{9}$/]',
        'code'     => 'required|regex_match[^[1-9]\d{4}$]',
        'password' => 'required|alpha_numeric',
    ];

    // /u 表示按unicode(utf-8)匹配（主要针对多字节比如汉字）
    // 女 Unicode编码16进制 5973
    // 男 Unicode编码16进制 7537
    public $AccountUpdateUserInfo = [
        'username' => 'required|regex_match[/^([\x{4e00}-\x{9fa5}]{1,6})$/u]',
        'sex'      => 'required|regex_match[/^([\x{7537}\x{5973}]{1})$/u]',
        'IdCard'   => 'regex_match[/^([1-9]\d{5}(18|19|20|(3\d))\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]){0,1}$/]',
        'politic'  => 'required|regex_match[^[1-9]\d{0,4}$]',
        'job'      => 'required|regex_match[^[1-9]\d{0,4}$]',
        'title'    => 'required|regex_match[^[1-9]\d{0,4}$]',
    ];

    public $AccountUpdatePassword = [
        'password'    => 'required|alpha_numeric',
        'newPassword' => 'required|alpha_numeric',
    ];

    public $AccountUpdatePhone = [
        'password' => 'required|alpha_numeric',
        'phone'    => 'required|regex_match[/^[1][3,4,5,7,8][0-9]{9}$/]',
    ];

    public $AccountSMS = [
        'email' => 'required|regex_match[/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/]',
    ];

    public $AccountUpdateEmail = [
        'password' => 'required|alpha_numeric',
        'email'    => 'required|regex_match[/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/]',
        'code'     => 'required|regex_match[^[1-9]\d{4}$]',
    ];

    public $GeneratorEventNew = [
        'station_id'   => 'required|regex_match[^[1-9]\d{0,2}$]',
        'generator_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'event'        => 'required|regex_match[^[1-9]\d{0,2}$]',
        'timestamp'    => 'required|regex_match[^[1-9]\d{9}$]',
        'creator'      => 'required|regex_match[/^([\x{4e00}-\x{9fa5}]{1,6})$/u]',
    ];

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var string[]
     */
    public $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    //--------------------------------------------------------------------
    // Rules
    //--------------------------------------------------------------------
}