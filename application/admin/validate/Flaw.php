<?php
/**
 * Created by PhpStorm.
 * User: ali
 * Date: 2017/12/22
 * Time: 14:10
 */
namespace app\admin\validate;


use think\Validate;

class Flaw extends Validate {
    protected $rule   = [
        'front_cover' =>  'require',
        'title'       =>  'require',
        'content'     =>  'require',
        'publisher'   =>  'require',
    ];

    protected $message = [
        'front_cover.require'  =>  '请添加封面图片！',
        'title.require'        =>  '请添加标题！',
        'content.require'      =>  '请填写内容！',
        'publisher.require'    =>  '请填写发布人'
    ];

}