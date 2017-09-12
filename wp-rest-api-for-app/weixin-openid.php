<?php
//获取用户的微信 openid
add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'weixin/getopenid', array(
    'methods' => 'POST',
    'callback' => 'getOpenid'
  ) );
} );


function getOpenid($request) {
    $js_code= $request['js_code'];
    $encryptedData=$request['encryptedData'];
    $iv=$request['iv'];
    $avatarUrl=$request['avatarUrl'];
    if(empty($js_code))
    {
        return new WP_Error( 'error', 'js_code is empty', array( 'status' => 500 ) );
    }
    else if(!function_exists('curl_init')) {
        return new WP_Error( 'error', 'php  curl is not enabled ', array( 'status' => 500 ) );
    }
    
    else
    {
        $data=post_openid_json($js_code,$encryptedData,$iv,$avatarUrl); 
        if (empty($data)) {
            return new WP_Error( 'error', 'get openid error', array( 'status' => 404 ) );
          }  
        // Create the response object
         $response = new WP_REST_Response($data); 
        // Add a custom status code
         $response->set_status( 201 ); 
        // Add a custom header
        //$response->header( 'Location', 'https://www.watch-life.net' );
        return $response;
    
    }    
    
}
function post_openid_json($js_code,$encryptedData,$iv,$avatarUrl) {


        $appid = get_option('wf_appid');
        $appsecret = get_option('wf_secret');
        if(empty($appid) || empty($appsecret) )
        {
                $result["code"]="success";
                $result["message"]= "appid  or  appsecret is  empty";
                $result["status"]="500";                   
                return $result;
        }
        else
        {
        
            $access_url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$appsecret."&js_code=".$js_code."&grant_type=authorization_code";
            $access_result = https_request($access_url);
            if($access_result !="ERROR")
            {
                $access_array = json_decode($access_result,true);
                if(empty($access_array['errcode']))
                {
                    $openid = $access_array['openid']; 
                    if(strpos(get_option('home'),'www.zhaen.com'))
                    {
                    
                        $sessionKey = $access_array['session_key'];                    
                        $pc = new WXBizDataCrypt($appid, $sessionKey);
                        $errCode = $pc->decryptData($encryptedData, $iv, $data );
                        if ($errCode == 0) {
                        
                            if(!username_exists($openid))
                            {
                                $data =json_decode($data,true);
                                $unionId = $data['unionId'];
                                
                                $userdata = array(
                                    'user_login'  =>  $openid,
                                    'user_nicename'=> $unionId,
                                    'display_name' => $avatarUrl,
                                    'user_pass'   =>  NULL 
                                );

                                    $user_id = wp_insert_user( $userdata ) ;                    
                                    if (is_wp_error( $user_id ) ) {
                                    
                                        $result["code"]="success";
                                        $result["message"]= "insert openid error";
                                        $result["status"]="500";                   
                                        return $result;
                                        
                                    }
                                    else
                                    {
                                        $result["code"]="success";
                                        $result["message"]= "get  openid success  ";
                                        $result["status"]="201";
                                        $result["openid"]=$openid;
                                        return $result;
                                    }
                            
                            }
                            else
                            {
                                $result["code"]="success";
                                $result["message"]= "get  openid success  ";
                                $result["status"]="201";
                                $result["openid"]=$openid;
                                return $result;
                            }
                            
                        }
                        else {
                        
                            $result["code"]="success";
                            $result["message"]=$errCode;
                            $result["status"]="500";                   
                            return $result;
                            
                        }
                    
                    }                    
                    else
                    {
                    
                    
                         if(!username_exists($openid))
                            { 
                                $userdata = array(
                                    'user_login'  =>  $openid,                                    
                                    'user_pass'   =>  NULL 
                                );

                                    $user_id = wp_insert_user( $userdata ) ;                    
                                    if (is_wp_error( $user_id ) ) {
                                    
                                        $result["code"]="success";
                                        $result["message"]= "insert openid error";
                                        $result["status"]="500";                   
                                        return $result;
                                        
                                    }
                                    else
                                    {
                                        $result["code"]="success";
                                        $result["message"]= "get  openid success  ";
                                        $result["status"]="201";
                                        $result["openid"]=$openid;
                                        return $result;
                                    }
                            
                            }
                            else
                            {
                                $result["code"]="success";
                                $result["message"]= "get  openid success  ";
                                $result["status"]="201";
                                $result["openid"]=$openid;
                                return $result;
                            }
                    
                        
                        
                    }
                                       
                    
                    
                    
                }               
                else
                {
                
                    $result["code"]=$access_array['errcode'];
                    $result["message"]= $access_array['errmsg'];
                    $result["status"]="500";                   
                    return $result;
                
                }
                
            }
            else
            {
                    $result["code"]="success";
                    $result["message"]= "https request error";
                    $result["status"]="500";                   
                    return $result;
            }
            
            
        }
}

