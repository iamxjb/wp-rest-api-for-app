<?php
 // 微信小程序设置菜单
add_action('admin_menu', 'weixinapp_create_menu');
function weixinapp_create_menu() {
    // 创建新的顶级菜单
    add_options_page('微信小程序设置', '微信小程序设置', 'administrator', 'weixinapp_slug', 'weixinapp_settings_page', '');
    // 调用注册设置函数
    add_action( 'admin_init', 'register_weixinappsettings' );
}

function register_weixinappsettings() {
    // 注册设置
    register_setting( 'weixinapp-group', 'wf_appid' );
    register_setting( 'weixinapp-group', 'wf_secret' );
    register_setting( 'weixinapp-group', 'wf_swipe' );
    register_setting( 'weixinapp-group', 'wf_poster_imageurl' );
    register_setting( 'weixinapp-group', 'wf_enable_comment_option' );
       
    
    
}

function weixinapp_settings_page() {
?>
<div class="wrap">
<h2>微信小程序设置</h2>
<form method="post" action="options.php">
    <?php settings_fields( 'weixinapp-group' ); ?>
    <?php do_settings_sections( 'weixinapp-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">AppID</th>
        <td><input type="text" name="wf_appid" style="width:400px" value="<?php echo esc_attr( get_option('wf_appid') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">AppSecret</th>
        <td><input type="text" name="wf_secret" style="width:400px" value="<?php echo esc_attr( get_option('wf_secret') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">小程序首页滑动文章ID</th>
        <td><input type="text" name="wf_swipe" style="width:400px" value="<?php echo esc_attr( get_option('wf_swipe') ); ?>" />(请用英文半角逗号分隔)</td>
        </tr>

        <tr valign="top">
        <th scope="row">开启小程序的评论</th>
        <td>

            <?php

            $wf_enable_comment_option =get_option('wf_enable_comment_option');            
            $checkbox=empty($wf_enable_comment_option)?'':'checked';
            echo '<input name="wf_enable_comment_option"  type="checkbox"  value="1" '.$checkbox. ' />';
            

                       ?>
        </td>
        </tr>     

        <tr valign="top">
        <th scope="row">海报图片默认地址</th>
        <td><input type="text" name="wf_poster_imageurl" style="width:600px" value="<?php echo esc_attr( get_option('wf_poster_imageurl') ); ?>" /><br/>(请输完整的图片地址,例如:<span style="color: blue">https://www.watch-life.net/images/2017/06/winxinapp-wordpress-watch-life-new-700.jpg</span>)</td>
        </tr>
               
    </table>
    
    <?php submit_button();?>
</form>
</div>
<?php }  ?>

<?php 


//获取是否启动小程序评论选项
add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', '/options/enableComment', array(
    'methods' => 'GET',
    'callback' => 'getEnableComment'    
  ) );
} );


function getEnableComment($data) {
$data=get_enableComment_json(); 
if ( empty( $data ) ) {
    return new WP_Error( 'no  options', 'no  options', array( 'status' => 404 ) );
  } 
// Create the response object
$response = new WP_REST_Response( $data ); 
// Add a custom status code
$response->set_status( 200 );
return $response;
}

function get_enableComment_json() {
    $wf_enable_comment_option  =get_option('wf_enable_comment_option');
    if(empty($wf_enable_comment_option ))
    {
        $result["code"]="success";
        $result["message"]= "get  enableComment success  ";
        $result["status"]="200";
        $result["enableComment"]="0";
        return $result;
    }
    else
    {
        $result["code"]="success";
        $result["message"]= "get  enableComment success  ";
        $result["status"]="200";
        $result["enableComment"]="1";
        return $result;
    }

}







 



