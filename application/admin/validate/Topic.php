<?php
/**
 * Created by PhpStorm.
 * User: laowang
 * Date: 2018/7/3
 * Time: 下午3:11
 */

namespace app\admin\validate;


use think\Validate;

class Topic extends Validate
{
    protected $rule = [
        'front_cover' => 'require',
        'title' => 'require',
        'content' => 'require',
        'publisher' => 'require',
    ];
    protected $message = [
        'title.require' => '标题不能为空',
        'content.require' => '内容不能为空',
        'publisher.require' => '发布人不能为空',
        'front_cover.require' => '封面图片不能为空',
    ];
    protected $scene = [
        'act' => ['title','front_cover'],
        'other' => ['title','content','publisher','front_cover'],
    ];
}