<?php
/**
 * Created by PhpStorm.
 * User: ali
 * Date: 2018/1/2
 * Time: 14:44
 */
namespace app\admin\controller;
/*
 * 流动党员
 * */
use app\admin\model\SelfFlaw;
use com\wechat\TPQYWechat;
use app\admin\model\Picture;
use think\Config;
use app\admin\model\Push;
class Flaw extends Admin {


    /*
     *流动党员
     * */
    public function index() {
        $map  = ['status'=>['egt',1]];
        $list = $this ->lists('SelfFlaw',$map);//分页
        int_to_string($list,array(//数组进行整数映射转换，转换出自己想要啊的
            'type' => array(1=>"流动党员"),
            'status' => array(1=>"已发布"),
            'push' => [0 => "否" , 1 => "是"],
            'photo' => [1 => "图片" , 2 => "视频"],
        ));
        $this->assign('list',$list);
        return $this->fetch();
    }
    


    /*
     * 添加
     * */
    public function add() {
        if(IS_POST) {
            $data   = $this->request->param();
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];//获取用户名
            $data['type'] = 1;//1为流动党员
            $data['push'] = 0;//推送
            unset($data['id']);
            if($data['photo'] == 2) {
                if($data['net_path'] == '') {
                    return $this->error('请添加视频链接');
                }
            }
            $activity   = new SelfFlaw();
            $result = $activity ->validate('Study')->save($data);
            if($result) {
                return $this->success('添加成功',url('admin/flaw/index'));
            } else {
                return $this->error($activity->getError());
            }
        } else {
            $this->assign('msg','');
            return $this->fetch('edit');
        }
    }
    /*
     * 修改
     * */
    public function edit() {
        $id      = input('id');
        $activity    = new SelfFlaw();
        if(IS_POST) {
            $data   = input('post.');
            $data['type'] = 1;//1为流动党员
            $data['push'] = 0;//推送
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];//获取用户名
            $result = $activity ->save($data,['id'=>$id]);
            if($result) {
                return $this->success('修改成功',url('admin/flaw/index'));
            } else {
                return $this->error($activity->getError());
            }
        } else {
            $msg = $activity ->get($id);//图片是通过v层调用自己分装的函数，函数分装在app\common
            $this->assign('msg',$msg);
            return $this->fetch();
        }
    }
    /*
     * 删除
     * */
    function del() {
        $id     = input('id');
        $activity  = new SelfFlaw();
        $result = $activity ->save(['status'=>-1,'create_user'=>$_SESSION['think']['user_auth']['id']],['id'=>$id]);
        if($result) {
            return $this->success('删除成功');
        } else {
            return $this->error($activity->getError());
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
                'status' => 1
            );
            $infoes = SelfFlaw::where($info)->select();
            foreach($infoes as $value){
                switch ($value['type']){
                    case 1:
                        $value['title'] = '【流动党员】'.$value['title'];
                        break;

                    default;
                }
            }
            return $this->success($infoes);
        }else{
            //新闻消息列表
            $map = array(
                'class'  => 4,  //流动党 员
                'status' => array('egt',-1),
            );
            $list=$this->lists('Push',$map);
            int_to_string($list,array(
                'status' => array(-1 => '不通过', 0 => '未审核', 1=> '已发送')
            ));
            //数据重组
            foreach($list as $value){
                $msg = SelfFlaw::where('id',$value['focus_main'])->find();
                switch ($msg['type']){
                    case 1:
                        $value['type'] = '流动党员';
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
                'status' => 1
            );
            $infoes = SelfFlaw::where($info)->select();
            foreach($infoes as $value){
                switch ($value['type']){
                    case 1:
                        $value['title'] = '【流动党员】'.$value['title'];
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
            $info1 = SelfFlaw::where('id',$arr1)->find();
        }
        $update['push'] = '1';
        $title1 = $info1['title'];
        SelfFlaw::where(['id'=>$arr1])->update($update); // 更新推送后的状态
        $str1 = strip_tags($info1['content']);
        $des1 = mb_substr($str1,0,40);
        $content1 = str_replace("&nbsp;","",$des1);  //空格符替换成空
        $pre = '【流动党员】';
        $url1 = hostUrl."/home/Flaw/detail/id/".$info1['id'].".html";
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
                SelfFlaw::where(['id'=>$value])->update($update); // 更新推送后的状态
                $info2 = SelfFlaw::where('id',$value)->find();
                $title2 = $info2['title'];
                $str2 = strip_tags($info2['content']);
                $des2 = mb_substr($str2,0,40);
                $content2 = str_replace("&nbsp;","",$des2);  //空格符替换成空
                $pre1 = '【流动党员】';
                $url2 = hostUrl."/home/Flaw/detail/id/".$info2['id'].".html";
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
            $data['class'] = 4;  //
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
}