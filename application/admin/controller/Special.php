<?php
namespace app\admin\controller;
use com\wechat\TPQYWechat;
use app\admin\model\Picture;
use app\admin\model\Push;
use think\Config;
use app\admin\model\Special as SpecialModel;
/**
 * Created by PhpStorm.
 * User: 老王
 * Date: 2017/7/17
 * Time: 14:09
 */
/*
  通知公告  控制器
*/
class Special extends Admin
{
    /*
     * 主页 管理
     */
    public function index(){
        $map = array(
            'status' => array('egt',0),
        );
        $list = $this->lists('Special',$map);
        int_to_string($list,array(
            'type' => [1 => "政策解读" , 2 => "通知公告"],
            'status' => array(0=>"已发布",1=>"已发布"),
            'recommend' => [0 => "否" , 1 => "是"]
        ));
        $this->assign('list',$list);
        return $this->fetch();
    }
    /*
     *内容 添加
     */
    public function add(){
        if(IS_POST) {
            $data = input('post.');
            $Model = new SpecialModel();
            if(empty($data['id'])) {
                unset($data['id']);
            }
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            $model = $Model->validate('Special')->create($data);
            if($model){
                $this ->publish($model['id']);//发布
                return $this->success('新增通知成功',Url('Special/index'));
            }else{
                return $this->get_update_error_msg($Model->getError());
            }
        }else{
            $msg = array();
            $msg['class'] = 1; // 1为添加 ，2为修改
            $this->assign('msg',$msg);
            return $this->fetch('edit');
        }
    }
    /*
     * 主题  内容 添加 修改
     */
    public function edit(){
        if(IS_POST) {
            $data = input('post.');
            $Model = new SpecialModel();
            $model = $Model->validate('Special')->save($data,['id'=> $data['id']]);
            if($model){
                $re = SpecialModel::where('id',$data['id'])->field('check')->find();
                if($re['check'] == 1) {
                    $this ->publish($data['id'],true);//发布
                } else {
                    $this ->publish($data['id']);
                }
                return $this->success('修改通知成功',Url('Special/index'));
            }else{
                return $this->get_update_error_msg($Model->getError());
            }
        }else{
            $this->default_pic();
            $id = input('id');
            $msg = SpecialModel::get($id);
            $msg['class'] = 2;
            $this->assign('msg',$msg);

            return $this->fetch();
        }
    }
    /*
     * 内容 删除
     */
    public function del(){
        $id = input('id');
        if (empty($id)){
            return $this->error('系统参数错误,请重新选择');
        }
        $res = SpecialModel::where(['id' => $id])->update(['status' => -1]);
        if ($res){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }
    /*
     * 推送列表
     */
    public function pushlist(){
        if(IS_POST){
            $id = input('id');//主图文id
            //副图文本周内的新闻消息
            $t = $this->week_time();
            $info = array(
                'id' => array('neq',$id),
                'create_time' => array('egt',$t),
                'status' => 0
            );
            $infoes = SpecialModel::where($info)->select();
            foreach($infoes as $value){
                switch ($value['type']){
                    case 1:
                        $value['title'] = '【政策解读】'.$value['title'];
                        break;
                    case 2:
                        $value['title'] = '【通知公告】'.$value['title'];
                        break;
                    default;
                }
            }
            return $this->success($infoes);
        }else{
            //新闻消息列表
            $map = array(
                'class' => 1,  // 通知公告
                'status' => array('egt',-1)
            );
            $list=$this->lists('Push',$map);
            int_to_string($list,array(
                'status' => array(-1 => '不通过', 0 => '未审核', 1=> '已发送')
            ));
            //数据重组
            foreach($list as $value){
                $msg = SpecialModel::where('id',$value['focus_main'])->find();
                switch ($msg['type']){
                    case 1:
                        $value['type'] = '政策解读';
                        break;
                    case 2:
                        $value['type'] = '通知公告';
                        break;
                    default;
                }
                $value['title'] = $msg['title'];
            }
            $this->assign('list',$list);
            //主图文本周内的新闻消息
            $t = $this->week_time();
            $info = array(
                'create_time' => array('egt',$t),
                'status' => 0
            );
            $infoes =SpecialModel::where($info)->select();
            foreach($infoes as $value){
                switch ($value['type']){
                    case 1:
                        $value['title'] = '【政策解读】'.$value['title'];
                        break;
                    case 2:
                        $value['title'] = '【通知公告】'.$value['title'];
                        break;
                    default;
                }
            }
            $this->assign('info',$infoes);
            return $this->fetch();
        }
    }
    /*
     * 新闻推送
     */
    public function push(){
        $data = input('post.');
        $arr1 = $data['focus_main'];      //主图文id
        isset($data['focus_vice']) ? $arr2 = $data['focus_vice'] : $arr2 = "";  //副图文id
        if($arr1 == -1){
            return $this->error('请选择主图文');
        }else{
            //主图文信息
            $info1 = SpecialModel::where('id',$arr1)->find();
        }
        $update['status'] = '1';
        $title1 = $info1['title'];
        SpecialModel::where(['id'=>$arr1])->update($update); // 更新推送后的状态
        $str1 = strip_tags($info1['content']);
        $des1 = mb_substr($str1,0,40);
        $content1 = str_replace("&nbsp;","",$des1);  //空格符替换成空
        $pre = '【通知公告】';
        $url1 = hostUrl."/home/Notice/detail/id/".$info1['id'].".html";
        $image1 = Picture::get($info1['front_cover']);
        $path1 = hostUrl.$image1['path'];
        $information1 = array(
            'title' => $pre.$title1,
            'description' => $content1,
            'url'  => $url1,
            'picurl' => $path1
        );
        $information = array();
        if(!empty($arr2)){
            //副图文信息
            $information2 = array();
            foreach($arr2 as $key=>$value){
                SpecialModel::where(['id'=>$value])->update($update); // 更新推送后的状态
                $info2 = SpecialModel::where('id',$value)->find();
                $title2 = $info2['title'];
                $str2 = strip_tags($info2['content']);
                $des2 = mb_substr($str2,0,40);
                $content2 = str_replace("&nbsp;","",$des2);  //空格符替换成空
                $pre1 = '【通知公告】';
                $url2 = hostUrl."/home/Notice/detail/id/".$info2['id'].".html";
                $image2 = Picture::get($info2['front_cover']);
                $path2 = hostUrl.$image2['path'];
                $information2[] = array(
                    "title" =>$pre1.$title2,
                    "description" => $content2,
                    "url" => $url2,
                    "picurl" => $path2,
                );
            }
            //数组合并,主图文放在首位
            foreach($information2 as $key=>$value){
                $information[0] = $information1;
                $information[$key+1] = $value;
            }
        }else{
            $information[0] = $information1;
        }
        //重组成article数据
        $send = array();
        $re[] = $information;
        foreach($re as $key => $value){
            $key = "articles";
            $send[$key] = $value;
        }

        //发送给企业号
        $Wechat = new TPQYWechat(Config::get('user'));
        $message = array(
            "touser" => toUser,
            "msgtype" => 'news',
            "agentid" => agentId,  // 个人中心
            "news" => $send,
            "safe" => "0"
        );
        $msg = $Wechat->sendMessage($message);  // 推送至审核

        if($msg['errcode'] == 0){
            $data['focus_vice'] ? $data['focus_vice'] = json_encode($data['focus_vice']) : $data['focus_vice'] = null;
            $data['create_user'] = session('user_auth.username');
            $data['class'] = 1;  // 通知公告
            $data['status'] = 1;
            //保存到推送列表
            $result = Push::create($data);
            if($result){
                return $this->success('发送成功');
            }else{
                return $this->error('发送失败');
            }
        }else{
            return $this->error('发送失败');
        }
    }

    /*
     * 发布推送
     */
    public function publish($arr1,$type=null){
        $info1 = SpecialModel::where('id',$arr1)->find();
        $title1 = $info1['title'];
        $str1 = strip_tags($info1['content']);
        $des1 = mb_substr($str1,0,40);
        $content1 = str_replace("&nbsp;","",$des1);  //空格符替换成空
        $pre = '【发布-通知公告】';
        $url1 = hostUrl."/home/Notice/detail/id/".$info1['id'].".html";
        $image1 = Picture::get($info1['front_cover']);
        $path1 = hostUrl.$image1['path'];
        $information1 = array(
            'title' => $pre.$title1,
            'description' => $content1,
            'url'  => $url1,
            'picurl' => $path1
        );
        $information = array();
        $information[0] = $information1;
        //重组成article数据
        $send = array();
        $re[] = $information;
        foreach($re as $key => $value){
            $key = "articles";
            $send[$key] = $value;
        }

        //发送给企业号
        if($type) {
            $Wechat = new TPQYWechat(Config::get('user'));
        } else {
            $Wechat = new TPQYWechat(Config::get('review'));
        }
        if($type) {
            $message = array(
                "touser" => toUser,
                "msgtype" => 'news',
                "agentid" => 1000005,  // 个人中心
                "news" => $send,
                "safe" => "0"
            );
        } else {
            $message = array(
                "touser" => toUser,
                "msgtype" => 'news',
                "agentid" => agentId,  // 消息审核
                "news" => $send,
                "safe" => "0"
            );
        }
        $msg = $Wechat->sendMessage($message);  // 推送至审核

        if($msg['errcode'] == 0){
            return $this->success('发送成功');
        }else{
            return $this->error('发送失败');
        }
    }
}