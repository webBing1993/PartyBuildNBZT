<?php
/**
 * Created by PhpStorm.
 * User: stiff <1570004138@163.com>
 * Date: 2017/5/24
 * Time: 9:22
 */
namespace app\home\controller;
use think\Controller;
use app\user\controller\Index as APIIndex;
use com\wechat\TPQYWechat;

class Verify extends Controller{
    /**
     * 用户登入获取信息
     */
    public function login(){
        // 获取用户信息
        $Wechat = new TPQYWechat(config('user'));
        $result = $Wechat->getUserId(input('code'), config('user.agentid'));
        if(isset($result['UserId'])) {
            $user = $Wechat->getUserInfo($result['UserId']);
            $data = [
                'userid' => $user['userid'],
                'name' => $user['name'],
                'mobile' => $user['mobile'],
                'gender' => $user['gender'],
                'avatar' => $user['avatar'],
                'department' => json_encode($user['department']),
                'status' => $user['status'],
            ];
            if (isset($user['extattr'])){
                $data['extattr'] = json_encode($user['extattr']);
            }
            $data['order'] = (isset($user['order'])) ? json_encode($user['order']) : "";
            // 添加本地数据
            $UserAPI = new APIIndex();
            $localUser = $UserAPI->checkWechatUser($result['UserId']);
            if($localUser) {
                $UserAPI->updateWechatUser($data);
            } else {
                $UserAPI->addWechatUser($data);
            }
            session("userId", $result['UserId']);
            //存在url则跳转，不存在则回主页
            if(session('url')){
                $this->redirect(session('url'));
                session('url','');
            }else{
                $this->redirect("Index/index");
            }
        } else {
            // 用户不存在通讯录默认为游客，跳转到url;
            session('userId','visitor');
            $this->redirect(session('url'));
        }
    }
    public function null(){
        return $this->fetch();
    }

}