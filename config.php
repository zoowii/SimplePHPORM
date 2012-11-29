<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 12-11-26
 * Time: 下午2:02
 * To change this template use File | Settings | File Templates.
 */

include_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';

define('ROOT', __DIR__);
define('SIMU_COMPONENTS_PATH', ROOT . DIRECTORY_SEPARATOR . 'simu_components');

define('DB_ADAPTER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', 'test');
define('DB_USER', 'test');
define('DB_PASS', 'test');
define('DB_CHARSET', 'utf8')

include SIMU_COMPONENTS_PATH . DIRECTORY_SEPARATOR . 'SimuAjaxResponse.class.php';
include SIMU_COMPONENTS_PATH . DIRECTORY_SEPARATOR . 'Common.class.php';
include SIMU_COMPONENTS_PATH . DIRECTORY_SEPARATOR . 'SimuCache.class.php';
include SIMU_COMPONENTS_PATH . DIRECTORY_SEPARATOR . 'Simu.class.php';
include SIMU_COMPONENTS_PATH . DIRECTORY_SEPARATOR . 'Model.class.php';

autoload_dir(SIMU_COMPONENTS_PATH . DIRECTORY_SEPARATOR . 'models');

