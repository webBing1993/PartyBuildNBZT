<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16 0016
 * Time: 上午 9:47
 */

namespace app\home\controller;
use app\home\model\Notice;
use app\home\model\Company;
use app\home\model\Picture;
use think\Db;
use app\home\model\WechatUser;
use com\wechat\TPQYWechat;
use think\Config;
/**
 * Class Review
 * @package app\home\controller  消息审核
 */
class Review extends Base{
    /**
     * @return mixed 主页
     */
    public function index(){
        $this->checkRole();
        $this ->anonymous();
        $len = array('meeting' => 0,'dream' => 0,'volunteer' => 0);
        // 待审核
        $list = $this ->getDataList($len,0);
        $this ->assign('list',$list['data']);
        // 已审核
        $lists = $this->getDataList($len,1);
        foreach($lists['data'] as $key => $value){
            $lists['data'][$key]['review_name'] = "暂无";
            $lists['data'][$key]['review_time'] = time();
            if ($value['class'] == 1){
                // 会议纪要
                $lists['data'][$key]['review_name'] = Db::name('review')->where(['class' => 1 , 'aid' => $value['id']])->value('name');
                $lists['data'][$key]['review_time'] = Db::name('review')->where(['class' => 1 , 'aid' => $value['id']])->value('create_time');
            }else{
                // 志愿
                $lists['data'][$key]['review_name'] = Db::name('review')->where(['class' => 2 , 'aid' => $value['id']])->value('name');
                $lists['data'][$key]['review_time'] = Db::name('review')->where(['class' => 2 , 'aid' => $value['id']])->value('create_time');
            }
        }
        $this->assign('lists',$lists['data']);
        return $this->fetch();
    }
    /**
     * 获取数据列表 会议纪要 notice  微心愿 company type 1 志愿招募 company type 2
     * @param $len
     */
    public function getDataList($len,$status=0)
    {
        //从第几条开始取数据
        $count1 = $len['meeting'];   // 会议纪要
        $count2 = $len['dream'];  // 微心愿
        $count3 = $len['volunteer'];  //志愿招募
        $notice = new Notice();
        $company = new Company();
        $notice_check = false; // 数据状态 true为取空
        $company1_check = false;
        $company2_check = false;
        $all_list = array();
        //获取数据  取满12条 或者取不出数据退出循环
        while(true)
        {
            // 会议纪要
            if (!$notice_check && count($all_list) < 12){
                $res1 = $notice->getDataList($count1,$status);
                if (empty($res1)){
                    $notice_check = true;
                }else{
                    $count1 ++ ;
                    $all_list = $this->changeTpye($all_list,$res1,1);
                }
            }
            // 微心愿
            if(!$company1_check &&
                count($all_list) < 12)
            {
                $res2 = $company ->getDataList($count2,$status,1);
                if(empty($res2))
                {
                    $company1_check = true;
                }else {
                    $count2 ++;
                    $all_list = $this ->changeTpye($all_list,$res2,2);
                }
            }
            // 志愿招募
            if(!$company2_check &&
                count($all_list) < 12)
            {
                $res3 = $company ->getDataList($count3,$status,2);
                if(empty($res3))
                {
                    $company2_check = true;
                }else {
                    $count3 ++;
                    $all_list = $this ->changeTpye($all_list,$res3,3);
                }
            }
            if(count($all_list) >= 12 || ($notice_check && $company1_check && $company2_check))
            {
                break;
            }
        }
        if (count($all_list) != 0)
        {
            return ['code' => 1,'msg' => '获取成功','data' => $all_list];
        }else{
            return ['code' => 0,'msg' => '获取失败','data' => $all_list];
        }
    }

