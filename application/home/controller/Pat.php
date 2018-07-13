<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 10:04
 */
namespace app\home\controller;
use app\home\model\WechatUser;
use app\home\model\WechatDepartmentUser;
use app\home\model\WechatDepartment;
use app\home\model\WechatUserTag;
use app\admin\model\Blog;
use think\Db;
/**
 * Class Pat
 * 随手拍
 */
class Pat extends Base {

    /*
     * 随手拍首页
     * */
    public function index()
    {
        $this->checkRole();
        $userId = session('userId');
        $res    = WechatUserTag::where(['userid' => $userId , 'tagid' => tagId])->find();
        if ($res){
            // 大领导权限
            $User = WechatUser::where(['userid' => $userId])->find();
            $msg  = array();
            $msg['header'] = $User['avatar'];  // 头像
            $msg['name']   = $User['name'];  // 姓名
            if ($User['position']){    // 职位
                $msg['position'] = $User['position'];
            }else{
                $msg['position'] = "暂无";
            }
            //切换部门
            if(IS_POST) {
                $depart_id     = input('title');
                $msg['depart'] = WechatDepartment::where(['id' => $depart_id])->value('name'); // 部门
                $msg['userid'] = $User['userid'];
                //
                $list = Db::table('pb_wechat_department_user')->where('departmentid',$depart_id)->field('userid')->limit(4)->select();  // 获取 用户列表
                //部门  下拉菜单
                $deps = Db::name('wechat_department')->where(['id' => ['not in' , [1]]])->order('id','desc')->field('id,name')->select();//['not in' , [1,2,50]]
                foreach($list as $key => $value){
                    $User = Db::table('pb_wechat_user')->where('userid',$value['userid'])->find();
                    $list[$key]['name'] = $User['name'];  // 名字
                    if ($User['avatar']){
                        $list[$key]['header'] = $User['avatar'];  // 头像
                    }else{
                        $list[$key]['header'] = '/home/images/common/vistor.jpg';  // 头像
                    }
                    if ($User['position']){    // 职位
                        $list[$key]['position'] = $User['position'];
                    }else{
                        $list[$key]['position'] = "暂无";
                    }
                    // 获取该人的最新一篇日志
                    $Log = Blog::where(['userid' => $value['userid'],'status' => 0])->order('create_time desc')->find();
                    if ($Log){
                        switch (date('N',$Log['create_time'])){
                            case 1:
                                $Log['time'] = date('Y.m.d （星期一）H:i',$Log['create_time']);
                                break;
                            case 2:
                                $Log['time'] = date('Y.m.d （星期二）H:i',$Log['create_time']);
                                break;
                            case 3:
                                $Log['time'] = date('Y.m.d （星期三）H:i',$Log['create_time']);
                                break;
                            case 4:
                                $Log['time'] = date('Y.m.d （星期四）H:i',$Log['create_time']);
                                break;
                            case 5:
                                $Log['time'] = date('Y.m.d （星期五）H:i',$Log['create_time']);
                                break;
                            case 6:
                                $Log['time'] = date('Y.m.d （星期六）H:i',$Log['create_time']);
                                break;
                            default:
                                $Log['time'] = date('Y.m.d （星期日）H:i',$Log['create_time']);
                        }
                        $list[$key]['times'] = date('m-d H:i',$Log['create_time']);

                    } else {
                        switch (date('N',time())){
                            case 1:
                                $Log['time'] = date('Y.m.d （星期一）H:i',time());
                                break;
                            case 2:
                                $Log['time'] = date('Y.m.d （星期二）H:i',time());
                                break;
                            case 3:
                                $Log['time'] = date('Y.m.d （星期三）H:i',time());
                                break;
                            case 4:
                                $Log['time'] = date('Y.m.d （星期四）H:i',time());
                                break;
                            case 5:
                                $Log['time'] = date('Y.m.d （星期五）H:i',time());
                                break;
                            case 6:
                                $Log['time'] = date('Y.m.d （星期六）H:i',time());
                                break;
                            default:
                                $Log['time'] = date('Y.m.d （星期日）H:i',time());
                        }
                        $Log['content'] = '暂无';
                        $list[$key]['times'] = date('m-d H:i',time());
                    }
                    $list[$key]['log'] = $Log;
                }
                $this->assign('deps',$deps);
                return $this->success('','',$list);
            } else {
                $depart_id     = WechatDepartmentUser::where(['userid' => $userId])->value('departmentid');
                $msg['depart'] = WechatDepartment::where(['id' => $depart_id])->value('name'); // 部门
                $msg['userid'] = $User['userid'];

                // 默认
                $list = Db::table('pb_wechat_department_user')->where('departmentid',2)->field('userid')->limit(4)->select();   // 获取 用户列表
                //部门  下拉菜单

                $deps = Db::name('wechat_department')->order('id','asc')->field('id,name')->select();
                //return var_dump($deps);
                foreach($list as $key => $value){
                    $User = Db::table('pb_wechat_user')->where('userid',$value['userid'])->find();
                    $list[$key]['name'] = $User['name'];  // 名字
                    if ($User['avatar']){
                        $list[$key]['header'] = $User['avatar'];  // 头像
                    }else{
                        $list[$key]['header'] = '/home/images/common/vistor.jpg';  // 头像
                    }
                    if ($User['position']){    // 职位
                        $list[$key]['position'] = $User['position'];
                    }else{
                        $list[$key]['position'] = "暂无";
                    }
                    // 获取该人的最新一篇日志
                    $Log = Blog::where(['userid' => $value['userid'],'status' => 0])->order('create_time desc')->find();
                    if ($Log){
                        switch (date('N',$Log['create_time'])){
                            case 1:
                                $Log['time'] = date('Y.m.d （星期一）H:i',$Log['create_time']);
                                break;
                            case 2:
                                $Log['time'] = date('Y.m.d （星期二）H:i',$Log['create_time']);
                                break;
                            case 3:
                                $Log['time'] = date('Y.m.d （星期三）H:i',$Log['create_time']);
                                break;
                            case 4:
                                $Log['time'] = date('Y.m.d （星期四）H:i',$Log['create_time']);
                                break;
                            case 5:
                                $Log['time'] = date('Y.m.d （星期五）H:i',$Log['create_time']);
                                break;
                            case 6:
                                $Log['time'] = date('Y.m.d （星期六）H:i',$Log['create_time']);
                                break;
                            default:
                                $Log['time'] = date('Y.m.d （星期日）H:i',$Log['create_time']);
                        }
                        $list[$key]['times'] = date('m-d H:i',$Log['create_time']);
                    } else {
                        switch (date('N',time())){
                            case 1:
                                $Log['time'] = date('Y.m.d （星期一）H:i',time());
                                break;
                            case 2:
                                $Log['time'] = date('Y.m.d （星期二）H:i',time());
                                break;
                            case 3:
                                $Log['time'] = date('Y.m.d （星期三）H:i',time());
                                break;
                            case 4:
                                $Log['time'] = date('Y.m.d （星期四）H:i',time());
                                break;
                            case 5:
                                $Log['time'] = date('Y.m.d （星期五）H:i',time());
                                break;
                            case 6:
                                $Log['time'] = date('Y.m.d （星期六）H:i',time());
                                break;
                            default:
                                $Log['time'] = date('Y.m.d （星期日）H:i',time());
                        }
                        $Log['content'] = '暂无';
                        $list[$key]['times'] = date('m-d H:i',time());
                    }
                }
                //var_dump($list);exit;
                $this->assign('list',$list);
                $this->assign('log',$Log);
                $this->assign('deps',$deps);
                return $this ->fetch();
            }
        } else {
            // 普通员工权限
            $this->redirect('/home/pat/more?userid='.$userId);
        }
    }

