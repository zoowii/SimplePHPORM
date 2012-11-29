<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 12-11-29
 * Time: 上午11:15
 * To change this template use File | Settings | File Templates.
 */
final class SimuCacheModel extends Model
{
    protected static $tableName = 'cache';
    protected static $pkColumn = 'id';
    protected static $columnTypes = array(
        'id' => 'int',
        'name' => 'string',
        'value' => 'string',
        'create_time' => 'timestamp'
    );

}
