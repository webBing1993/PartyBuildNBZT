<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15 0015
 * Time: 下午 1:17
 */

namespace app\home\controller;

namespace app\home\controller;
use app\home\model\WechatUser;
use app\admin\model\Question;
use app\home\model\Answer;
use app\home\model\Study;
use app\home\model\Redfilm;
use app\home\model\Redbook;
use app\home\model\RedbookRead;
use app\home\model\Redmusic;
use app\home\model\Picture;
use think\Db;
class Floating  extends Base
{
    /**
     * 流动党员排行榜
     */
    public function rank(){

        return $this->fetch();

    }

    /**
     * 两学一做
     */
    public function index(){
        $Study = new Study();
        //数据列表
        $map = ['status' => array('egt',0),'recommend' => 1];
        $mapp = ['status' => ['egt',0]];
        $top = $Study->get_list($map,0,true);  // 推荐
        $list = $Study->get_list($mapp);  // 列表
        $this->assign('top',$top);
        $this ->assign('list',$list);
        return $this->fetch();

    }

    /**
     * 图文详情
     */
    public function article(){
        $this->anonymous();        //判断是否是游客
        $this->jssdk();
        $id = input('id');
        $list = $this->content(3,$id);
        $this->assign('detail',$list);
        return $this->fetch();
    }
    /**
     * 视频课程
     */
    public function video(){
        $this->anonymous();        //判断是否是游客
        $this->jssdk();
        $id = input('id');
        $list = $this->content(3,$id);
        $this->assign('detail',$list);
        return $this->fetch();
    }
}