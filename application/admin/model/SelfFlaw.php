<?php
/**
 * Created by PhpStorm.
 * User: ali
 * Date: 2018/3/16
 * Time: 10:23
 */
namespace app\admin\model;


class SelfFlaw extends Base{
    public $autoWriteTimestamp=true;

    /**
     * 获取列表数据
     */
    public function get_list($where,$length=0){
        $list = $this->where($where)->order('create_time','desc')->limit($length,10)->select();
        foreach($list as $value){
            $value['create_time'] = date('Y-m-d',$value['create_time']);
            $value['front_cover'] = Picture::where('id',$value['front_cover'])->value('path');
        }
        return $list;
    }
}