    /*
     * 详情页
     * */
    public function detail()
    {
        $this->checkRole();
        $id = input('id/d');
        $msg = Blog::where(['id' => $id,'status' =>0])->find();
        if (empty($msg)){
            return $this->error('系统参数错误或者内容已删除');
        }
        $User = WechatUser::where(['userid' => $msg['userid']])->find();
        $msg['header'] = $User['avatar'];  // 头像
        $msg['name'] = $User['name'];  // 姓名
        if ($User['position']){    // 职位
            $msg['position'] = $User['position'];
        }else{
            $msg['position'] = "暂无";
        }
        $depart_id = WechatDepartmentUser::where(['userid' => $User['userid']])->value('departmentid');
        $msg['depart'] = WechatDepartment::where(['id' => $depart_id])->value('name'); // 部门
        $msg['times'] = date('Y.m.d H:i',$msg['create_time']);
        switch (date('N',$msg['create_time'])){
            case 1:
                $msg['time'] = date('Y.m.d （星期一）H:i',$msg['create_time']);
                break;
            case 2:
                $msg['time'] = date('Y.m.d （星期二）H:i',$msg['create_time']);
                break;
            case 3:
                $msg['time'] = date('Y.m.d （星期三）H:i',$msg['create_time']);
                break;
            case 4:
                $msg['time'] = date('Y.m.d （星期四）H:i',$msg['create_time']);
                break;
            case 5:
                $msg['time'] = date('Y.m.d （星期五）H:i',$msg['create_time']);
                break;
            case 6:
                $msg['time'] = date('Y.m.d （星期六）H:i',$msg['create_time']);
                break;
            default:
                $msg['time'] = date('Y.m.d （星期日）H:i',$msg['create_time']);
        }
        $msg['description_pic'] = !empty($msg['description_pic']) ? json_decode($msg['description_pic'],true) : null;
        // var_dump($msg);die;
        $this->assign('msg',$msg);
        return $this ->fetch();
    }

