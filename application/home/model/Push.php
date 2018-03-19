<?php
/**
 * Created by PhpStorm.
 * User: laowang
 * Date: 2018/3/19
 * Time: 下午2:10
 */

namespace app\home\model;
use think\Db;
use think\Model;

/**
 * Class Push
 * @package app\home\model
 * 后台审核 表
 */
class Push extends Model
{
    public function index($where,$len=0){
        $list = $this->where($where)->order('create_time','desc')->limit($len,12)->select();
        $data = [];
        foreach($list as $value){
            switch ($value['class']){
                case 1: // 通知公告
                    $info = Db::name('special')->where('id',$value['focus_main'])->find();
                    $title = $info['title'];
                    $id = $info['id'];
                    $class = 1;
                    $front_cover = $info['front_cover'];
                    $publish = $info['publish'];
                    $time = $info['create_time'];
                    break;
                case 2: // 箬横动态
                    $info = Db::name('news')->where('id',$value['focus_main'])->find();
                    $title = $info['title'];
                    $id = $info['id'];
                    $class = 2;
                    $front_cover = $info['front_cover'];
                    $publish = $info['publish'];
                    $time = $info['create_time'];
                    break;
            }
            $name = '暂无';
            $times = '暂无';
            $status = 2;
            $Review = Db::name('push_review')->where('push_id',$value['id'])->find();
            if ($Review){
                $name = $Review['username'];
                $times = date('Y-m-d',$Review['review_time']);
                $status = $Review['status'];
            }
            $tem = [
                'title' => $title,
                'front_cover' => Picture::where('id',$front_cover)->value('path'),
                'id' => $id,
                'fid' => $value['id'],
                'class' => $class,
                'publish' => $publish,
                'create_time' => date('Y-m-d',$time),
                'name' => $name,
                'review_time' => $times,
                'status' => $status
            ];
            array_push($data,$tem);
        }
        return $data;
    }
}