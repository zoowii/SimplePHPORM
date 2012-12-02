<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 12-11-28
 * Time: 下午4:35
 * To change this template use File | Settings | File Templates.
 */
class SimuContact extends Model
{
    protected static $tableName = 'contacts';
    protected static $pkColumn = 'object_id';
    protected static $columnTypes = array(
        'object_id' => 'int',
        'first_name' => 'string',
        'surname' => 'string',
        'is_company' => 'int',
        'company_id' => 'int',
        'department' => 'string',
        'job_title' => 'string',
        'birthday' => 'datetime',
        'timezone' => 'float',
        'user_type' => 'int',
        'is_active_user' => 'int',
        'token' => 'string',
        'salt' => 'string',
        'twister' => 'string',
        'display_name' => 'string',
        'permission_group_id' => 'int',
        'username' => 'string',
        'contact_passwords_id' => 'int',
        'picture_file' => 'string',
        'avatar_file' => 'string',
        'comments' => 'string',
        'last_login' => 'datetime',
        'last_visit' => 'datetime',
        'last_activity' => 'datetime',
        'personal_member_id' => 'int',
        'disabled' => 'int',
        'default_billing_id' => 'int'
    );
	protected static $readOnlyColumns = array('object_id');
}
