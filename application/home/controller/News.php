<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/12
 * Time: 15:56
 */

namespace app\home\controller;

use app\home\model\News as NewsModel;
use think\Db;
/**
 * Class News
 * @package 箬横动态
 */
class News extends Base {
    /**
     * 主页
     */
    public function index(){
        $NewsModel = new NewsModel();
        $map = ['status' => ['eq',1],'type' => 1];
        $left = $NewsModel->get_list($map);
        $mapp = ['status' => ['eq',1],'type' => 2];
        $right = $NewsModel->get_list($mapp);
        $data=Db::table('pb_news')->where('status',1)->order('create_time desc')->limit(3)->select();
        foreach ($data as $key=>$v) {
            $list = Db::table('pb_picture')->where('id', $v['front_cover'])->find();
            $data[$key]['front_cover'] = $list['path'];
        }

        $this->assign('data',$data);
        $this->assign('left',$left); // 基层建设
        $this->assign('right',$right);  // 党政建设
        return $this->fetch();
    }

    /**
     * 详情页
     */
    public function detail(){
        $this->anonymous();
        $this->jssdk();
        $id = input('id/d');
        $info = $this->content(2,$id);
        $this->assign('detail',$info);
        return $this->fetch();
    }

    /**
     * 列表加载更多
     */
    public function listmore(){
        $NewsModel = new NewsModel();
        $len = input('length');
        $c = input('type');
        if ($c == 0){
            $type = 1;  //基层动态
        }else{
            $type = 2; //党建动态
        }
        $map = ['status' => 1, 'type' => $type];
        $list = $NewsModel->get_list($map,$len);
        if ($list){
            return $this->success('加载成功','',$list);
        }else{
            return $this->error('加载失败');
        }
    }

}