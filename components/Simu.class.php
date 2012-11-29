<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 12-11-28
 * Time: 上午1:18
 * To change this template use File | Settings | File Templates.
 */
class Simu
{

    private static $instance = null;
    private $conn;
    private $cache = null;
    public function __construct()
    {
        // init db conn
        $dsn = DB_ADAPTER . ':host=' . DB_HOST . ';dbname=' . DB_NAME;
        $this->conn = new PDO($dsn, DB_USER, DB_PASS,  array(PDO::ATTR_PERSISTENT => true));
        $this->conn->exec('set names ' . DB_CHARSET);

        // init cache
        $this->cache = new SimuCache();
    }
    public static function init()
    {
        self::$instance = new Simu();
    }

    public static function db()
    {
         if(self::$instance == null) {
             self::init();
         }
        return self::$instance->conn;
    }

    public static function cache() {
        if(self::$instance == null) {
            self::init();
        }
        return self::$instance->cache;
    }

}
