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
            'status' => ['gt',0]
        ];
        $list = $flaw ->where($map)->order('id','desc') ->limit(10)->select();
        $this->assign('month',$month);//最新的两条
        $this->assign('list',$list);//最新的两条
        return $this->fetch();
    }

    /*
     * 流动党员图文详情页
     * */

    public function detail() {
        $this->anonymous();
        $this->jssdk();
        $id = input('id/d');
        $info = $this->content(9,$id);
        $this->assign('detail',$info);
        return $this->fetch();
    }

    /*
     * 流动党员视频详情页
     * */

    public function video() {
        $this->anonymous();
        $this->jssdk();
        $id = input('id/d');
        $info = $this->content(9,$id);
        $this->assign('detail',$info);
        return $this->fetch();
    }

    /*
     * 流动党员加载更多
     * */

    public function more() {
        $NewsModel = new SelfFlaw();
        $len = input('length');
        $map = ['status' => ['gt',0]];
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

        //月榜
        $Months =  db('self_rank') ->whereTime('create_time','m')->field('userid')->group('userid')->select();
        $monthRank =  db('self_rank') ->whereTime('create_time','m')->select();
        $listMonth = [];//一年的数据
        //进行累加
        foreach($Months as $ko=>$vo) {
            $num = 0;
            foreach($monthRank as $key=>$value) {
                if($vo['userid'] == $value['userid']) {
                    $num += $value['rank'];
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
        $monthList = [];
        foreach ($listMonth as $value) {
            $monthList[] = $value;
        }
        //总榜
        $year = db('self_rank') ->field('userid')->group('userid')->select();
        $rank = db('self_rank') ->select();
        $listYear = [];//一年的数据
        //进行累加
        foreach($year as $ko=>$vo) {
            $num = 0;
            foreach($rank as $key=>$value) {
                if($vo['userid'] == $value['userid']) {
                    $num += $value['rank'];
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
        foreach ($listYear as $value) {
            $yearList[] = $value;
        }
        $list = [
            'monthList'=>$monthList,
            'yearList'=>$yearList
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
            $re = db('self_rank')->insert($info);
            if($re) {
                return $this->success('成功');
            } else {
                return $this->error('失败');
            }
        }
    }
}