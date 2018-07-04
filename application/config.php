<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

return [
    'url_route_on' => true,
//    'log'          => [
//        'type' => 'trace', // 支持 socket trace file
//    ],

    /* 默认模块和控制器 */
    'default_module' => 'home',
    'app_debug' => true,

    /* URL配置 */
    'base_url'=>'',
    'parse_str'=>[
        '__ROOT__' => '/',
        '__STATIC__' => '/static',
        '__ADMIN__' => '/admin',
        '__HOME__' => '/home',
    ],
    
    /* 企业配置 */
    'party' => array(
//        'token' => 'ZfqkOC4Fhd7D',
//        'encodingaeskey' => 'SWWdiNibALNG3hZfvqqAzQ48rmibi5KTc1JnqouajTC',
        'appid' => ' ',
        'appsecret' => ' ',
    ),
     /*个人中心*/
     'user' => array(
         'login' => ' ',
//        'token' => 'ZfqkOC4Fhd7D',
//        'encodingaeskey' => 'SWWdiNibALNG3hZfvqqAzQ48rmibi5KTc1JnqouajTC',
         'appid' => ' ',
         'appsecret' => ' ',
         'agentid' => 1000005
     ),
    /*消息审核*/
    'review' => array(
        'appid' => ' ',
        'appsecret' => ' ',
        'agentid' => 1000004
    ),
    /*活动签到*/
    'sign' => array(
        'appid' => ' ',
        'appsecret' => ' ',
        'agentid' => 1000003
    ),
    /*随手拍*/
    'pic' => array(
        'appid' => ' ',
        'appsecret' => ' ',
        'agentid' => 1000003
    ),
    // 第一聚焦
    'news' => array(
        'appid' => ' ',
        'appsecret' => ' ',
        'agentid' => 1000003
    ),
    // 通知公告
    'notice' => array(
        'appid' => ' ',
        'appsecret' => ' ',
        'agentid' => 1000003
    ),
    // 党员教育
    'learn' => array(
        'appid' => ' ',
        'appsecret' => ' ',
        'agentid' => 1000003
    ),
    /* UC用户中心配置 */
    'uc_auth_key' => '(.t!)=JTb_OPCkrD:-i"QEz6KLGq5glnf^[{p;je',

    // 民情日志 大领导权限
    define('tagId',5),
    define('hostUrl','http://nbzt.0571ztnet.com'),
//    define('toUser','@all')
    define('toUser','13588359175'),
    // 审核标签
    define('toTag',1),
];
