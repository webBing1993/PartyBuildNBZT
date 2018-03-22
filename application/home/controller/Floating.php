<?php
/**
 * Created by PhpStorm.
 * User: ali
 * Date: 2018/3/19
 * Time: 9:05
 */
namespace app\home\controller;
use app\admin\model\SelfFlaw;
use app\home\model\WechatUser;
use think\Db;
/*
 *
 * 流动党员
 * */

class Floating extends Base {


    /*
     * 流动党员首页
     * */

    public function index() {
        $flaw = new SelfFlaw();
        //本月最新的两条文章

        $month = db('self_flaw') ->whereTime('create_time','m')-> order('id','desc') ->limit(2)->select();
        //展示列表页
        $map = [
            'status' => ['egt',0]
        ];
        $list = $flaw ->where($map)->order('id','desc') ->select();
        foreach($list as $k=>$v) {
            if($k == 0 || $k == 1) {
                unset($list[$k]);
            }
        }
        $this->assign('month',$month);//最新的两条
        $this->assign('list',$list);//最新的两条
        return $this->fetch();
    }

    /*
     * 流动党员图文详情页
     * */

    public function article() {
        $userId = session('userId');
        $this->anonymous();
        $this->jssdk();
        $id = input('id/d');
        $map = [
            'userid'=>$userId,
            'detail_id'=>$id,
            'status'=>2
        ];
        $rank = db('self_rank')->where($map)->field('rank')->find();
        if($rank['rank'] != 0) {
            $check = 1;
        } else {
            $check = 0;
        }
        $info = $this->content(9,$id);
        $this->assign('detail',$info);
        $this->assign('check',$check);
        return $this->fetch();
    }

    /*
     * 流动党员视频详情页
     * */

    public function video() {
        $userId = session('userId');
        $this->anonymous();
        $this->jssdk();
        $id = input('id/d');
        $map = [
            'userid'=>$userId,
            'detail_id'=>$id,
            'status'=>2
        ];
        $rank = db('self_rank')->where($map)->field('rank')->find();
        if($rank['rank'] != 0) {
            $check = 1;
        } else {
            $check = 0;
        }
        $info = $this->content(9,$id);
        $this->assign('detail',$info);
        $this->assign('check',$check);
        return $this->fetch();
    }

    /*
     * 流动党员加载更多
     * */

    public function more() {
        $NewsModel = new SelfFlaw();
        $len = input('length');
        $map = ['status' => ['egt',0]];
        $list = $NewsModel->get_list($map,$len);
        if ($list){
            return $this->success('加载成功','',$list);
        }else{
            return $this->error('加载失败');
        }
    }

    /*
     * 流动党员积分排行
     * */

    public function rank() {
        $userId = session('userId');
        //月榜
        $Months =  db('self_rank') ->whereTime('create_time','m')->field('userid')->group('userid')->select();
        $monthRank =  db('self_rank') ->whereTime('create_time','m')->limit(100)->select();
        $listMonth = [];//一年的数据
        //进行累加
        foreach($Months as $ko=>$vo) {
            $num = 0;
            foreach($monthRank as $key=>$value) {
                if($vo['userid'] == $value['userid']) {
                    $zong = $value['rank']+$value['award'];
                    $num += $zong;
                }
            }
            $listMonth[$vo['userid']] = $num;
        }
        arsort($listMonth);
        //获取头像
        foreach($listMonth as  $k=>$v) {
            $user = WechatUser::where('userid',$k) ->find();
            $listMonth[$k]= ['rank'=> $listMonth[$k],'name'=>$user['name'],'avatar'=>$user['avatar'],'userid'=>$k,'header'=>$user['header']];
        }
        //重组
        $selfMonth = [];
        $monthList = [];
        $snum = 0;
        foreach ($listMonth as $key=>$value) {
            $monthList[$snum] = $value;
            if($key == $userId) {
                $selfMonth = $value;
                $selfMonth['ranking'] = $snum+1;
            }
            $snum++;
        }
        //总榜
        $years = date('Y',time());
        $info = array(
            "FROM_UNIXTIME(create_time,'%Y')" => $years,
        );
        $year = db('self_rank') ->field('userid')->group('userid')->select();
        $rank = db('self_rank') ->where($info)->limit(100)->select();
        $listYear = [];//一年的数据
        //进行累加
        foreach($year as $ko=>$vo) {
            $num = 0;
            foreach($rank as $key=>$value) {
                if($vo['userid'] == $value['userid']) {
                    $zong = $value['rank']+$value['award'];
                    $num += $zong;
                }
            }
            $listYear[$vo['userid']] = $num;
        }
        arsort($listYear);
        //获取头像
        foreach($listYear as  $k=>$v) {
            $user = WechatUser::where('userid',$k) ->find();
            $listYear[$k]= ['rank'=> $listYear[$k],'name'=>$user['name'],'avatar'=>$user['avatar'],'userid'=>$k,'header'=>$user['header']];
        }
        //年的重组
        $yearList = [];
        $selfYear = [];
        $ynum = 0;
        foreach ($listYear as $key=>$value) {
            $yearList[$ynum] = $value;
            if($key == $userId) {
                $selfYear = $value;
                $selfYear['ranking'] = $ynum+1;
            }
            $ynum++;
        }
        $list = [
            'monthList'=>$monthList,
            'yearList'=>$yearList,
            'selfMonth'=>$selfMonth,
            'selfYear' => $selfYear
        ];
        $this->assign('list',$list);
        return $this->fetch();
    }

    /*
     * 学习积分的统计
     *
     * */

    public function study() {
        //ajax传id 文章的id
        $data = input('post.');
        $userId = session('userId');
        //一遍文章只给一次积分
        $map = [
            'status'=>2,
            'userid'=>$userId,
            'detail_id'=>$data['id']
        ];

        $rank = db('self_rank')->where($map) ->find();
        if(empty($rank)) {
            $info = [
                'userid'=>$userId,
                'detail_id'=>$data['id'],
                'rank'=>1,
                'status'=>2,
                'create_time'=>time()
            ];
            $years = date('Y',time());
            $month = date('n',time());
            $week = array(
                "FROM_UNIXTIME(create_time,'%Y%c')" => $years.$month,
                'id'=>$data['id']
            );
            $flaw = db('self_flaw')->where($week)->find();
            if($flaw) {
                $re = db('self_rank')->insert($info);
            }
            if($re) {
                return $this->success('成功');
            } else {
                return $this->error('失败');
            }
        }
    }
}