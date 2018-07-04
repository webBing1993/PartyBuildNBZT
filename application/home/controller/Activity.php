<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/10
 * Time: 9:03
 */

namespace app\home\controller;

use app\home\model\Notice;
use think\Db;
use com\wechat\TPQYWechat;
use think\Config;
use app\home\model\Picture;
class Activity extends Base
{
    /**
     * @return mixed  主页
     */
    public function index()
    {
        $Notice = new Notice();
        $mapp = ['status' => ['eq', 1], 'type' => 1];
        $leftone = Db::table('pb_notice')->where($mapp)->order('create_time desc')->limit(2)->select();//活动安排
        $mapp = ['status' => ['eq', 1], 'type' => 2];//活动展示
        $lefttwo = $Notice->get_list($mapp);
        $mapp = ['status' => ['eq', 1], 'type' => 3];//会议纪要
        $center = $Notice->get_list($mapp);
        //循环遍历
        foreach ($center as $v) {
            $list = Db::table('pb_wechat_user')->where('userid', $v['userid'])->find();
            $v['userid'] = $list['name'];
        }
        $mapp = ['status' => ['eq', 1], 'type' => 4];//固定活动
        $right = $Notice->get_list($mapp);
        $this->assign('leftone', $leftone); // 活动安排
        $this->assign('lefttwo', $lefttwo); // 活动展示
        $this->assign('center', $center); // 会议纪要
        $this->assign('right', $right);  // 固定活动
        return $this->fetch();
    }

    /*
    *  更多活动
    */
    public function morelist()
    {
        $Notice = new Notice();
        $mapp = ['status' => ['eq', 1], 'type' => 1];//活动展示
        $info = $Notice->get_list($mapp);

        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
     * 详情页
     */
    public function detail2()
    {
        $this->anonymous();
        $this->jssdk();
        $id = input('id/d');
        $info = $this->content(4, $id);

        if ($info['images']) {
            $info['images'] = json_decode($info['images']);
        }
        $list = Db::table('pb_wechat_user')->where('userid', $info['userid'])->find();
        $info['userid'] = $list['name'];
        $this->assign('detail', $info);
        return $this->fetch();

    }

    public function detail()
    {
        $this->anonymous();
        $this->jssdk();
        $id = input('id/d');
        $info = $this->content(4, $id);
        
        $this->assign('detail', $info);
        return $this->fetch();

    }

    /**
     * 列表加载更多
     */
    public function more()
    {
        $Notice = new Notice();
        $len = input('length');
        $c = input('type');
        //dump($c);exit();
        if ($c == 3) {
            $type = 1;  //活动安排
        } elseif ($c == 0) {
            $type = 2; //活动展示
        } elseif ($c == 1){
            $type = 3;//会议纪要
        }elseif($c==2){
            $type =4;//固定活动
        }
        $map = ['status' => ['eq', 1], 'type' => $type];
        $list = $Notice->get_list($map, $len);
        foreach ($list as $v) {
            $list2 = Db::table('pb_wechat_user')->where('userid', $v['userid'])->find();
            $v['userid'] = $list2['name'];
        }
        if ($list) {
            return $this->success('加载成功', '', $list);
        } else {
            return $this->error('加载失败');
        }
    }

    /*
     *  发布和填写
     */
    public function publish()
    {
        if (IS_POST) {
            $userId = session('userId');
            $data = input('post.');
            if (!empty($data['images'])) {
                $data['images'] = json_encode($data['images']);
                $data['front_cover'] = $this->default_pic();
            } else {
                $data['front_cover'] = $this->default_pic();
            }
            $data['userid'] = $userId;
            $data['start_time'] = strtotime($data['start_time']);
            $data['create_time'] = strtotime(date("Y-m-d H:i:s"));
            $res = Db::table('pb_notice')->insert($data);
            if ($res) {
                // 推送
                $id = Db::name('notice')->getLastInsID();
                $str = strip_tags($data['content']);
                $des = mb_substr($str,0,40);
                $content = str_replace("&nbsp;","",$des);  //空格符替换成空
                $pre = '【会议纪要】';
                $url ="/home/review/detail/id/";
                $url = hostUrl.$url.$id.".html";
                $image = Picture::get($data['front_cover']);
                $path = hostUrl.$image['path'];
                $send = array(
                    "articles" => array(
                        array(
                            'title' => $pre.$data['title'],
                            'description' => $content,
                            'url'  => $url,
                            'picurl' => $path
                        )
                    )
                );
                $message = array(
                    "totag" => toTag,  // 审核组
                    "msgtype" => 'news',
                    "agentid" => Config::get('review.agentid'),
                    "news" => $send,
                );
                //发送给企业号
                $Wechat = new TPQYWechat(Config::get('review'));
                $msg = $Wechat->sendMessage($message);
                if($msg['errcode'] == 0){
                    return $this->success('发送成功');
                }else{
                    $this->error($Wechat->errMsg);
                }
            } else {
                return $this->error('发布失败！');
            }
        } else {
            return $this->fetch();
        }
    }
}