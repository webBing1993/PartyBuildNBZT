<?php
namespace app\admin\controller;

/**
 * Created by PhpStorm.
 * User: 老王
 * Date: 2017/7/18
 * Time: 8:58
 */
use app\admin\model\Work as WorkModel;
use think\Db;
use app\admin\model\Picture;
use think\Config;
use com\wechat\TPQYWechat;
/**
 * Class Work
 * @package app\admin\controller  签到管理  控制器
 */
class Work extends Admin
{
    /*
     * 主页
     */
    public function index()
    {
        $map = array(
            'status' => array('egt', 0),
        );
        $list = $this->lists('Work', $map);
        int_to_string($list, array(
            'type' => array(1 => "三会一课", 2 => "志愿之家"),
        ));
        $this->assign('list', $list);
        return $this->fetch();
    }

    /*
     *  删除签到标题
     */
    public function del($id)
    {
        $data = Db::table('pb_work')->where('id', $id)->update(['status' => '-1']);
        if ($data) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

    /*
     * 标题添加
     */
    public function add()
    {

        return $this->fetch();
    }

    public function add2()
    {
        $data = input('post.');
        $data3 = date("Y-m-d H:i:s");
        $list9 = $data['front_cover'];
        $list8 = $data['type'];
        $list1 = $data['title'];
        $list2 = $data['meet_time'];
        $list3 = $data['meet_endtime'];
        $list4 = $data['branch'];
        $list5 = $data['tel'];
        $list6 = $data['meet_home'];
        $list7 = $data['publisher'];
        if (empty($list9)) {
            return $this->error('会议封面不能为空!');
        } elseif (empty($list1)) {
            return $this->error('会议标题不能为空!');
        } elseif (empty($list2)) {
            return $this->error('会议开始时间不能为空');
        } elseif (empty($list3)) {
            return $this->error('会议结束时间不能为空');
        } elseif (empty($list6)) {
            return $this->error('会议地点不能为空');
        } elseif (empty($list4)) {
            return $this->error('主办方不能为空');
        } elseif (empty($list5)) {
            return $this->error('联系电话不能为空');
        } elseif (empty($list7)) {
            return $this->error('发布人不能为空');
        } else {
            $data1 = ['title' => $list1, 'meet_time' => $list2, 'meet_endtime' => $list3, 'branch' => $list4,
                'tel' => $list5, 'meet_home' => $list6, 'publisher' => $list7, 'create_time' => $data3, 'type' => $list8, 'front_cover' => $list9];
            $data2 = Db::table('pb_work')->insert($data1);
            if ($data2) {
                return $this->success('添加成功!', Url('Work/index'));
            } else {
                return $this->error('添加失败!');
            }
        }
    }

    //查看会议详情
    public function see($id)
    {
        $data = Db::table('pb_work')->where('id', $id)->find();
        //查找图片表里的图片
        $data2 = Db::table('pb_picture')->where('id', $data['front_cover'])->find();
        $data1 = Db::table('pb_apply')->where('sign_id', $id)->select();
        $arr = array();
        //循环遍历三维数组$arr3
        foreach ($data1 as $value) {
            $arr[] = $value;
        }
        //查询签到用户
        $data4 = Db::table('pb_apply')->where('sign_id', $id)->select();
        foreach($data4 as $key=>$v){
            $list = Db::table('pb_wechat_user')->where('userid',$v['userid'])->find();
            $data4[$key]['userid']=$list['name'];
        }
        $this->assign('arr', $arr);
        $this->assign('data', $data);
        $this->assign('data2', $data2);
        $this->assign('data4', $data4);
        return $this->fetch();
    }

    //后台签到显示内容页面上的修改页面显示
    public function modifyxs($id)
    {
        $data = Db::table('pb_work')->where('id', $id)->find();
        if ($data['type'] == 1) {
            $list = array(
                'type' => '三会一课',
            );
        } else {
            $list = array(
                'type' => '志愿之家',
            );
        }
        //查找图片表里的图片
        $data2 = Db::table('pb_picture')->where('id', $data['front_cover'])->find();
        $this->assign('list',$list);
        $this->assign('data2', $data2);
        $this->assign('data', $data);
        return $this->fetch();
    }

    //显示页面上的修改
    public function modify($id)
    {
        $data = input('post.');
        if (empty($data['front_cover'])) {
            if (empty($data['title'])) {
                return $this->error('会议标题不能为空!');
            } elseif (empty($data['meet_time'])) {
                return $this->error('会议开始时间不能为空!');
            } elseif (empty($data['meet_endtime'])) {
                return $this->error('会议结束时间不能为空!');
            } elseif (empty($data['meet_home'])) {
                return $this->error('会议地点不能为空!');
            } elseif (empty($data['branch'])) {
                return $this->error('主办方不能为空!');
            } elseif (empty($data['tel'])) {
                return $this->error('联系电话不能为空!');
            } elseif (empty($data['publisher'])) {
                return $this->error('发布人不能为空!');
            } else {
                $list = ['title' => $data['title'], 'meet_time' => $data['meet_time'], 'meet_endtime' => $data['meet_endtime'], 'branch' => $data['branch'], 'tel' => $data['tel'], 'meet_home' => $data['meet_home'], 'publisher' => $data['publisher'],'type'=>$data['type']];
                $data2 = Db::table('pb_work')->where('id', $id)->update($list);
                if ($data2) {
                    return $this->success('修改成功!', Url('Work/see?id=' . $id));
                } else {
                    return $this->error('修改失败!');
                }
            }
        } else {
            if (empty($data['front_cover'])) {
                return $this->error('会议封面不能为空!');
            } elseif (empty($data['title'])) {
                return $this->error('会议标题不能为空!');
            } elseif (empty($data['meet_time'])) {
                return $this->error('会议开始时间不能为空!');
            } elseif (empty($data['meet_endtime'])) {
                return $this->error('会议结束时间不能为空!');
            } elseif (empty($data['meet_home'])) {
                return $this->error('会议地点不能为空!');
            } elseif (empty($data['branch'])) {
                return $this->error('主办方不能为空!');
            } elseif (empty($data['tel'])) {
                return $this->error('联系电话不能为空!');
            } elseif (empty($data['publisher'])) {
                return $this->error('发布人不能为空!');
            } else {
                $list = ['title' => $data['title'], 'meet_time' => $data['meet_time'], 'meet_endtime' => $data['meet_endtime'], 'branch' => $data['branch'], 'tel' => $data['tel'], 'meet_home' => $data['meet_home'], 'publisher' => $data['publisher'], 'front_cover' => $data['front_cover'],'type'=>$data['type']];
                $data2 = Db::table('pb_work')->where('id', $id)->update($list);
                if ($data2) {
                    return $this->success('修改成功!', Url('Work/see?id=' . $id));
                } else {
                    return $this->error('修改失败!');
                }
            }
        }
    }
    /**
     * 推送
     */
    public function push(){
        $id = input('id');
        if (empty($id)){
            return $this->error('系统错误,参数不存在');
        }
        $info = WorkModel::where(['id' => $id])->find();
        $content = "截止时间 : ".$info['meet_endtime'];
        $pre = '【会议签到】';
        $url = hostUrl."/home/signin/detail/id/".$info['id'].".html";
        $image = Picture::get($info['front_cover']);
        $path = hostUrl.$image['path'];
        $information = array(
            'title' => $pre.$info['title'],
            'description' => $content,
            'url'  => $url,
            'picurl' => $path
        );
        $send = array(
            "articles" => array(
                $information
            )
        );
        $message = array(
            "touser" => toUser,
            "msgtype" => 'news',
            "agentid" =>config('sign.agentid'),  // 活动签到
            "news" => $send,
        );
        //发送给企业号
        $Wechat = new TPQYWechat(Config::get('sign'));
        $msg = $Wechat->sendMessage($message);

        if($msg['errcode'] == 0){
            //保存到推送列表
            $result = WorkModel::where('id',$id)->update(['push' => 1]);
            if($result){
                return $this->success('发送成功');
            }else{
                $this->error('发送失败');
            }
        }else{
            $this->error($Wechat->errMsg);
        }
    }
}