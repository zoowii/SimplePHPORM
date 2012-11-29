<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 12-11-27
 * Time: 上午10:48
 * To change this template use File | Settings | File Templates.
 */
class Common
{
    public static function  httpPost($url, array $postFiled)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_READDATA, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($postFiled) ? http_build_query($postFiled) : $postFiled);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    public static function getClientIp()
    {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return $ip;
    }

    /**
     * PHP截取UTF-8字符串，解决半字符问题。
     * 英文、数字（半角）为1字节（8位），中文（全角）为3字节
     * @static
     * @param $str 源字符串
     * @param $len 左边的子串的长度
     * @return string 取出的字符串, 当$len小于等于0时, 会返回整个字符串
     */
    public static function  utf_substr($str, $len)
    {
        for ($i = 0; $i < $len; $i++) {
            $temp_str = substr($str, 0, 1);
            if (ord($temp_str) > 127) {
                $i++;
                if ($i < $len) {
                    $new_str[] = substr($str, 0, 3);
                    $str = substr($str, 3);
                }
            } else {
                $new_str[] = substr($str, 0, 1);
                $str = substr($str, 1);
            }
        }
        return implode('', $new_str);
    }

    static function getIntervalTime($interval_seconds)
    {
        if ($interval_seconds < 30) {
            return "刚刚";
        }
        $minutes = floor($interval_seconds / 60);
        $hours = floor($minutes / 60);
        $days = floor($hours / 24);
        $months = floor($days / 30);
        $years = floor($days / 365);
        if ($years != 0) {
            return $years . "年前";
        }
        if ($months != 0) {
            return $months . '个月前';
        }
        if ($days != 0) {
            return $days . '天前';
        }
        if ($hours != 0) {
            return $hours . '小时前';
        }
        if ($minutes != 0) {
            return $minutes . '分钟前';
        }
        return "刚刚";
    }

    static function getIntervalTimeTillNow($timestamp)
    {
        return self::getIntervalTime(time() - $timestamp);
    }

    /**
     * @param $content
     * @return string
     */
    function unhtml($content)
    {
        $content = htmlspecialchars($content); //转换文本中的特殊字符
        $content = str_ireplace(chr(13), "<br>", $content); //替换文本中的换行符
        $content = str_ireplace(chr(32), " ", $content); //替换文本中的
        $content = str_ireplace("[_[", "<", $content); //替换文本中的小于号
        $content = str_ireplace(")_)", ">", $content); //替换文本中的大于号
        $content = str_ireplace("|_|", " ", $content); //替换文本中的空格
        $content = str_ireplace(array("\n", "\r\n"), "<br>", $content);
        return trim($content); //删除文本中首尾的空格
    }

    public static function ajaxRequireRequests($fields)
    {
        foreach ($fields as $field) {
            if (!isset($_REQUEST[$field])) {
                SimuAjaxResponse::send(SimuAjaxResponse::MISSING_PARAMS);
            }
        }
    }

    public static function requireRequests($fields)
    {
        foreach ($fields as $field) {
            if (!isset($_REQUEST[$field])) {
                var_dump('Missing param: ' . $field);
            }
        }
    }

    /**
     * 将数组$arr分成$count组，假设$arr长为7，$count=3，则0,3,6一组,1,4一组,2,5一组
     * @param $arr
     */
    public static function slice_array($arr, $count)
    {
        $result = array();
        for ($i = 0; $i < count($arr); $i++) {
            $result[$i % $count][] = $arr[$i];
        }
        return $result;
    }

    public static function useDefaultIfUndefinedOrNotInArray($default, $src_array, $property, $func)
    {
        if (!isset($src_array[$property])) {
            return $default;
        }
        $tmp = $src_array[$property];
        if (!$func($tmp)) {
            return $default;
        }
        return $tmp;
    }

    public static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public static function generateRandomString($length = 50)
    {
        $multi = $length % 40 + 1;
        $random_string = '';
        for ($i = 0; $i < $multi; ++$i) {
            $random_string .= sha1(rand(1, 100000) . $_SERVER['REQUEST_TIME'] . rand(1, 100000));
        }
        return substr($random_string, 0, $length);
    }

}
