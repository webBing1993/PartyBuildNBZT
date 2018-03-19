<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/12
 * Time: 16:12
 */

namespace app\home\controller;
use app\home\model\Special;

/**
 * Class Notice
 * @package  通知公告
 */
class Notice extends Base {
    /**
     * 主页
     */
    public function index(){
        $Special = new Special();
        $map = ['status' => 1,'type' => 1];
        $left = $Special->get_list($map);
        $mapp = ['status' => 1,'type' => 2];
        $right = $Special->get_list($mapp);
        $this->assign('left',$left); // 政策解读
        $this->assign('right',$right);  // 通知公告
        return $this->fetch();
    }
    /**
     * 列表加载更多
     */
    public function more(){
        $Special = new Special();
        $len = input('length');
        $c = input('type');
        if ($c == 0){
            $type = 1;  //政策解读
        }else{
            $type = 2; //通知公告
        }
        $map = ['status' => 1, 'type' => $type];
        $list = $Special->get_list($map,$len);
        if ($list){
            return $this->success('加载成功','',$list);
        }else{
            return $this->error('加载失败');
        }
    }
    /**
     * 详情页
     */
    public function detail(){
        $this->anonymous();
        $this->jssdk();
        $id = input('id/d');
        $info = $this->content(1,$id);
        $this->assign('detail',$info);
        return $this->fetch();

    }
}