    /**
     * 进行数据区分
     * @param $list
     * @param $type 1 会议纪要  2 微心愿 3 志愿招募
     */
    private function changeTpye($all,$list,$type){
        $list['class'] = $type;
        array_push($all,$list);
        return $all;
    }
    /**
     * 首页加载更多新闻列表
     * @return array
     */
    public function moreDataList(){
        $this->checkRole();
        $this ->anonymous();
        $len = input('post.');
        if ($len['type'] == 0){
            // 待审核
            unset($len['type']);
            $list = $this ->getDataList($len,0);
        }else {
            // 已审核
            unset($len['type']);
            $list = $this->getDataList($len,1);
            foreach($list['data'] as $key => $value){
                $list['data'][$key]['review_name'] = "暂无";
                $list['data'][$key]['review_time'] = time();
                if ($value['class'] == 1){
                    // 会议纪要
                    $list['data'][$key]['review_name'] = Db::name('review')->where(['class' => 1 , 'aid' => $value['id']])->value('name');
                    $list['data'][$key]['review_time'] = date('Y-m-d',Db::name('review')->where(['class' => 1 , 'aid' => $value['id']])->value('create_time'));
                }else{
                    // 志愿
                    $list['data'][$key]['review_name'] = Db::name('review')->where(['class' => 2 , 'aid' => $value['id']])->value('name');
                    $list['data'][$key]['review_time'] = date('Y-m-d',Db::name('review')->where(['class' => 2 , 'aid' => $value['id']])->value('create_time'));
                }
            }
        }
        //转化图片路径 时间戳
        foreach ($list['data'] as $k => $v)
        {
            $img_path = Picture::get($list['data'][$k]['front_cover']);
            $list['data'][$k]['time'] = date('Y-m-d',$v['create_time']);
            $list['data'][$k]['path'] = $img_path['path'];
            $list['data'][$k]['name'] = WechatUser::where('userid',$v['userid'])->value('name');
        }
        return $list;
    }
    /**
     * 会议纪要  详情
     */
    public function detail(){
        $this->checkRole();
        $this ->anonymous();
        $id = input('id');
        $Notice = new Notice();
        $info = $Notice->where('id',$id)->find();
        $info['images']=json_decode($info['images'],true);
        //dump($info);exit();
        $this->assign('detail',$info);
        return $this->fetch();
    }
    /**
     * 志愿  详情
     */
    public function details(){
        $this->checkRole();
        $this ->anonymous();
        $id = input('id');
        $company = new Company();
        $info = $company->where('id',$id)->find();
        $this->assign('detail',$info);
        return $this->fetch();

    }
    /**
     * 审核
     */
    public function review(){
        $this->checkRole();
        $this ->anonymous();
        $userId = session('userId');
        $user = WechatUser::where('userid', $userId)->find();
        $username = $user['name'];
        $msg = input('post.');
        //新建review数据
        $data = array(
            'class' => $msg['class'],
            'aid' => $msg['id'],
            'userid' => $userId,
            'name' => $username,
            'status' => $msg['status'],
            'create_time' => time()
        );
        Db::name('review')->insert($data);
        $res = $this->change_status($msg['class'],$msg['id'],$msg['status']);
        if ($res){
            // 审核通过   推送至个人中心
            switch ($msg['class']){
                case 1:
                    $pre = "会议纪要";
                    $title = Db::name('notice')->where('id',$msg['id'])->value('title');
                    $user_id = Db::name('notice')->where('id',$msg['id'])->value('userid');
                    break;
                case 2:
                    $pre = "微心愿";
                    $title = Db::name('company')->where('id',$msg['id'])->value('title');
                    $user_id = Db::name('company')->where('id',$msg['id'])->value('userid');
                    break;
                case 3:
                    $pre = "志愿招募";
                    $title = Db::name('company')->where('id',$msg['id'])->value('title');
                    $user_id = Db::name('company')->where('id',$msg['id'])->value('userid');
                    break;
                default:
                    $pre = "暂无";
                    $title = "暂无";
                    $user_id = '';
            }
            if ($msg['status'] == 1){
                $content = "恭喜您提交的".$pre."【".$title."】已成功通过审核!";
            }else{
                $content = "很遗憾您提交的".$pre."【".$title."】未通过审核！";
            }
            $message = array(
                "touser" => $user_id,
                "msgtype" => 'text',
                "agentid" =>Config::get('user.agentid'),
                "text" => array('content' => $content),
            );
            //发送给企业号
            $Wechat = new TPQYWechat(Config::get('user'));
            $msg = $Wechat->sendMessage($message);
            if($msg['errcode'] == 0){
                return $this->success('审核成功');
            }else{
                $this->error($Wechat->errMsg);
            }
        }else{
            return $this->error('审核失败');
        }
    }
    /**
     *  改变状态值
     */
    public function change_status($type,$id,$status){
        switch ($type) {    //根据类别获取表明
            case 1:
                $table = "notice";
                break;
            case 2:
            case 3:
                $table = "company";
                break;
            default:
                return $this->error("无该数据表");
                break;
        }
        $res = Db::name($table)->where(['id' => $id])->update(['status' => $status]);
        return $res;
    }
}