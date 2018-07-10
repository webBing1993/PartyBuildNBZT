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
    'app_debug' => false,

    /* URL配置 */
    'base_url'=>'',
    'parse_str'=>[
        '__ROOT__' => '/',
        '__STATIC__' => '/static',
        '__ADMIN__' => '/admin',
        '__HOME__' => '/home',
    ],
    
    /* 企业配置 */
     /*个人中心*/
     'user' => array(
         'login' => 'http://nbzt.0571ztnet.com/home/verify/lpgin',
         'appid' => 'wxdf4be446ff21c5a9',
         'appsecret' => 'YnBzPC7We-XPCMd8ZtTIQjuKwq6RRF_6iUqmapTe37w',
         'agentid' => 1000005
     ),
    /*消息审核*/
    'review' => array(
        'appid' => 'wxdf4be446ff21c5a9',
        'appsecret' => 'rOA9iV9EftswBOnpZuE3hr8H7fDpyEtq_WtbNeWSags',
        'agentid' => 1000011
    ),
    /*活动签到*/
    'sign' => array(
        'appid' => 'wxdf4be446ff21c5a9',
        'appsecret' => 'AIMz09CjG0nbioNpTHsk5T-XRn3EO4u_I4hczB5HZgM',
        'agentid' => 1000006
    ),
    /*随手拍*/
    'pic' => array(
        'appid' => 'wxdf4be446ff21c5a9',
        'appsecret' => '5OJomHoe_y8srFN0eit2ZW9Lz2OgJdmO0F9Fxjgku58',
        'agentid' => 1000010
    ),
    // 第一聚焦
    'news' => array(
        'appid' => 'wxdf4be446ff21c5a9',
        'appsecret' => 'BH2WX_Ml4oxHUGPkLEyfxojR9oI0wOzmNYWnrRzda-k',
        'agentid' => 1000002
    ),
    // 通知公告
    'notice' => array(
        'appid' => 'wxdf4be446ff21c5a9',
        'appsecret' => 'ISgan4-RwOxqUU41f00o7FBQKkYmxZYPc9bg8Uuw3iU',
        'agentid' => 1000003
    ),
    // 党员教育
    'learn' => array(
        'appid' => 'wxdf4be446ff21c5a9',
        'appsecret' => 'sfiyHsfzDuaoJEVZIKkSD5xQij3CWJ77TC-ObbBecro',
        'agentid' => 1000007
    ),
    // 通讯录
    'book' => [
        'appid' => 'wxdf4be446ff21c5a9',
        'appsecret' => '5PWyqG3q7a9TTdZBtsZ0B-OUjN8SH8lpU6-G1FVqgDg',
    ],
    /* UC用户中心配置 */
    'uc_auth_key' => '(.t!)=JTb_OPCkrD:-i"QEz6KLGq5glnf^[{p;je',

    // 民情日志 大领导权限
    define('tagId',1),
    // 会议签到  扫码权限
    define('scanId',2),
    define('hostUrl','http://nbzt.0571ztnet.com'),
//    define('toUser','@all')
    define('toUser','13588359175'),
    // 审核标签
    define('toTag',3),
];
