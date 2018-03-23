<?php
/**
 * Created by PhpStorm.
 * User: laowang
 * Date: 2018/3/19
 * Time: 下午2:03
 */

namespace app\home\controller;
use app\home\model\Push;
use app\home\model\WechatUser;
use think\Db;
use app\home\model\Picture;
use com\wechat\TPQYWechat;
use think\Config;
/**
 * Class Reviews
 * @package app\home\controller
 * 箬横董涛 通知公告  审核
 */
class Reviews extends Base
{
    /**
     * 审核列表
     */
    public function index(){
        $this->checkRole();
        $Push = new Push();
        // 待审核
        $left = $Push->index(['status' => 0,'class' => ['in','1,2,4']]);
        // 已审核
        $right = $Push->index(['status' => ['gt',0],'class' => ['in','1,2,4']]);
        $this->assign('left',$left);
        $this->assign('right',$right);
        return $this->fetch();
    }
    /**
     * 加载更多
     */
    public function more(){
        $len = input('len'); //长度
        $c = input('type'); // 类型 0 待审核 1 已审核
        if ($c == 0){
            $where = ['status' => $c,'class' => ['in','1,2,4']];
        }else{
            $where = ['status' => ['egt',$c],'class' => ['in','1,2,4']];
        }
        $Push = new Push();
        $list = $Push->index($where,$len);
        if ($list){
            return $this->success('加载成功');
        }else{
            return $this->error('加载失败');
        }
    }
    /**
     * 审核
     */
    public function review(){
        $this->checkRole();
        $userId = session('userId');
        $username = WechatUser::where('userid', $userId)->value('name');
        $msg = input('post.');
        //新建review数据
        $data = array(
            'push_id' => $msg['fid'],
            'userid' => $userId,
            'username' => $username,
            'review_time' => time(),
            'status' => $msg['status'],
        );
        Push::where('id',$msg['fid'])->update(['status' => $msg['status']]);  // 状态改变
        $res = Db::name('push_review')->insert($data);
        if ($res){
            // 修改相应表状态
            switch ($msg['type']){
                case 1: // 通知公告
                    Db::name('special')->where('id',$msg['id'])->update(['status' => $msg['status']]);
                    $info = Db::name('special')->where('id',$msg['id'])->find();
                    $pre = '【通知公告】';
                    $url = hostUrl."/home/Notice/detail/id/".$info['id'].".html";
                    break;
                case 2: // 箬横动态
                    Db::name('news')->where('id',$msg['id'])->update(['status' => $msg['status']]);
                    $info = Db::name('news')->where('id',$msg['id'])->find();
                    $pre = '【箬横动态】';
                    $url = hostUrl."/home/News/detail/id/".$info['id'].".html";
                    break;
                case 4: // 流动党员
                    Db::name('self_flaw')->where('id',$msg['id'])->update(['status' => $msg['status']]);
                    $info = Db::name('self_flaw')->where('id',$msg['id'])->find();
                    $pre = '【流动党员】';
                    break;
            }
            // 通过 则判断是否推送
            if($msg['type'] == 4 ) {
                if($info['status'] == 1){
                    $str = strip_tags($info['content']);
                    $des = mb_substr($str,0,40);
                    $content = str_replace("&nbsp;","",$des);  //空格符替换成空
                    $url = hostUrl."/home/flaw/detail/id/".$info['id'].".html";
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
                    $Wechat = new TPQYWechat(Config::get('floating'));
                    $message = array(
                        "touser" => toUser,
                        "msgtype" => 'news',
                        "agentid" => config('floating.agentid'),  // 个人中心
                        "news" => $send,
                        "safe" => "0"
                    );
                    $msg = $Wechat->sendMessage($message);
                    if($msg['errcode'] !== 0){
                        return $this->error('审核失败');
                    }
                }
            } else {
                if($info['push'] == 1){
                    $str = strip_tags($info['content']);
                    $des = mb_substr($str,0,40);
                    $content = str_replace("&nbsp;","",$des);  //空格符替换成空
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
                    $Wechat = new TPQYWechat(Config::get('user'));
                    $message = array(
                        "touser" => toUser,
                        "msgtype" => 'news',
                        "agentid" => config('user.agentid'),  // 个人中心
                        "news" => $send,
                        "safe" => "0"
                    );
                    $msg = $Wechat->sendMessage($message);
                    if($msg['errcode'] !== 0){
                        return $this->error('审核失败');
                    }
                }
            }
            return $this->success('审核成功');
        }else{
            return $this->error('审核失败');
        }
    }
    /**
     * 审核详情
     */
    public function detail(){
        $this->checkRole();
        $class = input('class'); // 类型 1 通知公告 2 箬横动态
        $id = input('id');
        if (empty($class) || empty($id)){
            return $this->error('系统参数错误');
        }
        $info = [];
        switch ($class){
            case 1:
                $info = Db::name('special')->where('id',$id)->find();
                break;
            case 2:
                $info = Db::name('news')->where('id',$id)->find();
                break;
            case 4:
                $info = Db::name('self_flaw')->where('id',$id)->find();
                break;
        }
        $this->assign('detail',$info);
        return $this->fetch();
    }
}