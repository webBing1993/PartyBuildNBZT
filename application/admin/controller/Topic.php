<?php
/**
 * Created by PhpStorm.
 * User: laowang
 * Date: 2018/7/3
 * Time: 下午3:05
 */

namespace app\admin\controller;
use app\admin\model\Topic as TopicModel;

/**
 * Class Topic
 * @package app\admin\controller
 * 专题模块
 */
class Topic extends Admin
{
    /*
     * 主题  管理
     */
    public function topic(){
        $map = array(
            'type' => 1, // 主题
            'status' => array('egt',0),
        );
        $list = $this->lists('Topic',$map);
        int_to_string($list,array(
            'status' => array(0=>"已发布",1=>"已发布"),
        ));

        $this->assign('list',$list);
        return $this->fetch();
    }
    /*
     * 主题  内容 添加
     */
    public function add(){
        $Model = new TopicModel();
        if(IS_POST) {
            $data = input('post.');
            if(empty($data['id'])) {
                unset($data['id']);
            }
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if ($data['type'] == 1){
                $model = $Model->validate('Topic.act')->save($data);
            }else{
                $model = $Model->validate('Topic.other')->save($data);
            }
            if($model){
                if ($data['type'] == 1){
                    return $this->success('新增主题成功',Url('Topic/topic'));
                }else if($data['type'] == 2){
                    return $this->success('新增内容成功',Url('Topic/index'));
                }
            }else{
                return $this->get_update_error_msg($Model->getError());
            }
        }else{
            $msg = array();
            $msg['type'] = input('type');
            $msg['class'] = 1; // 1为添加 ，2为修改
            $this->assign('msg',$msg);
            $topic = $Model->where(['type' => 1 ,'status' => ['egt',0]])->order('id')->select();
            $this->assign('topic',$topic);
            return $this->fetch('edit');
        }
    }
    /*
     * 主题  内容 添加 修改
     */
    public function edit(){
        $Model = new TopicModel();
        if(IS_POST) {
            $data = input('post.');
            if ($data['type'] == 1){
                $model = $Model->validate('Topic.act')->save($data,['id'=> $data['id']]);
            }else{
                $model = $Model->validate('Topic.other')->save($data,['id'=> $data['id']]);
            }
            if($model){
                if ($data['type'] == 1){
                    return $this->success('修改主题成功',Url('Topic/topic'));
                }else if($data['type'] == 2){
                    return $this->success('修改内容成功',Url('Topic/index'));
                }
            }else{
                return $this->get_update_error_msg($Model->getError());
            }
        }else{
            $this->default_pic();
            $id = input('id');
            $msg = TopicModel::get($id);
            $msg['class'] = 2;
            $msg['type'] = input('type');
            $topic = $Model->where(['type' => 1 ,'status' => ['egt',0]])->order('id')->select();
            $this->assign('topic',$topic);
            $this->assign('msg',$msg);

            return $this->fetch();
        }
    }
    /*
     * 内容 列表
     */
    public function index(){
        $map = array(
            'type' => 2, // 内容
            'status' => array('egt',0),
        );
        $list = $this->lists('Topic',$map);
        foreach ($list as $value){
            $value['t_id'] = TopicModel::where('id',$value['t_id'])->value('title');
        }
        int_to_string($list,array(
            'status' => array(0=>"已发布",1=>"已发布"),
        ));

        $this->assign('list',$list);
        return $this->fetch();
    }
    /*
     * 内容 删除
     */
    public function del(){
        $id = input('id');
        if (empty($id)){
            return $this->error('系统参数错误,请重新选择');
        }
        $res = TopicModel::where(['id' => $id])->update(['status' => -1]);
        if ($res){
            $topic = TopicModel::where('t_id',$id)->find();
            if (!empty($topic)){
                TopicModel::where('t_id',$id)->update(['status' => -1]);
            }
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }
}