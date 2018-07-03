<?php
/**
 * Created by PhpStorm.
 * User: laowang
 * Date: 2018/7/3
 * Time: 下午4:05
 */

namespace app\home\controller;
use app\admin\model\Topic as TopicModel;
use think\Db;
use app\home\model\Browse;
use app\home\model\WechatUser;
use app\home\model\Picture;
use app\home\model\Like;
use app\home\model\Comment;
class Topic extends Base
{
    /**
     * @return mixed
     * 主题列表
     */
    public function home(){
        // 主题
        $map = array(
            'type' => 1,
            'status' => ['egt',0]
        );
        $topic = TopicModel::where($map)->order('id','desc')->limit(12)->select();
        $this->assign('list',$topic);
        return $this->fetch();
    }
    /**
     * @return
     * 主题更多
     */
    public function home_more(){
        $len = input('length');
        $map = array(
            'type' => 1,
            'status' => array('egt',0),
        );
        $list = TopicModel::where($map)->order('id desc')->limit($len,12)->select();
        foreach($list as $value){
            $img = Picture::get($value['front_cover']);
            $value['src'] = $img['path'];
        }
        if($list){
            return $this->success("加载成功",'',$list);
        }else{
            return $this->error("加载失败");
        }
    }
    /*
   * 专题模块 列表
   */
    public function index(){
        $id = input('id');
        // 专题列表
        $maps = array(
            'type' => 2,
            'status' => ['egt',0],
            't_id' => $id
        );
        $list = TopicModel::where($maps)->order('id',' desc')->limit(12)->select();
        $this->assign('list',$list);
        return $this->fetch();
    }
    /*
     * 专题 详情
     */
    public function detail(){
        $this->anonymous();
        $this->jssdk();

        $userId = session('userId');
        $id = input('id');
        //浏览加一
        $info['views'] = array('exp','`views`+1');
        TopicModel::where('id',$id)->update($info);

        if($userId != "visitor"){
            //浏览不存在则存入pb_browse表
            $con = array(
                'user_id' => $userId,
                'special_id' => $id,
            );
            $history = Browse::get($con);
            if(!$history && $id != 0){
                $s['score'] = array('exp','`score`+1');
                if ($this->score_up()){
                    // 未超过 15分
                    WechatUser::where('userid',$userId)->update($s);
                    Browse::create($con);
                }
            }
        }
        //详细信息
        $info = TopicModel::get($id);
        //分享图片及链接及描述
        $image = Picture::where('id',$info['front_cover'])->find();
        $info['share_image'] = "http://".$_SERVER['SERVER_NAME'].$image['path'];
        $info['link'] = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL'];
        $info['desc'] = str_replace('&nbsp;','',strip_tags($info['content']));

        //获取 文章点赞
        $likeModel = new Like;
        $like = $likeModel->getLike(6,$id,$userId);
        $info['is_like'] = $like;
        $this->assign('new',$info);

        //获取 评论
        $commentModel = new Comment();
        $comment = $commentModel->getComment(6,$id,$userId);
        $this->assign('comment',$comment);
        $this->assign('insert_id',$insert_id);
        return $this->fetch();
    }
    /*
     * 专题 列表 更多
     */
    public function more(){
        $len = input('length');
        $id = input('id');
        $map = array(
            'type' => 2,
            'status' => array('egt',0),
            't_id' => $id
        );
        $list = TopicModel::where($map)->order('id desc')->limit($len,12)->select();
        foreach($list as $value){
            $img = Picture::get($value['front_cover']);
            $value['src'] = $img['path'];
            $value['time'] = date("Y-m-d",$value['create_time']);
        }
        if($list){
            return $this->success("加载成功",'',$list);
        }else{
            return $this->error("加载失败");
        }
    }
}