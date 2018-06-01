<?php
/*
Plugin Name: WP REST API For App
Plugin URI: http://www.watch-life.net
Description: 为微信小程序、app提供定制WordPress rest api
Version: 2.0
Author: jianbo
Author URI: http://www.watch-life.net
License: GPL v3
*/


define('WP_REST_API_FOR_APP_PLUGIN_DIR', plugin_dir_path(__FILE__));
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'weixin-openid.php');    // 获取微信openid
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'allow-anonymous-comments.php');    // 开启匿名评论
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'category-cover.php');    // 设置分类的微信小程序封面
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'top-hot-posts.php');    // 获取热门文章
//include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'addpageview.php');    // 更新文章浏览数

include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'custom-fields-rest-prepare-post.php');    // 自定义文章输出的字段
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'post-like.php');    // 点赞
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'weixin-praise.php');    //赞赏 
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'api.php');    // 公用函数
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'weixin-comment.php');    // 微信提交评论
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'weixin-send-message.php');    // 发送微信模版消息
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'do-not-show-users.php');    // 不显示用户列表
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'category-subscription.php');    // 分类订阅，显示，取消
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'custom-fields-rest_prepare_comment.php');    // 自定义评论输出的字段
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'weixin-qrcode.php');    // 创建海报
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'video-content.php');    //解析腾讯视频
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'weixin-config.php');    //微信小程序配置
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'weixin-enablecomment.php');    //微信小程序开启评论
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'getcomments.php');    //获取评论和回复

