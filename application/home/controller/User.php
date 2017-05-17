<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/4/20
 * Time: 15:34
 */

namespace app\home\controller;
use app\home\model\Comment;
use app\home\model\Learn;
use app\home\model\Years;
use app\home\model\Notice;
use app\home\model\Picture;
use app\home\model\Browse;
use app\home\model\Feedback;
use app\home\model\WechatUser;
use app\home\model\WechatUserTag;
use think\Controller;
use think\Db;
use com\wechat\TPQYWechat;
use think\Config;
/**
 * Class User
 * 用户个人中心
 */
class User extends Base {
    /**
     * 个人中心主页
     */
    public function index(){
        //是否游客登录
        $this->anonymous();
        $userId = session('userId');
        $user = WechatUser::where('userid',$userId)->find();
        $this->assign('user',$user);
        return $this->fetch();
    }

    /**
     * 个人信息页
     */
    public function personal(){
        $userId = session('userId');
        $user = WechatUser::where('userid',$userId)->find();
        switch ($user['gender']) {
            case 0:
                $user['sex'] = "未定义";
                break;
            case 1:
                $user['sex'] = "男";
                break;
            case 2:
                $user['sex'] = "女";
                break;
            default:
                break;
        }
        $Notice = new Notice();
        $map = array(
            'type' => array('in',[3,4]), // 活动通知 活动情况
            'status' =>array('egt',0)
        );
        $activityAll = $Notice->where($map)->count(); // 活动情况总数
        $maps = array(
            'type' => 2, // 情况报道
            'status' =>array('egt',0)
        );
        $partyAll = $Notice->where($maps)->count(); // 情况报道总数
        $Brower = new Browse();
        $map1 = array(
            'user_id' => $userId,
            'notice_id' => array('exp',"is not null")
        );
        $activity = $Brower->where($map1)->select(); // 浏览notice总记录
        $num1 = 0; // 活动情况数
        $num2 = 0; // 情况报道数
        foreach($activity as $value){
            $All = $Notice->where('id',$value['notice_id'])->find();
            // 判断具体的类型  活动情况 情况报道
            switch($All['type']){
                case 3 : // 活动通知
                    $num1++;
                    break;
                case 4 : // 活动情况
                    $num1++;
                    break;
                case 2 : // 情况报道
                    $num2++;
                    break;
            }
        }
        $user['activity'] = array(
            'all' => $activityAll,
            'num' => $num1,
        );
        $user['party'] = array(
            'all' => $partyAll,
            'num' => $num2
        );
        $this->assign('user',$user);

        return $this->fetch();
    }

    /**
     * 设置头像
     */
    public function setHeader(){
        $userId = session('userId');
        $header = input('header');
        $map = array(
            'header' => $header,
        );
        $info = WechatUser::where('userid',$userId)->update($map);
        if($info){
            return $this->success("修改成功");
        }else{
            return $this->error("修改失败");
        }
    }
    
    /*
     * 我的消息
     */
    public function history(){
        $userId = session('userId');
        $Year = new Years();
        $map = array(
            "userid" => $userId,
        );
        $list = $Year->where($map)->order(['create_time'=>'desc'])->select();
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 临时党员信息
     */
    public function eg() {
        $id = input('id');
        $user = WechatUser::where('userid',$id)->find();
        switch ($user['gender']) {
            case 0:
                $user['sex'] = "未定义";
                break;
            case 1:
                $user['sex'] = "男";
                break;
            case 2:
                $user['sex'] = "女";
                break;
            default:
                break;
        }
        $Notice = new Notice();
        $map = array(
            'type' => array('in',[3,4]), // 活动通知 活动情况
            'status' =>array('egt',0)
        );
        $activityAll = $Notice->where($map)->count(); // 活动情况总数
        $maps = array(
            'type' => 2, // 情况报道
            'status' =>array('egt',0)
        );
        $partyAll = $Notice->where($maps)->count(); // 情况报道总数
        $Brower = new Browse();
        $map1 = array(
            'user_id' => $id,
            'notice_id' => array('exp',"is not null")
        );
        $activity = $Brower->where($map1)->select(); // 浏览notice总记录
        $num1 = 0; // 活动情况数
        $num2 = 0; // 情况报道数
        foreach($activity as $value){
            $All = $Notice->where('id',$value['notice_id'])->find();
            // 判断具体的类型  活动情况 党课情况  会议情况
            switch($All['type']){
                case 3 : // 活动通知
                    $num1++;
                    break;
                case 4 : // 活动情况
                    $num1++;
                    break;
                case 2 : // 情况报道
                    $num2++;
                    break;
            }
        }
        $user['activity'] = array(
            'all' => $activityAll,
            'num' => $num1,
        );
        $user['party'] = array(
            'all' => $partyAll,
            'num' => $num2
        );
        $this->assign('user',$user);
        return $this->fetch();
    }
    /**
     * 积分商城
     */
    public function score() {
        return $this->fetch();
    }
    /**
     * 意见反馈
     */
    public function feedback() {
        return $this->fetch();
    }
    /*
     * 意见反馈  提交
     */
    public function feedbackup(){
        $data['content'] = input('post.content');
        $data['userid'] = session('userId');
        $Feedback = new Feedback();
        $res = $Feedback->save($data);
        if ($res){
            return $this->success('提交成功');
        }else{
            return $this->error('提交失败');
        }
    }
}