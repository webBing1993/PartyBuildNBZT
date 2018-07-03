<?php
/**
 * Created by PhpStorm.
 * User: laowang
 * Date: 2018/7/3
 * Time: 下午3:06
 */

namespace app\admin\model;


use think\Model;

class Topic extends Model
{
    protected $insert = [
        'likes' => 0,
        'views' => 0,
        'comments' => 0,
        'create_time' => NOW_TIME,
    ];
    //获取后台用户名称
    public function user(){
        return $this->hasOne('Member','id','create_user');
    }
}