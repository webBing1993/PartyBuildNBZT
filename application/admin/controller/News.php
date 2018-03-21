<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/21
 * Time: 14:41
 */
namespace app\admin\controller;
use app\admin\model\Picture;
use app\admin\model\Push;
use com\wechat\TPQYWechat;
use app\admin\model\News as NewsModel;
use think\Config;
/**
 * Class News
 * @package 党建动态   控制器
 */
class News extends Admin {
    /**
     * 主页列表
     */
    public function index(){
        $map = array(
            'status' => array('egt',0),
        );
        $list = $this->lists('News',$map);
        int_to_string($list,array(
            'type' => array(1=>"基层动态",2=>"党建动态"),
            'status' => array(0 =>"待审核",1=>"已通过",2=>"未通过"),
            'recommend' => [0 => "否" , 1 => "是"],
            'push' => [0=>"否",1=>"是"]
        ));

        $this->assign('list',$list);

        return $this->fetch();
    }

    /**
     * 新闻添加
     */
    public function add(){
        if(IS_POST) {
            $data = input('post.');
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if(empty($data['id'])){
                unset($data['id']);
            }
            $newModel = new NewsModel();
            $info = $newModel->validate('news')->create($data);
            if($info) {
                if ($data['push'] == 1){
                    $this->push($info['id'],$info['status']);
                }
                $data = [
                    'focus_vice' => null,
                    'create_user' => session('user_auth.username'),
                    'focus_main' => $info['id'],
                    'class' => 2, // 箬横动态
                ];
                //保存到推送列表
                Push::create($data);
                return $this->success("新增成功",Url('News/index'));
            }else{
                return $this->error($newModel->getError());
            }
        }else{
            $this->assign('msg','');

            return $this->fetch('edit');
        }
    }

    /**
     * 修改
     */
    public function edit(){
        if(IS_POST) {
            $data = input('post.');
            $data['create_time'] = time();
            $newModel = new NewsModel();
            $info = $newModel->validate('news')->save($data,['id'=>input('id')]);
            if($info){
                if ($data['push'] == 1){
                    $this->push($info['id'],$info['status']);
                }
                return $this->success("修改成功",Url("News/index"));
            }else{
                return $this->get_update_error_msg($newModel->getError());
            }
        }else{
            $id = input('id');
            $msg = NewsModel::get($id);
            $this->assign('msg',$msg);

            return $this->fetch();
        }
    }

    /**
     * 删除功能
     */
    public function del(){
        $id = input('id');
        $data['status'] = '-1';
        $info = NewsModel::where('id',$id)->update($data);
        if($info) {
            return $this->success("删除成功");
        }else{
            return $this->error("删除失败");
        }

    }
    /*
     * 新闻推送
     */
    public function push($id,$status){
        if(empty($id)){
            return $this->error('请选择推送文章');
        }
        $info = NewsModel::where('id',$id)->find();
        $str = strip_tags($info['content']);
        $des = mb_substr($str,0,40);
        $content = str_replace("&nbsp;","",$des);  //空格符替换成空
        $pre = '【箬横动态】';
        $url = hostUrl."/home/Reviews/detail/class/2/id/".$id.".html";
        $image = Picture::get($info['front_cover']);
        $path = hostUrl.$image['path'];
        $send = [
            'articles' => [
                0 => [
                    'title' => $pre.$info['title'],
                    'description' => $content,
                    'url'  => $url,
                    'picurl' => $path
                ]
            ]
        ];
        //发送给企业号
        if ($status == 0){
            // 待审核
            $Wechat = new TPQYWechat(Config::get('review'));
            $message = array(
                "touser" => toUser,
                "msgtype" => 'news',
                "agentid" => config('review.agentid'),  // 消息审核
                "news" => $send,
                "safe" => "0"
            );
        }elseif ($status == 1){
            // 通过
            $Wechat = new TPQYWechat(Config::get('user'));
            $message = array(
                "touser" => toUser,
                "msgtype" => 'news',
                "agentid" => config('user.agentid'),  // 个人中心
                "news" => $send,
                "safe" => "0"
            );
        }
        $Wechat->sendMessage($message);  // 推送至审核
    }
}