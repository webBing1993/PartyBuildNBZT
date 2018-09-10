<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/23
 * Time: 16:31
 */

namespace app\admin\controller;
use app\admin\model\Snapshot as SnapshotModel;

class Snapshot extends Admin
{
    public function index()
    {
        $map = array(
            'status' => 0,  // 删除 未删除
        );
        $list = $this->lists('Snapshot', $map);
        $this->assign([
            'list'=>$list,
        ]);
        return $this->fetch();
    }

    public function detail()
    {
        $id = input('id');
        $msg = SnapshotModel::where('id',$id)->field('content,front_cover')->find();
        if ($msg['front_cover']){
            $pic = json_decode($msg['front_cover'],true);
            foreach ($pic as $item){
                $path[] = get_cover($item)['path'];
            }
        }else{
            $path = '';
        }
        $msg['path'] = $path;
        $this->assign('msg',$msg);
        return $this->fetch();
    }

    public function del()
    {
        $id = input('id');
        $data['status'] = -1;
        $info = SnapshotModel::where('id', $id)->update($data);
        if ($info) {
            return $this->success("删除成功");
        } else {
            return $this->error("删除失败");
        }
    }
}