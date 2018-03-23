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
            $arr = [];
            // 获取签到人员列表
            $list = Db::name('wechat_user_tag')->where(['tagid' => ['in',[2,3]]])->select();
            foreach($list as $key => $value){
                // 签到积分
                $sum1 = Db::name('apply')->where(['userid' => $value['userid'],'type' => 2,'status' => 0,"FROM_UNIXTIME(create_time,'%Y%c')" => $year.$mouth])->sum('score');
                // 干预分数
                $sum2 = Db::name('handle')->where(['userid' => $value['userid'],'mouth' => $mouth])->sum('score');
                $User = WechatUser::where('userid',$value['userid'])->find();
                if ($User){
                    $name = $User['name'];
                    $department_id = WechatDepartmentUser::where(['userid'=>$value['userid'],'departmentid' => ['in',[185,186]]])->value('departmentid');
                    $department = WechatDepartment::where('id',$department_id)->value('name');
                    //基础分
                    $sum3 = $User['volunteer_base'];
                }else {
                    $name = '暂无';
                    $department = "暂无";
                    //基础分
                    $sum3 = 0;
                }
                array_push($arr,['sum1' => $sum1,'sum2' => $sum2,'mouth' => $mouth,'name' => $name,'department' => $department,'sum3' => $sum3,'sum' => $sum1+$sum2+$sum3,'userid' => $value['userid']]);
            }
            array_multisort(array_column($arr,'sum'),SORT_DESC,$arr);
            return $this->success('加载成功','',$arr);
        }else{
            $year = date('Y',time());  // 年
            $mouth = date('m',time());  // 当前月份
            $arr = [];
            $list = Db::name('wechat_user_tag')->where(['tagid' => ['in',[2,3]]])->select();
            foreach($list as $key => $value){
                // 签到积分
                $sum1 = Db::name('apply')->where(['userid' => $value['userid'],'type' => 2,'status' => 0,"FROM_UNIXTIME(create_time,'%Y%c')" => $year.$mouth])->sum('score');
                // 干预分数
                $sum2 = Db::name('handle')->where(['userid' => $value['userid'],'mouth' => $mouth])->sum('score');
                $User = WechatUser::where('userid',$value['userid'])->find();
                if ($User){
                    $name = $User['name'];
                    $department_id = WechatDepartmentUser::where(['userid'=>$value['userid'],'departmentid' => ['in',[185,186]]])->value('departmentid');
                    $department = WechatDepartment::where('id',$department_id)->value('name');
                    //基础分
                    $sum3 = $User['volunteer_base'];
                }else {
                    $name = '暂无';
                    $department = "暂无";
                    //基础分
                    $sum3 = 0;
                }
                array_push($arr,['sum1' => $sum1,'sum2' => $sum2,'mouth' => $mouth,'name' => $name,'department' => $department,'sum3' => $sum3,'sum' => $sum1+$sum2+$sum3,'userid' => $value['userid']]);
            }
            //获取全部共同数据年份
            $years = Apply::all(function($query){
                $query->group("FROM_UNIXTIME(create_time,'%Y')")->field("FROM_UNIXTIME(create_time,'%Y') as year");
            });
            if(!in_array($year,$years)){
                array_push($years,['year' => $year]);
            }
            array_multisort(array_column($arr,'sum'),SORT_DESC,$arr);
            $this->assign('years',$years);
            $this->assign('list',$arr);
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
            $name = "暂无";
            $names = WechatUser::where('userid',$value['userid'])->value('name');
            if($names){
                $name = $names;
            }
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
            $num = -1;
        }else{
            // 加分
            $num = 1;
        }
        $res = Db::name('handle')->insert(['userid' => $userid,'mouth' => $mouth,'score' => $num,'create_time' => time()]);
        if ($res){
            return  $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }
}