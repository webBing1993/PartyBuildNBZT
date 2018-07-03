<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/7
 * Time: 9:17
 */

namespace app\home\controller;
use app\home\model\Picture;
use app\home\model\Snapshot as SnapshotModel;
use com\wechat\TPWechat;
use think\Config;
/**随手拍控制器
 * Class Snapshot
 * @package app\home\controller
 */
class Snapshot extends Base
{
    /*
     * 随手拍浏览页
    */
    public function snapshot(){
        //验证游登录
        $this->anonymous();
        $Model = new SnapshotModel();
        $list = $Model->getList();
        $this->assign('list',$list);
        return $this->fetch();
    }

    /*
     * 发布页
    */
    public function publish(){
        $this->checkAnonymous();
        $this->jssdk();
        $Wechat = new TPWechat(Config::get('party'));
        if (IS_POST) {
            $data = input('post.');
            $data['uid'] = session('userId');
            if (isset($data['images'])) {
                $tem = [];
                foreach ($data['images'] as $value){
                    $pic = $Wechat->getMedia($value);
                    $url = "./uploads/download/images/".time().uniqid().".jpg";
                    $myfile = fopen($url, "w+");
                    fwrite($myfile, $pic);
                    fclose($myfile);
                    $Picture = new Picture();
                    $id = $Picture->save(['path' => substr($url,1),'url' => null,'md5' => md5($url),'sha1' => sha1($url),'status' => 0,'create_time' => time()]);
                    array_push($tem,$id);
                }
                $data['front_cover'] = json_encode($tem);
                unset($data['images']);
            }
            $Model = new SnapshotModel();
            $info = $Model->save($data);
            if ($info) {
                return $this->success("新增成功", Url('Snapshot/publish'));
            } else {
                return $this->error("新增失败", Url('Snapshot/publish'));
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * 下拉加载
     */
    public function listMore(){
        $snapshot = new SnapshotModel();
        $length = input('length');
        $list = $snapshot->getList($length);
        if($list) {
            return $this->success("加载更多","",$list);
        }else{
            return $this->error("加载失败");
        }
    }
}