//发起https请求
function https_request($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl,  CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);  
        $data = curl_exec($curl);
        if (curl_errno($curl)){
            return 'ERROR';
        }
        curl_close($curl);
        return $data;
    }
    
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
       

        
        
    </table>
    <?php submit_button();?>
</form>
</div>
<?php }
 
//禁止在rest api里显示用户列表
 add_filter( 'rest_endpoints', function( $endpoints ){
    if ( isset( $endpoints['/wp/v2/users'] ) ) {
        unset( $endpoints['/wp/v2/users'] );
    }
    if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
        unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    }
    return $endpoints;
});



/**
 * error code 说明.
 * <ul>

 *    <li>-41001: encodingAesKey 非法</li>
 *    <li>-41003: aes 解密失败</li>
 *    <li>-41004: 解密后得到的buffer非法</li>
 *    <li>-41005: base64加密失败</li>
 *    <li>-41016: base64解密失败</li>
 * </ul>
 */
class ErrorCode
{
	public static $OK = 0;
	public static $IllegalAesKey = -41001;
	public static $IllegalIv = -41002;
	public static $IllegalBuffer = -41003;
	public static $DecodeBase64Error = -41004;
}



/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder
{
	public static $block_size = 16;

	/**
	 * 对需要加密的明文进行填充补位
	 * @param $text 需要进行填充补位操作的明文
	 * @return 补齐明文字符串
	 */
	function encode( $text )
	{
		$block_size = PKCS7Encoder::$block_size;
		$text_length = strlen( $text );
		//计算需要填充的位数
		$amount_to_pad = PKCS7Encoder::$block_size - ( $text_length % PKCS7Encoder::$block_size );
		if ( $amount_to_pad == 0 ) {
			$amount_to_pad = PKCS7Encoder::block_size;
		}
		//获得补位所用的字符
		$pad_chr = chr( $amount_to_pad );
		$tmp = "";
		for ( $index = 0; $index < $amount_to_pad; $index++ ) {
			$tmp .= $pad_chr;
		}
		return $text . $tmp;
	}

	/**
	 * 对解密后的明文进行补位删除
	 * @param decrypted 解密后的明文
	 * @return 删除填充补位后的明文
	 */
	function decode($text)
	{

		$pad = ord(substr($text, -1));
		if ($pad < 1 || $pad > 32) {
			$pad = 0;
		}
		return substr($text, 0, (strlen($text) - $pad));
	}

}

/**
 * Prpcrypt class
 *
 * 
 */
class Prpcrypt
{
	public $key;

	function __construct( $k )
	{
		$this->key = $k;
	}

	/**
	 * 对密文进行解密
	 * @param string $aesCipher 需要解密的密文
     * @param string $aesIV 解密的初始向量
	 * @return string 解密得到的明文
	 */
	public function decrypt( $aesCipher, $aesIV )
	{

		try {
			
			$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
			
			mcrypt_generic_init($module, $this->key, $aesIV);

			//解密
			$decrypted = mdecrypt_generic($module, $aesCipher);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
		} catch (Exception $e) {
			return array(ErrorCode::$IllegalBuffer, null);
		}


		try {
			//去除补位字符
			$pkc_encoder = new PKCS7Encoder;
			$result = $pkc_encoder->decode($decrypted);

		} catch (Exception $e) {
			//print $e;
			return array(ErrorCode::$IllegalBuffer, null);
		}
		return array(0, $result);
	}
}


class WXBizDataCrypt
{
    private $appid;
	private $sessionKey;

	/**
	 * 构造函数
	 * @param $sessionKey string 用户在小程序登录后获取的会话密钥
	 * @param $appid string 小程序的appid
	 */
	public function __construct( $appid, $sessionKey)
	{
		$this->sessionKey = $sessionKey;
		$this->appid = $appid;
	}


	/**
	 * 检验数据的真实性，并且获取解密后的明文.
	 * @param $encryptedData string 加密的用户数据
	 * @param $iv string 与用户数据一同返回的初始向量
	 * @param $data string 解密后的原文
     *
	 * @return int 成功0，失败返回对应的错误码
	 */
	 public function decryptData( $encryptedData, $iv, &$data )
	{
		if (strlen($this->sessionKey) != 24) {
			return ErrorCode::$IllegalAesKey;
		}
		$aesKey=base64_decode($this->sessionKey);

        
		if (strlen($iv) != 24) {
			return ErrorCode::$IllegalIv;
		}
		$aesIV=base64_decode($iv);

		$aesCipher=base64_decode($encryptedData);

		$pc = new Prpcrypt($aesKey);
		$result = $pc->decrypt($aesCipher,$aesIV);
        
		if ($result[0] != 0) {
			return $result[0];
		}
     
        $dataObj=json_decode( $result[1] );
        if( $dataObj  == NULL )
        {
            return ErrorCode::$IllegalBuffer;
        }
        if( $dataObj->watermark->appid != $this->appid )
        {
            return ErrorCode::$IllegalBuffer;
        }
		$data = $result[1];
		return ErrorCode::$OK;
	}

}

