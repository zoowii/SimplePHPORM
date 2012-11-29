<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 12-11-26
 * Time: 下午2:02
 * To change this template use File | Settings | File Templates.
 */
function ls_files_in_dir($dir, $recur = true)
{
    $d = opendir($dir);
    $files = array();
    while ($file = readdir($d)) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
            if ($recur) {
                $files += ls_files_in_dir($dir . DIRECTORY_SEPARATOR . $file);
            } else {
                continue;
            }
        } else {
            $files[] = $dir . DIRECTORY_SEPARATOR . $file;
        }
    }
    return $files;
}

function autoload_dir($dir, $recur = true)
{
    $files = ls_files_in_dir($dir, $recur);
    foreach ($files as $file) {
        require $file;
    }
}