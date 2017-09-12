<?php
/*
Plugin Name: WP REST API For App
Plugin URI: http://www.watch-life.net
Description: 为微信小程序、app提供定制WordPress rest api
Version: 0.5
Author: jianbo
Author URI: http://www.watch-life.net
License: GPL v3
*/


define('WP_REST_API_FOR_APP_PLUGIN_DIR', plugin_dir_path(__FILE__));
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'weixin-openid.php');    // 获取微信openid
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'allow-anonymous-comments.php');    // 开启匿名评论
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'category-cover.php');    // 设置分类的微信小程序封面
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'top-hot-posts.php');    // 获取热门文章
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'addpageview.php');    // 更新文章浏览数
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'custom-fields-rest-prepare-post.php');    // 自定义文章输出的字段
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'post-like.php');    // 点赞
//include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'weixin-praise.php');    //赞赏 
include(WP_REST_API_FOR_APP_PLUGIN_DIR . 'api.php');    // 公用函数