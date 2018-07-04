<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/14 0014
 * Time: 下午 1:04
 */

namespace app\home\controller;
use app\home\model\Apply;
use app\home\model\WechatUser;
use app\home\model\WechatUserTag;
use app\home\model\Work;
use think\Db;
//签到
class Signin extends Base {
    /**
     * 签到主页
     */
    public function index(){
        $Work = new Work();
        $data=date("Y-m-d H:i:s");
        $map = ['status' => ['eq',0],'meet_endtime'=>['egt',$data]];
        $left = $Work->get_list($map);
        $map = ['status' => ['eq',0],'meet_endtime'=>['lt',$data]];
        $right = $Work->get_list($map);
        
        $this->assign('left',$left); // 最新签到
        $this->assign('right',$right);  // 历史签到
        return $this->fetch();
        
    }
    //上拉加载
    public function more()
    {
        $Work = new Work();
        $len = input('length');
        $c=input('type');
        $data=date("Y-m-d H:i:s");
        if ($c == 0){
            $map = ['status' => ['eq',0],'meet_endtime'=>['egt',$data]];
        }else{
            $map = ['status' => ['eq',0],'meet_endtime'=>['lt',$data]];
        }
        $list = $Work->get_list($map, $len);
        if ($list) {
            return $this->success('加载成功', '', $list);
        } else {
            return $this->error('加载失败');
        }
    }
    

    /**
     * 签到详情页
     */
    public function detail(){
        $this->jssdk();
        $id=input('id');
        $userId = session('userId');
        $res = WechatUserTag::where(['tagid' => scanId,'userid' => $userId])->find();
        if ($res){
            $is = 1;
        }else{
            $is = 0;
        }
        $data=Db::table('pb_work')->where('id',$id)->find();
        $data2=Db::table('pb_apply')->where('sign_id',$id)->select();
        foreach ($data2 as $key=>$v) {
            $list = Db::table('pb_wechat_user')->where('userid', $v['userid'])->find();
            $data2[$key]['userid'] = $list['name'];
            $data2[$key]['image'] = $list['avatar'];
        }
        $this->assign('data',$data);//详情内容
        $this->assign('data2',$data2);//签到人员
        $this->assign('is',$is);
        return $this->fetch();

    }
    /**
     * 签到功能
     */
    public function sign(){
        $data = input('post.');
        $id = $data['id'];
        $userid = $data['user_id'];
        $result = Apply::where(array('sign_id'=>$id,'userid'=>$userid))->find();
        if($result){
            // 已经签到
            $Wechat = WechatUser::where('userid',$userid)->find();
            return array('status'=>0,'header'=>$Wechat['avatar'],'name'=>$Wechat['name']);
        }else{
            $Wechat = WechatUser::where(['userid' => $userid])->find();
            if(!empty($Wechat)){
                $work = new Work();
                $Apply = new Apply();
                $type = $work->where('id',$id)->value('type');  // 1 三会一课  2 志愿之家
                if ($type == 1){
                    $data = array(
                        'sign_id' => $id,
                        'userid' =>$userid,
                        'score' => 0,
                        'status' =>0,
                        'type' => $type,
                        'create_time' => time(),
                        'mouth' => 0,
                    );
                    $res = $Apply->save($data);
                }else{
                    $mouth = date('m',time());
                    $score = $Apply->where(['userid' => $userid])->whereTime('create_time','d')->sum('score');
                    if ($score < 2){
                        WechatUser::where('userid',$userid)->setInc('volunteer_score');
                        $res = $Apply->save(['sign_id' => $id,'userid' => $userid,'score' => 1,'status' => 0,'type' =>$type,'mouth' => $mouth,'create_time' => time()]);
                    }else{
                        $res = $Apply->save(['sign_id' => $id,'userid' => $userid,'score' => 0,'mouth' => $mouth,'type' =>$type,'status' => 0,'create_time' => time()]);
                    }
                }
                if($res){
                    $User = WechatUser::where(['userid'=>$userid])->find();
                    return array('status'=>1,'header'=>$User['avatar'],'name'=>$User['name']);
                }else{
                    return array('status'=>2,'header'=>null,'name'=>null);
                }
            }else{
                return array('status'=>3,'header'=>null,'name'=>null);
            }

        }
    }
}