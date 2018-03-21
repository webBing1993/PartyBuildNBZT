<?php
/**
 * Created by PhpStorm.
 * User: laowang <958364865@qq.com>
 * Date: 2017/9/11
 * Time: 16:14
 */

namespace app\admin\controller;
use app\admin\model\WechatDepartment;
use app\admin\model\WechatDepartmentUser;
use app\admin\model\WechatUser;
use app\admin\model\Apply;
use think\Db;
/**
 * Class Rank
 * @package app\admin\controller  排行榜
 */
class Rank extends Admin
{
    /**
     * 首页
     */
    public function index(){
        if (IS_POST){
            $year = input('year');
            $mouth = input('month');
            // 获取签到人员列表
            $list = Db::name('wechat_user_tag')->where(['tagid' => ['in',[2,3]]])->select();
            foreach($list as $key => $value){
                $sums = Db::name('apply')->where(['userid' => $value['userid'],'type' => 2,'status' => 0,"FROM_UNIXTIME(create_time,'%Y%c')" => $year.$mouth])->sum('score');
                // 干预分数
                $sum = Db::name('handle')->where(['userid' => $value['userid'],'mouth' => $mouth])->sum('score');
                $list[$key]['sum'] = $sums + $sum;
                $list[$key]['sums'] = $sums;
                $list[$key]['mouth'] = $mouth;
                $User = WechatUser::where('userid',$value['userid'])->find();
                if ($User){
                    $list[$key]['name'] = $User['name'];
                    $department_id = WechatDepartmentUser::where(['userid'=>$value['userid'],'departmentid' => ['in',[185,186]]])->value('departmentid');
                    $list[$key]['department'] = WechatDepartment::where('id',$department_id)->value('name');
                    //基础分
                    $list[$key]['base'] = $User['volunteer_base'];
                }else {
                    $list[$key]['name'] = '暂无';
                    $list[$key]['department'] = "暂无";
                    //基础分
                    $list[$key]['base'] = 0;
                }
            }
            return $this->success('加载成功','',$list);
        }else{
            $year = date('Y',time());  // 年
            $mouth = date('m',time());  // 当前月份
            // 获取签到人员列表
            $list = Db::name('wechat_user_tag')->where(['tagid' => ['in',[2,3]]])->select();
            foreach($list as $key => $value){
                $sums = Db::name('apply')->where(['userid' => $value['userid'],'type' => 2,'status' => 0,"FROM_UNIXTIME(create_time,'%Y%c')" => $year.$mouth])->sum('score');
                // 干预分数
                $sum = Db::name('handle')->where(['userid' => $value['userid'],'mouth' => $mouth])->sum('score');
                $list[$key]['sum'] = $sums + $sum;
                $list[$key]['sums'] = $sums;
                $list[$key]['mouth'] = $mouth;
                $User = WechatUser::where('userid',$value['userid'])->find();
                if ($User){
                    $list[$key]['name'] = $User['name'];
                    $department_id = WechatDepartmentUser::where(['userid'=>$value['userid'],'departmentid' => ['in',[185,186]]])->value('departmentid');
                    $list[$key]['department'] = WechatDepartment::where('id',$department_id)->value('name');
                    //基础分
                    $list[$key]['base'] = $User['volunteer_base'];
                }else {
                    $list[$key]['name'] = '暂无';
                    $list[$key]['department'] = "暂无";
                    //基础分
                    $list[$key]['base'] = 0;
                }
            }
            //获取全部共同数据年份
            $years = Apply::all(function($query){
                $query->group("FROM_UNIXTIME(create_time,'%Y')")->field("FROM_UNIXTIME(create_time,'%Y') as year");
            });
            if(!in_array($year,$years)){
                array_push($years,['year' => $year]);
            }
            $this->assign('years',$years);
            $this->assign('list',$list);
            return $this->fetch();
        }
    }
    /**
     * 积分详情
     */
    public function detail($mouth,$userid){
        $map = array(
            'type' => 2,
            'mouth' => $mouth,
            'userid' => $userid,
        );
        $list = $this->lists('Apply',$map);
        foreach($list as $value){
            $info = Db::name('work')->where('id',$value['sign_id'])->find();
            $value['title']  = $info['title'];
        }
        $this->assign('list',$list);
        return  $this->fetch();
    }
    /**
     * 操作日志
     */
    public function book(){
        $search = input('search');
        $where = array();
        if ($search != '') {
            $map['name'] = ['like','%'.$search.'%'];
            $arr = WechatUser::where($map)->column('userid');
            $where = array(
                'userid' => ['in',$arr]
            );
        }
        $list =  $this->lists('Handle',$where);
        foreach($list as $value){
            $name = WechatUser::where('userid',$value['userid'])->value('name');
            $value['content'] = "对用户：【".$name."】人工干预 【 ".$value['score']." 】 分";
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 人工操作积分
     */
    public function handle(){
        $data = input('post.');
        $userid = $data['userid'];
        $mouth = $data['month'];  // 月份
        $type = $data['type'];  // 1 减  2 加
        $User = WechatUser::where('userid',$userid)->find();
        if (empty($User)){
            return $this->error('操作失败');
        }
        if ($type == 1){
            // 减分
            $res = WechatUser::where('userid',$userid)->setDec('volunteer_score');
            $num = -1;
        }else{
            // 加分
            $res = WechatUser::where('userid',$userid)->setInc('volunteer_score');
            $num = 1;
        }
        if ($res){
            // 存日志
            Db::name('handle')->insert(['userid' => $userid,'mouth' => $mouth,'score' => $num,'create_time' => time()]);
            return  $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }
}