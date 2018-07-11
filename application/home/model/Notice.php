<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/10/12
 * Time: 10:23
 */

namespace app\home\model;


use think\Model;

class Notice extends Model {
    //首页获取已推送的数据
    public function get_list($where,$length=0){
        $list = $this->where($where)->order('create_time','desc')->limit($length,12)->select();
        foreach($list as $value){
            $value['create_time'] = date('Y-m-d',$value['create_time']);
            $value['front_cover'] = Picture::where('id',$value['front_cover'])->value('path');
        }
        return $list;
    }

    /**
     * 首页获取推荐的数据
     * @param $length
     * @param string $push 推送数据获取
     */
    public function getDataList($length,$status=0,$opt=false){
        if ($opt){
            $map = array(
                'status' => 1,
                'type' => 3,
                'recommend' =>1
            );
            $num = 2;
        }else{
            if ($status == 0){
                $map = array(
                    'userid' => ['neq',''],
                    'status' => 0,
                    'type' => 3
                );
            }else{
                $map = array(
                    'userid' => ['neq',''],
                    'status' => ['egt',$status],
                    'type' => 3
                );
            }
            $num = 4;
        }
        $order = 'create_time desc';
        $limit = "$length,$num";
        $list = $this ->where($map) ->order($order) ->limit($limit) ->select();
        if(!empty($list))
        {
            return $list[0] ->data;
        }else{
            return $list;
        }
    }
    //获取后台用户名称
    public function user(){
        return $this->hasOne('Member','id','create_user');
    }

    public function name(){
        return $this->hasOne('WechatUser','userid','userid');
    }
}