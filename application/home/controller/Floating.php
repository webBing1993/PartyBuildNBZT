<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15 0015
 * Time: 下午 1:17
 */

namespace app\home\controller;


class Floating  extends Base
{
    /**
     * 流动党员排行榜
     */
    public function rank(){

        return $this->fetch();

    }
}