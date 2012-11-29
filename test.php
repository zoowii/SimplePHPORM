<?php
var_dump(SimuChat::findAllByAttributes(array('has_read'=>'true'), 't.create_time asc', array('user')));
$chat = new SimuChat();
$chat->user_id=26;
$chat->to_user_id=33;
$chat->create_time = date('Y-m-j h:i:s', time());
$chat->content = '¹ş¹ş';
$chat->save();