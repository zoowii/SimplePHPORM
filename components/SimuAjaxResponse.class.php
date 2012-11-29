<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 12-11-27
 * Time: 上午10:26
 * To change this template use File | Settings | File Templates.
 */
class SimuAjaxResponse
{

    const SUCCESS = '0';
    const DB_ERROR = '301';
    const ACCESS_DENIED = '302';
    const LOGIN_ACQUIRED = '303';
    const ILLEGAL_PARAMS = '400';
    const MISSING_PARAMS = '401';
    private static $CODE_MSG = array(
        self::SUCCESS => 'success',
        self::DB_ERROR => 'db error',
        self::ACCESS_DENIED => 'access denied',
        self::LOGIN_ACQUIRED => 'login acquired',
        self::ILLEGAL_PARAMS => 'illegal params',
        self::MISSING_PARAMS => 'missing params'
    );

    public static function send($level, $data = null, $ended = true)
    {
        $str = '{"success":';
        if ($level === self::SUCCESS) {
            $str .= "true,\"code\":$level,\"message\":\"" . self::$CODE_MSG[$level] . "\"";
        } else {
            $str .= "false,\"code\":$level,\"message\":\"" . self::$CODE_MSG[$level] . "\"";
        }
        if ($data !== null) {
            $str .= "\"data\":" . json_encode($data);
        }
        $str .= '}';
        print $str;
        if ($ended) {
            exit;
        }
    }

}
