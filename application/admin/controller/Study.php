<?php
/**
 * Created by PhpStorm.
 * User: laowang
 * Date: 2017/1/16
 * Time: 15:07
 */
namespace app\admin\controller;

use app\admin\model\Study as StudyModel;
use app\admin\model\Picture;
use app\admin\model\Push;
use com\wechat\TPQYWechat;
use think\Config;

/**
 * Class Learn
 * @package 两学一做
 */
class Study extends Admin {
    /**
     * 主页
     */
    public function index(){
        $map = array(
            'status' => array('egt',0),
            'type'=> array('in',[1,2])
        );
        $list = $this->lists('Study',$map);
        int_to_string($list,array(
            'status' => array(0=>"已发布",1=>"已发布"),
            'recommend' => array(0=>"否",1=>"是"),
            'type' => array(1=>"视频课程",2=>"文章课程")
        ));
        $this->assign('list',$list);

        return $this->fetch();
    }

    /**
     * 添加
     */
    public function add(){
        if(IS_POST){
            $data = input('post.');
            if(empty($data['id'])) {
                unset($data['id']);
            }
            if($data['type'] == 1){
                if($data['video_path'] == "" && $data['net_path'] == ""){
                    return $this->error("请上传视频文件或网址，如文件过大，请耐心等待..");
                }
            }
            $learnModel = new StudyModel();
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            $model = $learnModel->validate('Study')->save($data);
            if($model){
                return $this->success('新增成功!',Url("Study/index"));
            }else{
                return $this->error($learnModel->getError());
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
        if(IS_POST){
            $data = input('post.');
            if($data['type'] == 1){
                if($data['video_path'] == "" && $data['net_path'] == ""){
                    return $this->error("请上传视频文件或网址，如文件过大，请耐心等待..");
                }
            }
            $learnModel = new StudyModel();
            $model = $learnModel->validate('Study')->save($data,['id'=>input('id')]);
            if($model){
                return $this->success('修改成功!',Url("Study/index"));
            }else{
                return $this->get_update_error_msg($learnModel->getError());
            }
        }else{
            //根据id获取课程
            $id = input('id');
            if(empty($id)){
                return $this->error("系统错误,不存在该条数据!");
            }else{
                $msg = StudyModel::get($id);
                $this->assign('msg',$msg);
            }
            return $this->fetch();
        }
    }

    /**
     * 删除
     */
    public function del(){
        $id = input('id');
        $data['status'] = '-1';
        $info = StudyModel::where('id',$id)->update($data);
        if($info) {
            return $this->success("删除成功");
        }else{
            return $this->error("删除失败");
        }
    }
    /**
     * 推送列表
     */
    public function pushlist() {
        if(IS_POST){
            $id = input('id');
            //副图文本周内的新闻消息
            $t = $this->week_time();
            $info = array(
                'id' => array('neq',$id),
                'create_time' => array('egt',$t),
                'type' => array('in',[1,2]),
                'status' => 0,
            );
            $infoes = StudyModel::where($info)->select();
            int_to_string($infoes,array(
                'type' => array(1=>"视频课程",2=>"文章课程"),
            ));
            return $this->success($infoes);
        }else{
            //消息列表
            $map = array(
                'class' => 3, // 两学一做
                'status' => array('egt',-1),
            );
            $list = $this->lists('Push',$map);
            int_to_string($list,array(
                'status' => array(-1 => '不通过', 0 => '未审核', 1=> '已发送'),
            ));
            //数据重组
            foreach ($list as $value) {
                $msg = StudyModel::where('id',$value['focus_main'])->find();
                $value['title'] = $msg['title'];
            }
            $this->assign('list',$list);
            //主图文本周内的新闻消息
            $t = $this->week_time();    //获取本周一时间
            $info = array(
                'create_time' => array('egt',$t),
                'type' => array('in',[1,2]),
                'status' => 0,
            );
            $infoes = StudyModel::where($info)->select();
            int_to_string($infoes,array(
                'type' => array(1=>"视频课程",2=>"文章课程"),
            ));
            $this->assign('info',$infoes);
            return $this->fetch();
        }
    }

    /**
     * 推送
     */
    public function push(){
        $data = input('post.');
        $arr1 = $data['focus_main'];    //主图文id
        isset($data['focus_vice']) ? $arr2 = $data['focus_vice'] : $arr2 = "";    //副图文id
        if($arr1 == -1){
            return $this->error("请选择主图文!");
        }else{
            //主图文信息
            $focus1 = StudyModel::where('id',$arr1)->find();
            StudyModel::where('id',$arr1)->update(['status' => 1]);
            $title1 = $focus1['title'];
            $str1 = strip_tags($focus1['content']);
            $des1 = mb_substr($str1,0,100);
            $content1 = str_replace("&nbsp;","",$des1);  //空格符替换成空
            $pre1 = "【两学一做】";
            switch ($focus1['type']) {
                case 1:  // 视频
                    $url1 = hostUrl."/home/learn/video/id/".$focus1['id'].".html";
                    break;
                case 2:  // 图文
                    $url1 = hostUrl."/home/learn/article/id/".$focus1['id'].".html";
                    break;
                default:
                    break;
            }
            $img1 = Picture::get($focus1['front_cover']);
            $path1 = hostUrl.$img1['path'];
            $information1 = array(
                "title" => $pre1.$title1,
                "description" => $content1,
                "url" => $url1,
                "picurl" => $path1,
            );
        }

        $information = array();
        if(!empty($arr2)) {
            //副图文信息
            $information2 = array();
            foreach ($arr2 as $key=>$value){
                $focus = StudyModel::where('id',$value)->find();
                StudyModel::where('id',$value)->update(['status'=>1]);
                $title = $focus['title'];
                $str = strip_tags($focus['content']);
                $des = mb_substr($str,0,100);
                $content = str_replace("&nbsp;","",$des);  //空格符替换成空
                $pre = "【两学一做】";
                switch ($focus['type']) {
                    case 1:
                        $url = hostUrl."/home/learn/video/id/".$focus['id'].".html";
                        break;
                    case 2:
                        $url = hostUrl."/home/learn/article/id/".$focus['id'].".html";
                        break;
                    default:
                        break;
                }
                $img = Picture::get($focus['front_cover']);
                $path = hostUrl.$img['path'];
                $info = array(
                    "title" => $pre.$title,
                    "description" => $content,
                    "url" => $url,
                    "picurl" => $path,
                );
                $information2[] = $info;
            }

            //数组合并，主图文放在首位
            foreach ($information2 as $k=>$v){
                $information[0] = $information1;
                $information[$k+1] = $v;
            }
        }else{
            $information[0] = $information1;
        }

        //重组成article数据
        $send = array();
        $re[] = $information;
        foreach ($re as $key => $value){
            $key = "articles";
            $send[$key] = $value;
        }

        //发送给服务号
        $Wechat = new TPQYWechat(Config::get('learn'));
        $message = array(
            "touser" => toUser,
            "msgtype" => 'news',
            "agentid" => config('learn.agentid'),  // 个人中心
            "news" => $send,
            "safe" => "0"
        );
        $msg = $Wechat->sendMessage($message);
        if ($msg['errcode'] == 0){
            $data['focus_vice'] ? $data['focus_vice'] = json_encode($data['focus_vice']) : $data['focus_vice'] = null;
            $data['create_user'] = session('user_auth.username');
            $data['status'] = 1;
            $data['class'] = 3;  // 两学一做
            //保存到推送列表
            $s = Push::create($data);
            if ($s){
                return $this->success("发送成功");
            }else{
                return $this->error("发送失败");
            }
        }else{
            return $this->error("发送失败");
        }
    }
}