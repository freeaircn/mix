<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-28 22:26:11
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
        'password' => 'required',
    ];

    public $AuthSMS = [
        'phone' => 'required|regex_match[/^[1][3,4,5,7,8][0-9]{9}$/]',
    ];

    public $AuthResetPassword = [
        'phone'    => 'required|regex_match[/^[1][3,4,5,7,8][0-9]{9}$/]',
        'code'     => 'required|regex_match[^[1-9]\d{4}$]',
        'password' => 'required',
    ];

    // /u 表示按unicode(utf-8)匹配（多字节比如汉字）
    // 女 Unicode编码16进制 5973
    // 男 Unicode编码16进制 7537
    public $AccountUpdateBasicSetting = [
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

    // 2022-4-29
    public $GeneratorEventReqStatistic = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'date'       => 'required|valid_date[Y]',
    ];
    // 2022-4-29

    public $GeneratorEventNewRecord = [
        'station_id'   => 'required|regex_match[^[1-9]\d{0,2}$]',
        'generator_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'event'        => 'required|regex_match[^[1-9]\d{0,2}$]',
        'cause'        => 'required|regex_match[^[1-9]\d{0,2}$]',
        'event_at'     => 'required|regex_match[/^[1-2]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s+([0-1]\d|20|21|22|23):[0-5]\d:[0-5]\d$/]',
        'creator'      => 'required|regex_match[/^([\x{4e00}-\x{9fa5}]{1,6})$/u]',
    ];

    public $GeneratorEventUpdateRecord = [
        'id'           => 'required|regex_match[^[1-9]\d{0,10}$]',
        'station_id'   => 'required|regex_match[^[1-9]\d{0,2}$]',
        'generator_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'event'        => 'required|regex_match[^[1-9]\d{0,2}$]',
        'event_at'     => 'required|regex_match[/^[1-2]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s+([0-1]\d|20|21|22|23):[0-5]\d:[0-5]\d$/]',
        'creator'      => 'required|regex_match[/^([\x{4e00}-\x{9fa5}]{1,6})$/u]',
    ];

    public $GeneratorEventReqList = [
        'station_id'   => 'required|regex_match[^[1-9]\d{0,2}$]',
        'generator_id' => 'required|regex_match[^[0-9]\d{0,2}$]',
        'event'        => 'required|regex_match[^[0-9]\d{0,2}$]',
        'description'  => 'required|regex_match[^[0-9]\d{0,2}$]',
        // 'date'         => 'required|valid_date[Y-m-d]',
        'limit'        => 'required|regex_match[^[1-9]\d{0,2}$]',
        'offset'       => 'required|regex_match[^[1-9]\d{0,9}$]',
    ];

    public $GeneratorEventDelRecord = [
        'id'           => 'required|regex_match[^[1-9]\d{0,10}$]',
        'station_id'   => 'required|regex_match[^[1-9]\d{0,2}$]',
        'generator_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
    ];

    public $GeneratorEventExportExcel = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'date'       => 'required|valid_date[Y-m-d]',
    ];

    public $GeneratorEventSyncToKKX = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'date'       => 'required|valid_date[Y-m-d]',
    ];

    public $MeterNewRecord = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'log_date'   => 'required|valid_date[Y-m-d]',
        'log_time'   => 'required|regex_match[/^([0-1]\d|20|21|22|23):[0-5]\d:[0-5]\d$/]',
        'creator'    => 'required|regex_match[/^([\x{4e00}-\x{9fa5}]{1,6})$/u]',
    ];

    public $MeterReqList = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        // 'date'       => 'required',
        // 'date'       => 'required|valid_date[Y-m-d]',
        // 'type'       => 'required|in_list[month]',
        'limit'      => 'required|regex_match[^[1-9]\d{0,2}$]',
        'offset'     => 'required|regex_match[^[1-9]\d{0,9}$]',
    ];

    public $MeterDelRecord = [
        'id'         => 'required|regex_match[^[1-9]\d{0,10}$]',
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'log_date'   => 'required|valid_date[Y-m-d]',
        'log_time'   => 'required|regex_match[/^([0-1]\d|20|21|22|23):[0-5]\d:[0-5]\d$/]',
    ];

    public $MeterReqDailyReport = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'log_date'   => 'required|valid_date[Y-m-d]',
        'log_time'   => 'required|regex_match[/^([0-1]\d|20|21|22|23):[0-5]\d:[0-5]\d$/]',
    ];

    // 2021-11-08
    public $MeterGetDailyStatistic = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
    ];

    public $MeterReqPlanAndDeal = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'date'       => 'required|valid_date[Y-m-d]',
    ];

    public $MeterUpdatePlanAndDealRecord = [
        'id'         => 'required|regex_match[^[1-9]\d{0,10}$]',
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'year'       => 'required|valid_date[Y]',
        'month'      => 'required|regex_match[/^([1-9]|10|11|12)$/]',
        'planning'   => 'required',
        'deal'       => 'required',
    ];

    public $MeterReqRecordDetail = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'log_date'   => 'required|valid_date[Y-m-d]',
        'log_time'   => 'required|regex_match[/^([0-1]\d|20|21|22|23):[0-5]\d:[0-5]\d$/]',
    ];

    public $MeterReqStatisticCharts = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'date'       => 'required|valid_date[Y]',
    ];

    public $MeterReqStatisticOverall = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
    ];

    public $DtsReqDeviceList = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
    ];

    public $DtsUploadAttachment = [
        'dts_id'     => 'required|is_natural',
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
    ];

    public $DtsDelAttachment = [
        'id'     => 'required|is_natural_no_zero',
        'dts_id' => 'required|is_natural',
    ];

    public $DtsDownloadAttachment = [
        'id'     => 'required|is_natural_no_zero',
        'dts_id' => 'required|is_natural',
    ];

    public $DtsCreateOne = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'type'       => 'required|regex_match[^[1-9]$]',
        'level'      => 'required|regex_match[^[1-9]$]',
    ];

    public $DtsReqNews = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
    ];

    public $DtsReqList = [
        'station_id' => 'required|regex_match[^\d{0,2}$]',
        'type'       => 'required|regex_match[^\d{0,1}$]',
        'level'      => 'required|regex_match[^\d{0,1}$]',
        'device'     => 'required|regex_match[^\d{0,10}$]',
        'cause'      => 'required|regex_match[^\d{0,2}$]',
        'score'      => 'required|regex_match[^\d{0,1}$]',
        // 'dts_id'     => 'regex_match[/^[1-9]\d{0,19}$/]',
        // 'creator'    => 'regex_match[/^([\x{4e00}-\x{9fa5}]{1,6})$/u]',
        'limit'      => 'required|regex_match[^[1-9]\d{0,2}$]',
        'offset'     => 'required|regex_match[^[1-9]\d{0,9}$]',
    ];

    public $DtsReqDetails = [
        'dts_id' => 'required|regex_match[/^[1-9]\d{0,19}$/]',
    ];

    public $DtsReqStatisticChartData = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
    ];

    public $DtsReqAttachmentsList = [
        'station_id' => 'required|regex_match[^\d{0,2}$]',
        'limit'      => 'required|regex_match[^[1-9]\d{0,2}$]',
        'offset'     => 'required|regex_match[^[1-9]\d{0,9}$]',
    ];

    public $DtsDeleteOne = [
        'dts_id' => 'required|regex_match[/^[1-9]\d{0,19}$/]',
    ];

    public $DtsUpdateEntry = [
        'resource' => 'required',
        'dts_id'   => 'required|regex_match[/^[1-9]\d{0,19}$/]',
    ];

    public $DtsHandlerPut = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'ticket_id'  => 'required|regex_match[/^\d{2}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])[0-9]{3}$/]',
        'handler'    => 'required|is_natural_no_zero',
    ];

    public $DtsToReviewPost = [
        'station_id' => 'required|regex_match[^[1-9]\d{0,2}$]',
        'ticket_id'  => 'required|regex_match[/^\d{2}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])[0-9]{3}$/]',
        'reviewer'   => 'required|is_natural_no_zero',
    ];

    // Drawing
    public $DrawingReqList = [
        'station_id'  => 'required|regex_match[^\d{0,2}$]',
        'category_id' => 'required|is_natural',
        'dwg_name'    => 'regex_match[/^([\x{4e00}-\x{9fa5}a-zA-Z0-9_-]{0,60})$/u]',
        'dwg_num'     => 'regex_match[/^([a-zA-Z0-9-]{0,30})$/u]',
        'keywords'    => 'regex_match[/^([\x{4e00}-\x{9fa5}a-zA-Z0-9，,]{0,60})$/u]',
        'limit'       => 'required|regex_match[^[1-9]\d{0,2}$]',
        'offset'      => 'required|regex_match[^[1-9]\d{0,9}$]',
    ];

    public $DrawingUploadFile = [
        'key' => 'required|in_list[create, update]',
        'id'  => 'required|is_natural',
    ];

    public $DrawingDeleteFile = [
        'key'           => 'required|in_list[create, update]',
        'id'            => 'required|is_natural_no_zero',
        'file_org_name' => 'required',
    ];

    public $DrawingCreateOne = [
        'station_id'  => 'required|regex_match[^[1-9]\d{0,2}$]',
        'category_id' => 'required|is_natural_no_zero',
        'dwg_name'    => 'required|regex_match[/^([\x{4e00}-\x{9fa5}a-zA-Z0-9_-]{0,60})$/u]',
        'dwg_num'     => 'required|regex_match[/^([a-zA-Z0-9-]{0,30})$/u]',
        'keywords'    => 'required|regex_match[/^([\x{4e00}-\x{9fa5}a-zA-Z0-9，,]{0,60})$/u]',
        'info'        => 'regex_match[/^([\x{4e00}-\x{9fa5}a-zA-Z0-9,.，。]{0,1000})$/u]',
    ];

    public $DrawingDownloadFile = [
        'id'            => 'required|is_natural_no_zero',
        'file_org_name' => 'required',
    ];

    public $DrawingReqDetails = [
        'id' => 'required|is_natural_no_zero',
    ];

    public $DrawingReqEdit = [
        'id' => 'required|is_natural_no_zero',
    ];

    public $DrawingUpdateOne = [
        'id'          => 'required|is_natural_no_zero',
        'station_id'  => 'required|regex_match[^[1-9]\d{0,2}$]',
        'category_id' => 'required|is_natural_no_zero',
        'dwg_name'    => 'required|regex_match[/^([\x{4e00}-\x{9fa5}a-zA-Z0-9_-]{0,60})$/u]',
        'dwg_num'     => 'required|regex_match[/^([a-zA-Z0-9-]{0,30})$/u]',
        'keywords'    => 'required|regex_match[/^([\x{4e00}-\x{9fa5}a-zA-Z0-9，,]{0,60})$/u]',
        'info'        => 'regex_match[/^([\x{4e00}-\x{9fa5}a-zA-Z0-9,.，。]{0,1000})$/u]',
    ];

    public $DrawingDeleteOne = [
        'id' => 'required|is_natural_no_zero',
    ];

    // 2023-2-26
    public $PartyBranchCreateOne = [
        'station_id'       => 'required|' . VALIDATE_ID,
        'category_id'      => 'required|' . VALIDATE_ID,
        'title'            => 'required|' . VALIDATE_TITLE,
        'keywords'         => 'required|' . VALIDATE_KEY_WORDS,
        'secret_level'     => 'required|' . VALIDATE_ID,
        'retention_period' => 'required|' . VALIDATE_ID,
        'store_place'      => VALIDATE_TEXT,
        'summary'          => VALIDATE_TEXT,
    ];

    public $PartBranchReqList = [
        'station_id'  => 'required|' . VALIDATE_ID,
        'category_id' => 'required|' . VALIDATE_NATURAL_NUMBER,
        'title'       => VALIDATE_TITLE,
        'keywords'    => VALIDATE_KEY_WORDS,
        'limit'       => 'required|' . VALIDATE_ID,
        'offset'      => 'required|' . VALIDATE_ID,
    ];

    public $PartBranchDetails = [
        'uuid' => 'required|' . VALIDATE_ID,
    ];

    public $PartyBranchUploadFile = [
        'id'    => 'required|' . VALIDATE_ID,
        'op'    => 'required',
        'title' => 'required|' . VALIDATE_TITLE,
    ];

    public $PartyBranchDownloadFile = [
        'id'            => 'required|' . VALIDATE_ID,
        'file_org_name' => 'required',
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