    /*
     * 发布页
     * */
    public function publish()
    {
        $this->checkRole();
        $userId = session('userId');

        // 办公室领导  普通员工权限 可以提交
        if (IS_POST){
            $data = input('post.');
            $data['userid'] = $userId;
            $data['create_time'] = time();
            $data['depart'] = WechatDepartmentUser::where(['userid' => $userId])->value('departmentid');

            if (!empty($data['list_images'])){
                $data['description_pic'] = json_encode($data['list_images']);
            }
            unset($data['list_images']);
            unset($data['id']);
            $res = Blog::create($data);
            if ($res){
                return $this->success('提交成功');
            }else{
                return $this->error('提交失败');
            }
        }else{
            return $this->fetch();
        }
    }


    /*
     * 列表页
     * */
    public function more()
    {
        $this->checkRole();
        if (IS_POST){//通过年份搜索的
            $year = input('post.year');
            $month = input('month');
            $userId = input('post.userid');
            //只选取年份，默认为所有
            if($year && $month == 0) {
                $info = array(
                    "FROM_UNIXTIME(create_time,'%Y')" => $year,
                    "userid" => $userId,
                    'status' => 0
                );
                $all = Blog::where($info)->order('id desc')->select();
                //重组用key值分组
                $list = array();
                foreach ($all as $value) {
                    $value['day'] = date("d", $value['create_time']);
                    $k = date("m", $value['create_time']);
                    $list[$k][] = $value;
                }
            }else{ 
                $info = array(
                    "FROM_UNIXTIME(create_time,'%Y%c')"  => $year.$month,
                    "userid" => $userId,
                    'status' => 0
                );
                $all = Blog::where($info)->order('id desc')->select();
                //重组用key值分组
                $list = array();
                foreach($all as $value){
                    $value['day'] = date('d',$value['create_time']);
                    $k = date("m",$value['create_time']);
                    $list[$k][] = $value;
                }
            }
            krsort($list,SORT_NUMERIC);
            return $this->success('','',$list);
        }else{//个人的
            $this->anonymous();
            $userId = input('get.userid');
            $res = WechatUser::where(['userid' => $userId])->find();
            if (empty($res)){
                return $this->error('系统参数错误');
            }
            $User = WechatUser::where(['userid' => $userId])->find();
            $msg = array();
            if ($User['avatar']){
                $msg['header'] = $User['avatar'];  // 头像
            }else{
                $msg['header'] = '/home/images/common/vistor.jpg';  // 头像
            }
            $msg['name'] = $User['name'];  // 姓名
            if ($User['position']){    // 职位
                $msg['position'] = $User['position'];
            }else{
                $msg['position'] = "暂无";
            }
            $depart_id = WechatDepartmentUser::where(['userid' => $userId])->value('departmentid');
            $msg['depart'] = WechatDepartment::where(['id' => $depart_id])->value('name'); // 部门
            $msg['year'] = date('Y',time());
            //获取全部共同数据年份
            $years = Blog::all(function($query){
                $query->group("FROM_UNIXTIME(create_time,'%Y')")->field("FROM_UNIXTIME(create_time,'%Y') as year");
            });
            $this->assign('years',$years);

            $info = array(
                "FROM_UNIXTIME(create_time,'%Y')" => $msg['year'],
                "userid" => $userId,
                'status' => 0
            );
            $all = Blog::where($info)->order('id desc')->select();
            //重组用key值分组
            $list = array();
            foreach($all as $value){
                $value['day'] = date('d',$value['create_time']);
                $k = date("m",$value['create_time']);
                $list[$k][] = $value;
            }
            krsort($list);
            $this->assign('list',$list);
        }
        $res    = WechatUserTag::where(['userid' => session('userId') , 'tagid' => tagId])->find();//是领导隐藏发布按钮
        if($res) {
            $this->assign('leader',1);
        }
        $this->assign('msg',$msg);
        $this->assign('userid',$userId);
        return $this ->fetch();
    }
    /*
     * 主页加载更多
     * */
    public function indexMore() {
        $this->checkRole();
            $data = input('post.');//接受值
            //var_dump($data);exit;
            $userId = session('userId');
            // 大领导权限
            $User = WechatUser::where(['userid' => $userId])->find();
            $msg  = array();
            $msg['header'] = $User['avatar'];  // 头像
            $msg['name']   = $User['name'];  // 姓名
            if ($User['position']){    // 职位
                $msg['position'] = $User['position'];
            }else{
                $msg['position'] = "暂无";
            }
        $depart_id     = WechatDepartmentUser::where(['userid' => $userId])->value('departmentid');
        $msg['depart'] = WechatDepartment::where(['id' => $depart_id])->value('name'); // 部门
        $msg['userid'] = $User['userid'];

        // 默认 党政办公室
        $list = Db::table('pb_wechat_department_user')->where('departmentid',$data['depart_id'])->field('userid')->limit($data['length'],4)->select();  // 获取 用户列表
        //部门  下拉菜单
        $deps = Db::name('wechat_department')->order(['order'=>'desc','id'])->field('id,name')->select();
        foreach($list as $key => $value){
            $User = Db::table('pb_wechat_user')->where('userid',$value['userid'])->find();
            $list[$key]['name'] = $User['name'];  // 名字
            if ($User['avatar']){
                $list[$key]['header'] = $User['avatar'];  // 头像
            }else{
                $list[$key]['header'] = '/home/images/common/vistor.jpg';  // 头像
            }
            if ($User['position']){    // 职位
                $list[$key]['position'] = $User['position'];
            }else{
                $list[$key]['position'] = "暂无";
            }
            // 获取该人的最新一篇日志
            $Log = Blog::where(['userid' => $value['userid'],'status' => 0])->order('create_time desc')->find();
            if ($Log){
                switch (date('N',$Log['create_time'])){
                    case 1:
                        $Log['time'] = date('Y.m.d （星期一）H:i',$Log['create_time']);
                        break;
                    case 2:
                        $Log['time'] = date('Y.m.d （星期二）H:i',$Log['create_time']);
                        break;
                    case 3:
                        $Log['time'] = date('Y.m.d （星期三）H:i',$Log['create_time']);
                        break;
                    case 4:
                        $Log['time'] = date('Y.m.d （星期四）H:i',$Log['create_time']);
                        break;
                    case 5:
                        $Log['time'] = date('Y.m.d （星期五）H:i',$Log['create_time']);
                        break;
                    case 6:
                        $Log['time'] = date('Y.m.d （星期六）H:i',$Log['create_time']);
                        break;
                    default:
                        $Log['time'] = date('Y.m.d （星期日）H:i',$Log['create_time']);
                }
                $list[$key]['times'] = date('m-d H:i',$Log['create_time']);
            } else {
                switch (date('N',time())){
                    case 1:
                        $Log['time'] = date('Y.m.d （星期一）H:i',time());
                        break;
                    case 2:
                        $Log['time'] = date('Y.m.d （星期二）H:i',time());
                        break;
                    case 3:
                        $Log['time'] = date('Y.m.d （星期三）H:i',time());
                        break;
                    case 4:
                        $Log['time'] = date('Y.m.d （星期四）H:i',time());
                        break;
                    case 5:
                        $Log['time'] = date('Y.m.d （星期五）H:i',time());
                        break;
                    case 6:
                        $Log['time'] = date('Y.m.d （星期六）H:i',time());
                        break;
                    default:
                        $Log['time'] = date('Y.m.d （星期日）H:i',time());
                }
                $Log['content'] = '暂无';
                $list[$key]['times'] = date('m-d H:i',time());
            }
            $list[$key]['log']   = $Log;
        }
        $this->assign('deps',$deps);
        return $this ->success('','',$list);

        }


}