<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/10/12
 * Time: 10:23
 */

namespace app\home\model;
use think\Model;

/**
 * Class Study
 * @package app\home\model  两学一做
 */
class Study extends Model {
    /**
     * 获取列表数据
     */
    public function get_list($where,$length=0,$opt=false){
        if ($opt){
            $num = 3;
        }else{
            $num = 10;
        }
        $list = $this->where($where)->order('create_time','desc')->limit($length,$num)->select();
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
    public function getDataList($length){
        $map = array(
            'recommend' => 1,
            'status' => ['egt',0],
        );
        $num = 4;
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
}