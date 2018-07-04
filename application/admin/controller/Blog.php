<?php
/**
 * Created by PhpStorm.
 * User: laowang <958364865@qq.com>
 * Date: 2017/10/12
 * Time: 13:42
 */

namespace app\admin\controller;
use app\admin\model\WechatDepartment;
use app\admin\model\WechatDepartmentUser;
use app\admin\model\WechatUser;
use app\admin\model\Blog as BlogModel;
/**
 * Class Blog
 * @package app\admin\controller  工作日志 
 */
class Blog extends Admin
{
    /**
     * 首页
     */
    public function index(){
        $map = array(
            'status' => array('egt',0),
        );
        $list = $this->lists('Blog',$map);
        foreach($list as $value){
            $value['name'] = WechatUser::where(['userid' => $value['userid']])->value('name');  // 发布人
            $position = WechatUser::where(['userid' => $value['userid']])->value('position'); // 职位;
            if ($position){
                $value['position'] = $position;
            }else{
                $value['position'] = "暂无";
            }
            $depart_id = WechatDepartmentUser::where(['userid' => $value['userid']])->value('departmentid');
            $value['depart'] = WechatDepartment::where(['id' => $depart_id])->value('name'); // 部门
            $value['create_time'] = date('Y-m-d H:i',$value['create_time']);
        }
        $this->assign('list',$list);

        return $this->fetch();
    }
    /**
     * 查看详情
     */
    public function detail(){
        $id = input('get.id/d');
        $msg = BlogModel::where(['id' => $id,'status' => 0])->find();
        if (empty($msg)){
            return $this->error('系统参数错误');
        }
        $msg['description_pic'] = !empty($msg['description_pic']) ? json_decode($msg['description_pic'],true) : null;
        $this->assign('msg',$msg);
        return $this->fetch();
    }
    /**
     * 删除
     */
    public function del(){
        $id = input('get.id/d');
        if (empty($id)){
            return $this->error('系统参数错误');
        }
        $res = BlogModel::where(['id' => $id])->update(['status' => -1]);
        if ($res){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }
}