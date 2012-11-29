<?php
class SimuCache
{
    public function set($name, $value)
    {
        if ($name === null) {
            return false;
        }
        $cache = SimuCacheModel::findOne("`name`='" . $name . "'");
        if ($cache != null) {
            $cache->value = $value;
            return $cache->update();
        }
        $cache = new SimuCacheModel();
        $cache->name = $name;
        $cache->value = $value;
        $cache->create_time = date('Y-n-j h:i:s', time());
        return $cache->save();
    }

    public function get($name)
    {
        if ($name === null) {
            return false;
        }
        $cache = SimuCacheModel::findOne("`name`='" . $name . "'");
        if ($cache != null) {
            if ((time() - strtotime($cache->create_time)) < 3600) { // expire: 3600 seconds
                return $cache->value;
            } else {
                $cache->delete();
                return null;
            }
        } else {
            return null;
        }
    }
}
