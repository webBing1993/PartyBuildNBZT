<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/27
 * Time: 11:02
 */
namespace app\home\controller;

use app\home\model\Picture;
use app\home\model\Company;
use app\home\model\Companyst;
use app\home\model\Companys;
use think\Db;
use com\wechat\TPQYWechat;
use app\home\model\WechatUserTag;
use app\home\model\WechatUser;
use think\Config;

class Volunteer extends Base
{
    /*
     *  志愿之家主页
     * */
    public function index()
    {

        return $this->fetch();

    }

    /*
     *  志愿风采展
     * */
    public function mien()
    {
        $Companyst = new Companyst();
        $mapp = ['status' => ['eq', 1]];
        $data = $Companyst->get_list($mapp);
        //循环遍历
        foreach ($data as $v) {
            $list = Db::table('pb_companys')->where('type', $v['id'])->where('status',1)->count();
            $v['number'] = $list;
        }
        $this->assign('data', $data);
        return $this->fetch();
    }

    /*
     *  团队介绍
     * */
    public function team()
    {
        $id = input('id');
        $detail = Db::table('pb_companyst')->where('id', $id)->find();
        
        $this->assign('detail', $detail);
        return $this->fetch();
    }

    /*
     *  团队风采
     * */
    public function recruit()
    {
        $type = input('pid');
        $list = Db::table('pb_companys')->where('type', $type)->where('status', 1)->order('create_time desc')->limit(10)->select();
        $list2 = Db::table('pb_companys')->where('type', $type)->where('status', 1)->where('istop', 1)->order('create_time desc')->limit(3)->select();
        
        $this->assign('list2', $list2);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 志愿者风采活动页面加载更多
     */
    public function lomore()
    {
        $Companys = new Companys();
        $len = input('length');
        $type = input('pid');
        $map = ['status' => ['eq', 1], 'type' => ['eq', $type]];
        $list = $Companys->get_list($map, $len);
        if ($list) {
            return $this->success('加载成功', '', $list);
        } else {
            return $this->error('加载失败');
        }
    }

    /*
     *  详情
     * */
    public function detail()
    {
        $this->anonymous();
        $this->jssdk();
        $id = input('id/d');
        $info = $this->content(8, $id);
        $this->assign('detail', $info);
        return $this->fetch();
    }

    /**
     * 志愿者风采主题加载更多
     */
    public function more()
    {
        $Companyst = new Companyst();
        $len = input('length');
        $type = input('type');
        $map = ['status' => ['eq', 1]];
        $list = $Companyst->get_list($map, $len);
        foreach ($list as $v) {
            $list2 = Db::table('pb_companys')->where('type', $v['id'])->count();
            $v['number'] = $list2;
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
            $data['front_cover'] = $this->default_pic();
            $data['userid'] = $userId;
            $data['start_time'] = strtotime($data['start_time']);
            $data['create_time'] = strtotime(date("Y-m-d H:i:s"));
            $data['status'] = 0;
            $res = Db::table('pb_company')->insert($data);
            if ($res) {
                // 推送
                $id = Db::name('company')->getLastInsID();
                $str = strip_tags($data['content']);
                $des = mb_substr($str, 0, 40);
                $content = str_replace("&nbsp;", "", $des);  //空格符替换成空
                if ($data['type'] == 1) {
                    $pre = '【微心愿】';
                } else {
                    $pre = '【志愿招募】';
                }
                $url = "/home/review/details/id/";
                $url = "http://" . $_SERVER['HTTP_HOST'] . $url . $id . ".html";
                $image = Picture::get($data['front_cover']);
                $path = "http://" . $_SERVER['HTTP_HOST'] . $image['path'];
                $information = array(
                    'title' => $pre . $data['title'],
                    'description' => $content,
                    'url' => $url,
                    'picurl' => $path
                );
                $send = array(
                    "articles" => array(
                        0 => $information
                    )
                );
                $message = array(
                    "totag" => 1,  // 审核组
//                    "touser" => "17557289172",
                    "msgtype" => 'news',
                    "agentid" => 1000004,
                    "news" => $send,
                );
                //发送给企业号
                $Wechat = new TPQYWechat(Config::get('review'));
                $msg = $Wechat->sendMessage($message);
                if ($msg['errcode'] == 0) {
                    return $this->success('发送成功');
                } else {
                    $this->error($Wechat->errMsg);
                }
            } else {
                return $this->error('发布失败！');
            }
        } else {
            return $this->fetch();
        }
    }

    /*
     * 点亮微心愿
     * */
    public function wishes()
    {
        $Company = new Company();
        $mapp = ['status' => ['eq', 1], 'type' => 1];
        $data = $Company->get_list($mapp);
        foreach ($data as $v) {
            $list = Db::table('pb_company_recruit')->where('rid', $v['id'])->count();
            $v['receive_number'] = $list;
            if ($v['receive_number']==$v['demand_number']){
                $v['v']=1;
            }else{
                $v['v']=0;
            }
        }
        $type = 1;
        $this->assign('type', $type);
        $this->assign('data', $data);
        return $this->fetch();
    }

    /**
     * 微心愿加载更多
     */
    public function vomore()
    {
        $Company = new Company();
        $len = input('length');
        $type = input('type');
        $map = ['status' => ['eq', 1], 'type' => $type];
        $list = $Company->get_list($map, $len);
        foreach ($list as $v) {
            $list2 = Db::table('pb_company_recruit')->where('rid', $v['id'])->count();
            $v['receive_number'] = $list2;
        }
        if ($list) {
            return $this->success('加载成功', '', $list);
        } else {
            return $this->error('加载失败');
        }
    }


    //心愿详情页
    public function wishesdetail()
    {
        $userId = session('userId');
        $id = input('id');
        $data11 = Db::table('pb_company')->where('id', $id)->find();
        $data12 = Db::table('pb_company_recruit')->where('rid', $id)->count();
        if ($data11['demand_number'] == $data12) {
            $data = Db::table('pb_company')->where('id', $id)->find();
            if (!empty($data['image'])) {
                $list5 = Db::table('pb_picture')->where('id', $data['image'])->find();
                $data['image'] = $list5['path'];
            }
            $list = Db::table('pb_company_recruit')->where('rid', $id)->select();
            foreach ($list as $key => $v) {
                $list2 = Db::table('pb_wechat_user')->where('userid', $v['userid'])->find();
                $list[$key]['userid'] = $list2['name'];
                $list[$key]['image'] = $list2['avatar'];
            }
            $data['receive_number'] = Db::table('pb_company_recruit')->where('rid', $id)->count();
            $m=5;
            //dump($data);exit();
            $this->assign('m',$m);
            $this->assign('list', $list);
            $this->assign('data', $data);
            return $this->fetch('volunteer/wishesdetail2');
        } else {
            $list3 = Db::table('pb_company_recruit')->where('rid', $id)->where('userid', $userId)->select();
            if (empty($list3)) {
                $data = Db::table('pb_company')->where('id', $id)->find();
                if (!empty($data['image'])) {
                    $list5 = Db::table('pb_picture')->where('id', $data['image'])->find();
                    $data['image'] = $list5['path'];
                }
                $list = Db::table('pb_company_recruit')->where('rid', $id)->select();
                foreach ($list as $key => $v) {
                    $list2 = Db::table('pb_wechat_user')->where('userid', $v['userid'])->find();
                    $list[$key]['userid'] = $list2['name'];
                    $list[$key]['image'] = $list2['avatar'];
                }
                $data12 = Db::table('pb_company_recruit')->where('rid', $id)->count();
                $data['receive_number']=$data12;
                $this->assign('list', $list);
                $this->assign('data', $data);
                return $this->fetch();
            } else {
                $data = Db::table('pb_company')->where('id', $id)->find();
                if (!empty($data['image'])) {
                    $list5 = Db::table('pb_picture')->where('id', $data['image'])->find();
                    $data['image'] = $list5['path'];
                }
                $list = Db::table('pb_company_recruit')->where('rid', $id)->select();
                foreach ($list as $key => $v) {
                    $list2 = Db::table('pb_wechat_user')->where('userid', $v['userid'])->find();
                    $list[$key]['userid'] = $list2['name'];
                    $list[$key]['image'] = $list2['avatar'];
                }
                $data['receive_number'] = Db::table('pb_company_recruit')->where('rid', $id)->count();
                $m=3;
                $this->assign('m',$m);
                $this->assign('list', $list);
                $this->assign('data', $data);
                return $this->fetch('volunteer/wishesdetail2');
            }
        }
    }
    //心愿领取
    public function receive()
    {
        $data1 = input('rid');
        $userId = session('userId');
        $data = ['rid' => $data1, 'userid' => $userId];
        $data['create_time'] = strtotime(date('Y-m-d H:i:s'));
        $list = Db::table('pb_company_recruit')->insert($data);
        $list2 = Db::table('pb_wechat_user')->where('userid', $userId)->find();
        $list2['create_time'] = date('Y-m-d H:i');
        if ($list2) {
            return $this->success('领取成功!', '', $list2);
        } else {
            return $this->error('领取失败!');
        }
    }

    /*
     * 志愿者招募
     * */
    public function enlist()
    {
        $Company = new Company();
        $mapp = ['status' => ['eq', 1], 'type' => 2];
        $data = $Company->get_list($mapp);
        foreach ($data as $v) {
            $list = Db::table('pb_company_recruit')->where('rid', $v['id'])->count();
            $v['receive_number'] = $list;
            if ($v['receive_number']==$v['demand_number']){
                $v['v']=1;
            }else{
                $v['v']=0;
            }
        }
        $userId = session('userId');
        $list = Db::table('pb_wechat_user_tag')->where('tagid',4)->where('userid', $userId)->count();
        if ($list==1){
            $xs=11;
        }else{
            $xs=22;
        }
        $type = 2;
        $this->assign('xs',$xs);
        $this->assign('type', $type);
        $this->assign('data', $data);
        return $this->fetch();
    }

    /*
      * 志愿者排行
      * */
    public function rank()
    {
        $this->anonymous();
        $userId = session('userId');
        // 获取  党员志愿者
        $map = array(
            'tagid' => 2,
        );
        $mouth = date('m', time());
        $arr1 = array(); // 月榜
        $arr2 = array(); // 总榜
        $list = WechatUserTag::where($map)->select();
        foreach ($list as $key => $value) {
            $User = WechatUser::where('userid', $value->userid)->find();
            if (!empty($User)) {
                if($userId == $value['userid']) {
                    $type = 1;//党员志愿者
                }
                // 月积分
                $map = array(
                    "userid" => $value['userid'],
                    "mouth" => $mouth,
                    'type' => 2,
                    'status' => 0,
                );
                // 签到积分
                $lists = Db::name('apply')->where($map)->select();
                $score1 = 0;
                foreach ($lists as $v) {
                    $score1 += $v['score'];
                }
                // 干预分数
                $info = Db::name('handle')->where(['userid' => $value['userid'], 'mouth' => $mouth])->select();
                $score2 = 0;
                foreach ($info as $v) {
                    $score2 += $v['score'];
                }
                // 总分
                $score3 = $User['volunteer_base'] + $User['volunteer_score'];
                $arr1[$key]['name'] = $User['name']; // 名字
                $arr1[$key]['avatar'] = $User['avatar']; // 头像
                $arr1[$key]['mouth'] = $score1 + $score2; // 月榜
                $arr1[$key]['type'] = $type; 
                $arr2[$key]['type'] = $type; 
                $arr2[$key]['name'] = $User['name']; // 名字
                $arr2[$key]['avatar'] = $User['avatar']; // 头像
                $arr2[$key]['all'] = $score3; // 总榜
            } else {
                unset($list[$key]);
            }
        }
        // 冒泡排序  大数向前排列
        for ($i = 1; $i < count($arr1); $i++) {
            for ($k = 0; $k < count($arr1) - $i; $k++) {
                if ($arr1[$k]['mouth'] < $arr1[$k + 1]['mouth']) {
                    $temp = $arr1[$k + 1];
                    $arr1[$k + 1] = $arr1[$k];
                    $arr1[$k] = $temp;
                }
            }
        }
        for ($i = 1; $i < count($arr2); $i++) {
            for ($k = 0; $k < count($arr2) - $i; $k++) {
                if ($arr2[$k]['all'] < $arr2[$k + 1]['all']) {
                    $temp = $arr2[$k + 1];
                    $arr2[$k + 1] = $arr2[$k];
                    $arr2[$k] = $temp;
                }
            }
        }
        $this->assign('list', $arr1);
        $this->assign('lists', $arr2);
        // 获取  群众志愿者
        $map2 = array(
            'tagid' => 3,
        );
        $arr3 = array(); // 月榜
        $arr4 = array(); // 总榜
        $list2 = WechatUserTag::where($map2)->select();
        foreach ($list2 as $key => $value) {
            $User2 = WechatUser::where('userid', $value->userid)->find();
            if (!empty($User2)) {
                if($userId == $value['userid']) {
                    $type = 2;//群众志愿者
                }
                // 月积分
                $map3 = array(
                    "userid" => $value['userid'],
                    "mouth" => $mouth,
                    'type' => 2,
                    'status' => 0,
                );
                // 签到积分
                $lists2 = Db::name('apply')->where($map3)->select();
                $score1 = 0;
                foreach ($lists2 as $v) {
                    $score1 += $v['score'];
                }
                // 干预分数
                $info = Db::name('handle')->where(['userid' => $value['userid'], 'mouth' => $mouth])->select();
                $score2 = 0;
                foreach ($info as $v) {
                    $score2 += $v['score'];
                }
                // 总分
                $score3 = $User2['volunteer_base'] + $User2['volunteer_score'];
                $arr3[$key]['name'] = $User2['name']; // 名字
                $arr3[$key]['avatar'] = $User2['avatar']; // 头像
                $arr3[$key]['mouth'] = $score1 + $score2; // 月榜
                $arr3[$key]['type'] = $type; 
                $arr4[$key]['type'] = $type; 
                $arr4[$key]['name'] = $User2['name']; // 名字
                $arr4[$key]['avatar'] = $User2['avatar']; // 头像
                $arr4[$key]['all'] = $score3; // 总榜
            } else {
                unset($list[$key]);
            }
        }
        // 冒泡排序  大数向前排列
        for ($i = 1; $i < count($arr3); $i++) {
            for ($k = 0; $k < count($arr3) - $i; $k++) {
                if ($arr3[$k]['mouth'] < $arr3[$k + 1]['mouth']) {
                    $temp = $arr3[$k + 1];
                    $arr3[$k + 1] = $arr3[$k];
                    $arr3[$k] = $temp;
                }
            }
        }
        for ($i = 1; $i < count($arr4); $i++) {
            for ($k = 0; $k < count($arr4) - $i; $k++) {
                if ($arr4[$k]['all'] < $arr4[$k + 1]['all']) {
                    $temp = $arr4[$k + 1];
                    $arr4[$k + 1] = $arr4[$k];
                    $arr4[$k] = $temp;
                }
            }
        }
        $this->assign('list2', $arr3);
        $this->assign('lists2', $arr4);
        return $this->fetch();
    }


}