<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 12-11-28
 * Time: 上午1:59
 * To change this template use File | Settings | File Templates.
 */
class SimuChat extends Model
{
    protected static $tableName = 'chats';
    protected static $pkColumn = 'object_id';
    public $has_read = 'false'; // 默认值
    protected static $columnTypes = array(
        'object_id' => 'int',
        'user_id' => 'int',
        'to_user_id' => 'int',
        'content' => 'string',
        'create_time' => 'timestamp',
        'has_read' => 'enum',
        'object_type_id' => 'int'
    );
    protected static $relations = array(
        'user' => array('column' => 'user_id', 'model' => 'SimuContact', 'key' => 'object_id'),
        'to_user' => array('column' => 'to_user_id', 'model' => 'SimuContact', 'key' => 'object_id')
    );

}
