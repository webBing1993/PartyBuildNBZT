<?php
/**
 * Created by PhpStorm.
 * User: 老王
 * Date: 2016/11/2
 * Time: 13:21
 */
namespace app\home\controller;
use app\home\model\WechatDepartment;
use app\home\model\WechatDepartmentUser;
use app\home\model\WechatUser;

class Structure extends Base{
    /*
     * 组织架构主页
     */
    public function index(){
        $departments = WechatDepartment::where([])->order('order','asc')->field('id,name')->select();
        $this->assign('msg',$departments);
        return $this->fetch();
    }
    /*
     * 组织架构详情页
     */
    public function detail(){
        $id = input('id/d');
        $depart_name = WechatDepartment::where(['id' => $id])->value('name');
        $users = WechatDepartmentUser::where(['departmentid' => $id])->select();
        foreach ($users as $key => $value){
            $list = WechatUser::where(['userid' => $value['userid']])->field('name,position,mobile')->find();
            $users[$key]['name'] = $list['name'];
            $users[$key]['position'] = $list['position'];
            $users[$key]['mobile'] = $list['mobile'];
        }

        $this->assign('list',$users);
        $this->assign('name',$depart_name);
        return $this->fetch();
    }
}
