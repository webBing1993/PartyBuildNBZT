<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/7
 * Time: 9:18
 */

namespace app\home\model;


use think\Model;
class Snapshot extends Model
{
    private static $userMessage = [];

    protected $insert = [
        'create_time' => NOW_TIME,
    ];

    public function getList($length = 0,$person='')
    {
        $uid = session('userId');
        $comment = new Comment();
        if ($person){
            $map = [
                'status' => 0,
                'uid'=>$person,
            ];
        }else{
            $map = [
                'status' => 0
            ];
        }
        $field = ['id,uid,create_time,views,content,front_cover,likes'];
        $res = $this->where($map)->field($field)->order('create_time desc')->limit($length, 12)->select();
        $list = [];
        foreach ($res as $value) {
            //用户信息处理
            $value['time'] = date('Y-m-d', $value['create_time']);
            $message = $this->getUser($value['uid']);
            $value['user'] = $message['name'];
            $value['department'] = $message['department'];
            $value['header'] = $message['header']?$message['header']:$message['avatar'];
            $path = [];
            //图片处理
            if ($value['front_cover']){
                $pic = json_decode($value['front_cover'],true);
                foreach ($pic as $item){
                    $path[] = get_cover($item);
                }
            }
            $value['img'] = $path;
            //获取评论
            $value['comment'] = $comment->getComment(10,$value['id'],$uid);
            //点赞
            $likeModel = new Like();
            $like = $likeModel->getLike(10,$value['id'],$uid);
            $value['is_like'] = $like;
            $list [] =$value;
        }
        return $list;
    }

    public function getUser($uid)
    {
        if (isset(self::$userMessage[$uid])) {
            return self::$userMessage[$uid];
        } else {
            $user = WechatUser::where('userid', $uid)->field('name,department,header,avatar')->find();
            $department = WechatDepartment::where('id', $user['department'])->field('name')->find();
            $user['department'] = $department['name'];
            self::$userMessage[$uid] = $user;
            return self::$userMessage[$uid];
        }
    }
}