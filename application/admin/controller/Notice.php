<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/10/8
 * Time: 10:29
 */

namespace app\admin\controller;

use app\admin\model\Notice as NoticeModel;
use think\Db;

/**
 * Class Notice
 * @package  支部活动
 */
class Notice extends Admin
{
    /**
     * 活动安排
     */
    public function index()
    {
        $map = array(
            'type' => 1,
            'status' => array('egt', 0),
        );
        $list = $this->lists('Notice', $map);
        int_to_string($list, array(
            'status' => array(0 => "待审核", 1 => "已发布", 2 => "审核未通过"),
            'recommend' => array(0 => "否", 1 => "是")
        ));
        $list2 = 1;

        $this->assign('list2', $list2);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 活动安排 添加
     */
    public function indexadd($type)
    {
        $this->assign('type', $type);
        if (IS_POST) {
            $data = input('post.');
            if ($data['type'] == 1) {
                $result = $this->validate($data, 'Notice');  // 验证  数据
            } else {
                $result = $this->validate($data, 'Notices');
            }
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if (true !== $result) {
                return $this->error($result);
            } else {
                $noticeModel = new NoticeModel();
                $data['start_time'] = strtotime($data['start_time']);
                if (!empty($data['end_time'])) {
                    $data['end_time'] = strtotime($data['end_time']);
                }
                $res = $noticeModel->save($data);
                if ($res) {
                    if ($data['type'] == 1) {
                        return $this->success("添加活动安排成功", Url('Notice/index'));
                    } else {
                        return $this->success("添加活动日志成功", Url('Notice/meeting'));
                    }
                } else {
                    return $this->error($noticeModel->getError());
                }
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * 活动安排 修改
     */
    public function indexedit()
    {
        if (IS_POST) {
            $data = input('post.');
            if ($data['type'] == 1) {
                $result = $this->validate($data, 'Notice');  // 验证  数据
            } else {
                $result = $this->validate($data, 'Notices');
            }
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if (true !== $result) {
                return $this->error($result);
            } else {
                $noticeModel = new NoticeModel();
                $data['start_time'] = strtotime($data['start_time']);
                if (!empty($data['end_time'])) {
                    $data['end_time'] = strtotime($data['end_time']);
                }
                $res = $noticeModel->save($data, ['id' => $data['id']]);
                if ($res) {
                    if ($data['type'] == 1) {
                        return $this->success("修改活动安排成功", Url('Notice/index'));
                    } else {
                        return $this->success("修改活动日志成功", Url('Notice/meeting'));
                    }
                } else {
                    return $this->get_update_error_msg($noticeModel->getError());
                }
            }
        } else {
            $id = input('id');
            $msg = NoticeModel::get($id);
            if (!empty($msg['userid'])) {
                $list = Db::table('pb_wechat_user')->where('userid', $msg['userid'])->find();
                $msg['userid'] = $list['name'];
            }else{
                $msg['userid'] = $msg['publisher'];
            }
            
            $this->assign('msg', $msg);
            return $this->fetch();
        }
    }

    /**
     * 删除
     */
    public function del()
    {
        $id = input('id');
        if (empty($id)) {
            return $this->error('系统参数错误');
        }
        $map['status'] = "-1";
        $info = NoticeModel::where('id', $id)->update($map);
        if ($info) {
            return $this->success("删除成功");
        } else {
            return $this->error("删除失败");
        }
    }

    //会议纪要
    public function meeting()
    {
        $map = array(
            'type' => 3,
            'status' => array('egt', 0),
        );
        $list = $this->lists('Notice', $map);
        int_to_string($list, array(
            'status' => array(0 => "待审核", 1 => "已发布", 2 => "审核未通过"),
            'recommend' => array(0 => "否", 1 => "是")
        ));
        $list2 = 3;
        foreach ($list as $v) {
            if (!empty($v['userid'])) {
                $list5 = Db::table('pb_wechat_user')->where('userid', $v['userid'])->find();
                $v['userid'] = $list5['name'];
            } else {
                $v['userid'] = $v['publisher'];
            }
        }

        $this->assign('list2', $list2);
        $this->assign('list', $list);
        return $this->fetch('notice/index');
    }

    /**
     * 活动展示
     */
    public function show()
    {
        $map = array(
            'type' => 2,
            'status' => array('egt', 0),
        );
        $list = $this->lists('Notice', $map);
        int_to_string($list, array(
            'status' => array(0 => "待审核", 1 => "已发布", 2 => "审核未通过"),
            'recommend' => array(0 => "否", 1 => "是")
        ));
        $list2 = 2;

        $this->assign('list2', $list2);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 活动展示 添加
     */
    public function showadd($type)
    {
        $this->assign('type', $type);
        if (IS_POST) {
            $data = input('post.');
            $result = $this->validate($data, 'Activity');  // 验证  数据
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if (true !== $result) {
                return $this->error($result);
            } else {
                $noticeModel = new NoticeModel();

                $res = $noticeModel->save($data);
                if ($res) {
                    if ($data['type'] == 2) {
                        return $this->success("添加活动展示成功", Url('Notice/show'));
                    } else {
                        return $this->success("添加主题党日活动成功", Url('Notice/activity'));
                    }
                } else {
                    return $this->error($noticeModel->getError());
                }
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * 活动展示 修改
     */
    public function showedit()
    {
        if (IS_POST) {
            $data = input('post.');
            $result = $this->validate($data, 'Activity');  // 验证  数据
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if (true !== $result) {
                return $this->error($result);
            } else {
                $noticeModel = new NoticeModel();

                $res = $noticeModel->save($data, ['id' => $data['id']]);
                if ($res) {
                    if ($data['type'] == 2) {
                        return $this->success("修改活动展示成功", Url('Notice/show'));
                    } else {
                        return $this->success("修改主题党日活动成功", Url('Notice/activity'));
                    }
                } else {
                    return $this->get_update_error_msg($noticeModel->getError());
                }
            }
        } else {
            $id = input('id');
            $msg = NoticeModel::get($id);
            $this->assign('msg', $msg);
            return $this->fetch();
        }
    }

    /**
     * 固定活动
     */
    public function activity()
    {
        $map = array(
            'type' => 4,
            'status' => array('egt', 0),
        );
        $list = $this->lists('Notice', $map);
        int_to_string($list, array(
            'status' => array(0 => "待审核", 1 => "已发布", 2 => "审核未通过"),
            'recommend' => array(0 => "否", 1 => "是")
        ));
        $list2 = 4;

        $this->assign('list2', $list2);
        $this->assign('list', $list);
        return $this->fetch('notice/show');
    }
    /**
     * 新闻通知预览
     */
    public function preview(){
        $Model = new NoticeModel();
        $id = input('id');
        $list = $Model::get($id);
        if ($list['images']){
            $list['images'] = json_decode($list['images']);
        }
        if ($list['start_time']){
            $list['start_time'] = date('Y-m-d H:i',$list['start_time']);
        }
        if (!empty($list['userid'])) {
            $list5 = Db::table('pb_wechat_user')->where('userid', $list['userid'])->find();
            $list['userid'] = $list5['name'];
        } else {
            $list['userid'] = $list['publisher'];
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
    
}