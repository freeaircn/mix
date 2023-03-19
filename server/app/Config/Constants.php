<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-08-19 17:30:49
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-19 23:00:48
 */

/*
| --------------------------------------------------------------------
| App Namespace
| --------------------------------------------------------------------
|
| This defines the default Namespace that is used throughout
| CodeIgniter to refer to the Application directory. Change
| this constant to change the namespace that all application
| classes should use.
|
| NOTE: changing this will require manually modifying the
| existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
| --------------------------------------------------------------------------
| Composer Path
| --------------------------------------------------------------------------
|
| The path that Composer's autoload file is expected to live. By default,
| the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
|--------------------------------------------------------------------------
| Timing Constants
|--------------------------------------------------------------------------
|
| Provide simple ways to work with the myriad of PHP functions that
| require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR') || define('HOUR', 3600);
defined('DAY') || define('DAY', 86400);
defined('WEEK') || define('WEEK', 604800);
defined('MONTH') || define('MONTH', 2592000);
defined('YEAR') || define('YEAR', 31536000);
defined('DECADE') || define('DECADE', 315360000);

/*
| --------------------------------------------------------------------------
| Exit Status Codes
| --------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
 */
defined('EXIT_SUCCESS') || define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') || define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') || define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') || define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') || define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') || define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') || define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') || define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') || define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/**
 * My Code
 */
defined('LOG_HEADER') || define('LOG_HEADER', '{file}:{line} -->'); //
//
defined('VALIDATE_NATURAL_NUMBER') || define('VALIDATE_NATURAL_NUMBER', 'is_natural'); //
defined('VALIDATE_ID') || define('VALIDATE_ID', 'is_natural_no_zero'); //
defined('VALIDATE_USERNAME') || define('VALIDATE_USERNAME', 'regex_match[/^([\x{4e00}-\x{9fa5}]{1,6})$/u]'); //
defined('VALIDATE_PHONE') || define('VALIDATE_PHONE', 'regex_match[/^[1][3,4,5,7,8][0-9]{9}$/]'); //
defined('VALIDATE_EMAIL') || define('VALIDATE_EMAIL', 'regex_match[/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/]'); //
defined('VALIDATE_ID_CARD') || define('VALIDATE_ID_CARD', 'regex_match[/^([1-9]\d{5}(18|19|20|(3\d))\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]){0,1}$/]'); //
defined('VALIDATE_PASSWORD') || define('VALIDATE_PASSWORD', 'regex_match[/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z\W]{8,32}$/]'); //
defined('VALIDATE_SMS_CODE') || define('VALIDATE_SMS_CODE', 'regex_match[/^[1-9]\d{4}$/]'); //
//
defined('VALIDATE_TITLE') || define('VALIDATE_TITLE', 'regex_match[/^([\x{4e00}-\x{9fa5}a-zA-Z0-9_-]{0,64})$/u]'); //
defined('VALIDATE_KEY_WORDS') || define('VALIDATE_KEY_WORDS', 'regex_match[/^([\x{4e00}-\x{9fa5}a-zA-Z0-9，,]{0,64})$/u]'); //
defined('VALIDATE_TEXT') || define('VALIDATE_TEXT', 'regex_match[/^([\x{4e00}-\x{9fa5}a-zA-Z0-9,.?:;，。？：；_-]{0,1000})$/u]'); //
defined('VALIDATE_DOC_NUM') || define('VALIDATE_DOC_NUM', 'regex_match[/^([a-zA-Z0-9-]{0,64})$/u]'); //
// defined('VALIDATE_') || define('VALIDATE_', 'regex_match[]'); //
