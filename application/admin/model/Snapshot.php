<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/23
 * Time: 16:46
 */

namespace app\admin\model;


use think\Model;

class Snapshot extends Model
{
    public function user() {
        return $this->hasOne("WechatUser","userid","uid");
    }
}