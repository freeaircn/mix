<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-10-04 19:44:47
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
        'event_at'     => 'required|regex_match[/^[1-2]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s+([0-1]\d|20|21|22|23):[0-5]\d:[0-5]\d$/]',
        'creator'      => 'required|regex_match[/^([\x{4e00}-\x{9fa5}]{1,6})$/u]',
    ];

    public $GeneratorEventUpdate = [
        'id'           => 'required|regex_match[^[1-9]\d{0,10}$]',
        'station_id'   => 'required|regex_match[^[1-9]\d{0,2}$]',
        'generator_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'event'        => 'required|regex_match[^[1-9]\d{0,2}$]',
        'event_at'     => 'required|regex_match[/^[1-2]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s+([0-1]\d|20|21|22|23):[0-5]\d:[0-5]\d$/]',
        'creator'      => 'required|regex_match[/^([\x{4e00}-\x{9fa5}]{1,6})$/u]',
    ];

    public $GeneratorEventGet = [
        'station_id'   => 'required|regex_match[^[1-9]\d{0,2}$]',
        'generator_id' => 'required|regex_match[^[0-9]\d{0,2}$]',
        'date'         => 'required|valid_date[Y-m-d]',
        'limit'        => 'required|regex_match[^[1-9]\d{0,2}$]',
        'offset'       => 'required|regex_match[^[1-9]\d{0,9}$]',
    ];

    public $GeneratorEventDelete = [
        'id'           => 'required|regex_match[^[1-9]\d{0,10}$]',
        'station_id'   => 'required|regex_match[^[1-9]\d{0,2}$]',
        'generator_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
    ];

    public $GeneratorEventExport = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'date'       => 'required|valid_date[Y-m-d]',
    ];

    public $MeterLogsNew = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'log_date'   => 'required|valid_date[Y-m-d]',
        'log_time'   => 'required|regex_match[/^([0-1]\d|20|21|22|23):[0-5]\d:[0-5]\d$/]',
        'creator'    => 'required|regex_match[/^([\x{4e00}-\x{9fa5}]{1,6})$/u]',
    ];

    public $MeterLogsGet = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'date'       => 'required|valid_date[Y-m-d]',
        'type'       => 'required|in_list[month]',
        'limit'      => 'required|regex_match[^[1-9]\d{0,2}$]',
        'offset'     => 'required|regex_match[^[1-9]\d{0,9}$]',
    ];

    public $MeterLogsDelete = [
        'id'         => 'required|regex_match[^[1-9]\d{0,10}$]',
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'log_date'   => 'required|valid_date[Y-m-d]',
        'log_time'   => 'required|regex_match[/^([0-1]\d|20|21|22|23):[0-5]\d:[0-5]\d$/]',
    ];

    public $MeterDailyReportGet = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'log_date'   => 'required|valid_date[Y-m-d]',
        'log_time'   => 'required|regex_match[/^([0-1]\d|20|21|22|23):[0-5]\d:[0-5]\d$/]',
    ];

    public $MetersPlanningKWhGet = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'date'       => 'required|valid_date[Y-m-d]',
    ];

    public $MeterPlanningKWhRecordUpdate = [
        'id'         => 'required|regex_match[^[1-9]\d{0,10}$]',
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'year'       => 'required|valid_date[Y]',
        'month'      => 'required|regex_match[/^([1-9]|10|11|12)$/]',
        'planning'   => 'required',
        'deal'       => 'required',
    ];

    public $MeterBasicStatisticGet = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
    ];

    public $DtsDraftPost = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'type'       => 'required|regex_match[^[1-9]$]',
        'level'      => 'required|regex_match[^[1-9]$]',
    ];

    public $DtsTicketsGet = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'limit'      => 'required|regex_match[^[1-9]\d{0,2}$]',
        'offset'     => 'required|regex_match[^[1-9]\d{0,9}$]',
    ];

    public $DtsGetHandler = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'place'      => 'required|in_list[post, check, review, resolve, close, suspend, reject]',
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
