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
        'appid' => 'ww678237a01774a2fb',
        'appsecret' => 'pyXyO5cw8DfEbFr6dG-veoVo8Lv7izqMZoakYTAymro',
    ),
     /*个人中心*/
     'user' => array(
         'login' => 'http://rh.0571ztnet.com/home/verify/login',
//        'token' => 'ZfqkOC4Fhd7D',
//        'encodingaeskey' => 'SWWdiNibALNG3hZfvqqAzQ48rmibi5KTc1JnqouajTC',
         'appid' => 'ww678237a01774a2fb',
         'appsecret' => 'HdHeFsMy_5ezrO90p_fSqC_v21nPXucM5WeBpfOVYMA',
         'agentid' => 1000005
     ),
    /*消息审核*/
    'review' => array(
        'appid' => 'ww678237a01774a2fb',
        'appsecret' => 'wzp_OlcYTgH2IjTbjFgA7oLrF8zXHhQXc4Oaz9KVwwo',
        'agentid' => 1000004
    ),
    /*活动签到*/
    'sign' => array(
        'appid' => 'ww678237a01774a2fb',
        'appsecret' => '-njGAjJ7EnSKMXlzSoUxcJO6RDGhYtbVEiY5KSiry3s',
        'agentid' => 1000003
    ),
    /* UC用户中心配置 */
    'uc_auth_key' => '(.t!)=JTb_OPCkrD:-i"QEz6KLGq5glnf^[{p;je',
    /*直播地址*/

    'live_path' => 'http://pullhls36734237.live.126.net/live/6cb0985ea48d4af6876a397c0858f3d4/playlist.m3u8'
];
