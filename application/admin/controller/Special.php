<?php
namespace app\admin\controller;
use com\wechat\TPQYWechat;
use app\admin\model\Picture;
use app\admin\model\Push;
use think\Config;
use app\admin\model\Special as SpecialModel;
/**
 * Created by PhpStorm.
 * User: 老王
 * Date: 2017/7/17
 * Time: 14:09
 */
/*
  通知公告  控制器
*/
class Special extends Admin
{
    /*
     * 主页 管理
     */
    public function index(){
        $map = array(
            'status' => array('egt',0),
        );
        $list = $this->lists('Special',$map);
        int_to_string($list,array(
            'type' => [1 => "政策解读" , 2 => "通知公告"],
            'status' => array(0=>"待审核",1=>"已通过",2=>"未通过"),
            'recommend' => [0 => "否" , 1 => "是"],
            'push' => [0 => "否" , 1 => "是"]
        ));
        $this->assign('list',$list);
        return $this->fetch();
    }
    /*
     *内容 添加
     */
    public function add(){
        if(IS_POST) {
            $data = input('post.');
            $Model = new SpecialModel();
            if(empty($data['id'])) {
                unset($data['id']);
            }
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            $model = $Model->validate('Special')->save($data);
            if($model){
                if ($data['push'] == 1){
                    $this ->push($model['id'],$model['status']);//发布
                }
                $data = [
                    'focus_vice' => null,
                    'create_user' => session('user_auth.username'),
                    'focus_main' => $model['id'],
                    'class' => 1, // 通知公告
                ];
                //保存到推送列表
                Push::create($data);
                return $this->success('新增通知成功',Url('Special/index'));
            }else{
                return $this->get_update_error_msg($Model->getError());
            }
        }else{
            $msg = array();
            $msg['class'] = 1; // 1为添加 ，2为修改
            $this->assign('msg',$msg);
            return $this->fetch('edit');
        }
    }
    /*
     * 主题  内容 添加 修改
     */
    public function edit(){
        if(IS_POST) {
            $data = input('post.');
            $Model = new SpecialModel();
            $model = $Model->validate('Special')->save($data,['id'=> $data['id']]);
            if($model){
                if($data['push'] == 1) {
                    $this ->push($data['id'],$model['status']);//发布
                }
                return $this->success('修改通知成功',Url('Special/index'));
            }else{
                return $this->get_update_error_msg($Model->getError());
            }
        }else{
            $this->default_pic();
            $id = input('id');
            $msg = SpecialModel::get($id);
            $msg['class'] = 2;
            $this->assign('msg',$msg);

            return $this->fetch();
        }
    }
    /*
     * 内容 删除
     */
    public function del(){
        $id = input('id');
        if (empty($id)){
            return $this->error('系统参数错误,请重新选择');
        }
        $res = SpecialModel::where(['id' => $id])->update(['status' => -1]);
        if ($res){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }
    /*
     * 新闻推送
     */
    public function push($id,$status){
        if(empty($id)){
            return $this->error('请选择推送文章');
        }
        $info = SpecialModel::where('id',$id)->find();
        $str = strip_tags($info['content']);
        $des = mb_substr($str,0,40);
        $content = str_replace("&nbsp;","",$des);  //空格符替换成空
        $pre = '【通知公告】';
        $url = hostUrl."/home/Reviews/detail/class/1/id/".$info['id'].".html";
        $image = Picture::get($info['front_cover']);
        $path = hostUrl.$image['path'];
        $send = [
            'articles' => [
                [
                    'title' => $pre.$info['title'],
                    'description' => $content,
                    'url'  => $url,
                    'picurl' => $path
                ]
            ]
        ];
        //发送给企业号
        if ($status == 0){
            // 待审核
            $Wechat = new TPQYWechat(Config::get('review'));
            $message = array(
                "totag" => toTag,
                "msgtype" => 'news',
                "agentid" => config('review.agentid'),  // 消息审核
                "news" => $send,
                "safe" => "0"
            );
        }elseif ($status == 1){
            // 通过
            $Wechat = new TPQYWechat(Config::get('notice'));
            $message = array(
                "touser" => toUser,
                "msgtype" => 'news',
                "agentid" => config('notice.agentid'),  // 个人中心
                "news" => $send,
                "safe" => "0"
            );
        }
        $Wechat->sendMessage($message);  // 推送至审核
    }
}