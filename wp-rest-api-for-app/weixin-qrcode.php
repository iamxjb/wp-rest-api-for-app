<?php
//获取二维码海报
add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'weixin/qrcode', array(
    'methods' => 'POST',
    'callback' => 'getWinxinQrcode'
  ) );
} );


function getWinxinQrcode($request) {
      $postid= $request['postid'];
      $postImageUrl= !empty($request['postImageUrl'])?$request['postImageUrl']:''; 
      $title=   $request['title'];  
      $path=$request['path'];
      $openid =$request['openid']; 

    if(empty($openid) || empty($postid)  || empty($path) || empty($title))
        {
            return new WP_Error( 'error', ' openid   or postid or path   or title is  empty', array( 'status' => 500 ) );
        }
        else if(get_post($postid)==null)
        {
             return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
        }
        else
        {
              if(!username_exists($openid))
            {
                return new WP_Error( 'error', 'Not allowed to submit', array('status' => 500 ));
            }
            else if(is_wp_error(get_post($postid)))
            {
                 return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
            }
            else
            {

              $data=get_weixin_qcrode_json($postid,$path,$postImageUrl,$title); 
              if (empty($data)) {
                  return new WP_Error( 'error', 'creat qrcode error', array( 'status' => 404 ) );
                }
               $response = new WP_REST_Response($data);
               $response->set_status( 200 ); 
               return $response;

            }
        }

    	

}

function get_weixin_qcrode_json($postid,$path,$postImageUrl,$title){      
      $postimg=empty($postImageUrl)?get_option('wf_poster_imageurl'):$postImageUrl;      
      $qrcodeName = 'qrcode-'.$postid.'.png';//文章小程序二维码文件名
      $fileName = 'poster-'.$postid.'.jpg';//生成海报文件名
      $qrcodeurl = WP_REST_API_FOR_APP_PLUGIN_DIR.'qrcode/'.$qrcodeName;//文章小程序二维码路径
      $posterurl = WP_REST_API_FOR_APP_PLUGIN_DIR.'poster/'.$fileName;//生成海报临时存放路径
      $fonturl = WP_REST_API_FOR_APP_PLUGIN_DIR.'fonts/msyh.ttc'; //文字字体所在路径
      //自定义参数区域，可自行设置      
      $appid = get_option('wf_appid');
      $appsecret = get_option('wf_secret');
      $use_bgimg = false;//是否用预置背景图，请自行将600x900大小的图片放到backimg文件夹，会随机选取
      $bg_width = 580;//背景画布的宽度
      $margin = 5;//特色图在海报中的边距宽度，0为无边距
      $radius = 15;//特色图圆角数值，值越大越圆
      $gap = 10;//图文等元素的间距
      $qrcodesize = 180;//小程序二维码显示大小
      $title_font = 20;//文章标题字号      
      $scan_font = 14;//扫码提示语字号     
      $scan = '阅读文章,请长按识别二维码';
      $flag=false;

      //判断文章小程序二维码是否存在，如不存在，在此生成并保存
      if(!is_file($qrcodeurl)) {
          //$ACCESS_TOKEN = getAccessToken($appid,$appsecret,$access_token);
          $access_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
           $access_token_result = https_request($access_token_url);
           if($access_token_result !="ERROR")
            {
              $access_token_array= json_decode($access_token_result,true);
              if(empty($access_token_array['errcode']))
              {
                $access_token =$access_token_array['access_token'];
                if(!empty($access_token))
                {

                  //接口A小程序码,总数10万个（永久有效，扫码进入path对应的动态页面）
                  $url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token='.$access_token;
                  //接口B小程序码,不限制数量（永久有效，将统一打开首页，可根据scene跟踪推广人员或场景）
                  //$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$ACCESS_TOKEN;
                  //接口C小程序二维码,总数10万个（永久有效，扫码进入path对应的动态页面）
                  //$url = 'http://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token='.$ACCESS_TOKEN;

                  //header('content-type:image/png');
                  $color = array(
                      "r" => "0",  //这个颜色码自己到Photoshop里设
                      "g" => "0",  //这个颜色码自己到Photoshop里设
                      "b" => "0",  //这个颜色码自己到Photoshop里设
                  );
                  $data = array(
                      //$data['scene'] = "scene";//自定义信息，可以填写诸如识别用户身份的字段，注意用中文时的情况
                      //$data['page'] = "pages/index/index";//扫码后对应的path，只能是固定页面
                      'path' => $path, //前端传过来的页面path
                      'width' => intval(100), //设置二维码尺寸
                      'auto_color' => false,
                      'line_color' => $color,
                  );
                  $data = json_encode($data);
                  //可在此处添加或者减少来自前端的字段
                  $QRCode = get_content_post($url,$data);//小程序二维码
                  if(empty($QRCode['errcode']))
                  {
                    //输出二维码
                    file_put_contents($qrcodeurl,$QRCode);
                    //imagedestroy($QRCode);
                    $flag=true;
                  }
                  
                }
                else
                {
                  $flag=false;
                }

              }
              else
              {
                $flag=false;
              }

            }
            else
            {
              $flag=false;
            }
          
      }
      else
      {

        $flag=true;
      }


      if($flag)
      {

         if(!is_file($posterurl))
         {
              //开始合成海报（也可将画布更换成预设图）
              $bg_height = 900 - 2*$gap;//画布高度
              $temp = imagecreatetruecolor($bg_width,$bg_height);
              $color = imagecolorAllocate($temp,255,255,255);//分配一个纯底色，可以更改颜色
              imagefill($temp,0,0,$color);

              //特色图合并到画布上
              $y = $margin;//合并图片时在画布上的高度坐标
              //对特色图进行缩放，以适于画布尺寸，居中显示
              $check_width = $bg_width - 2*$margin;//海报中除了边距之外的宽度
              $newpi = PicCompress($postimg,$check_width);
              $pi_height = imagesy($newpi);
              $new_height = 0.614*$bg_height;
              //加圆角
              $lt_corner = get_lt_rounder_corner($radius, 0xef, 0xef, 0xe1);
              if($pi_height > $new_height){
                $cutpi = imagecreatetruecolor($check_width,$new_height);
                $color = imagecolorAllocate($cutpi,255,255,255);//分配一个白色底色，可以更改颜色
                imagefill($cutpi,0,0,$color);
                imagecopymerge($cutpi,$newpi,0,0,0,0,$check_width,$new_height,100);//裁切图片
                imagecopymerge($temp,$cutpi,$margin,$y,0,0,$check_width,$new_height,100);//海报布局的关键函数
                //圆角图片
                myradus($temp, $margin, $y, $lt_corner, $radius, $new_height, $check_width);
                $pi_height = $new_height;
              }else{
                imagecopymerge($temp,$newpi,$margin,$y,0,0,$check_width,$pi_height,100);//海报布局的关键函数
                myradus($temp, $margin, $y, $lt_corner, $radius, $pi_height, $check_width);
                $gap2 = $new_height - $pi_height;
              }

              //加上标题和其他文字
              $font_pic = FontToPic($title,$fonturl,$title_font,60,$check_width);//文章标题
              //$font2_pic = FontToPic($welcome,$fonturl,$welcome_font,40);//邀请语
              $y = $y + $pi_height + $gap;
              imagecopymerge($temp,$font_pic,$margin,$y+20,0,0,$check_width,60,100);
              //$y = $y + 60 + $gap;
              //imagecopymerge($temp,$font2_pic,$margin,$y,0,0,$check_width,40,100);

              //缩放二维码，然后将其合并到画布
              $qCodePath = PicCompress($qrcodeurl,$qrcodesize);//缩放二维码到指定大小
              $font3_pic = FontToPic($scan,$fonturl,$scan_font,20);
              $y = $y  + $gap + $gap2;
              imagecopymerge($temp, $qCodePath, ($bg_width -$qrcodesize)/2-120, $y+50, 0, 0, $qrcodesize, $qrcodesize, 100);
              $y = $y + $qrcodesize + $gap;
              imagecopymerge($temp,$font3_pic,250,$y-50,0,0,$check_width,20,100);//

              if($use_bgimg){
                  //将画布海报合并到预置背景图上
                  //获取文件夹内所有图片，数组显示
                  $img_array = glob("backimg/*.{gif,jpg,png}",GLOB_BRACE);
                  //从数组中随机选取一个
                  $img = array_rand($img_array);
                  $bg_imges_url = $img_array[$img];
                  $newtemp = imagecreatefromstring(file_get_contents($bg_imges_url));
                  imagecopymerge($newtemp,$temp,$gap,$gap,0,0,$bg_width,$bg_height,80);
                  $temp = $newtemp;
              }

              //保存
              //header('Content-Type:image/jpg');
              imagejpeg($temp,$posterurl,100);//保存图片到指定路径，可自行设置
              imagedestroy($temp);
              //del_file('poster');
         }
        
            $result["code"]="success";
            $result["message"]= "poster  creat  success"; 
            $result["status"]="200"; 
            $result["posterimageurl"]="";
            return $result;

      }
      else {
          $result["code"]="success";
          $result["message"]= "poster  creat  error"; 
          $result["status"]="200"; 
          return $result;
      }	
	  
	}

 
//获取二维码图片
add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'weixin/qrcodeimg', array(
    'methods' => 'POST',
    'callback' => 'getWinxinQrcodeImg'
  ) );
} );


function getWinxinQrcodeImg($request) {
      $postid= $request['postid'];      
      $path=$request['path'];
      $openid =$request['openid']; 

    if(empty($openid) || empty($postid)  || empty($path))
        {
            return new WP_Error( 'error', ' openid   or postid or path   empty', array( 'status' => 500 ) );
        }
        else if(get_post($postid)==null)
        {
             return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
        }
        else
        {
              if(!username_exists($openid))
            {
                return new WP_Error( 'error', 'Not allowed to submit', array('status' => 500 ));
            }
            else if(is_wp_error(get_post($postid)))
            {
                 return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
            }
            else
            {

              $data=get_weixin_qcrodeimg_json($postid,$path); 
              if (empty($data)) {
                  return new WP_Error( 'error', 'creat qrcode error', array( 'status' => 404 ) );
                }
               $response = new WP_REST_Response($data);
               $response->set_status( 200 ); 
               return $response;

            }
        }

      

}

function get_weixin_qcrodeimg_json($postid,$path){      
           
      $qrcodeName = 'qrcode-'.$postid.'.png';//文章小程序二维码文件名     
      $qrcodeurl = WP_REST_API_FOR_APP_PLUGIN_DIR.'qrcode/'.$qrcodeName;//文章小程序二维码路径
      
      
      //自定义参数区域，可自行设置      
      $appid = get_option('wf_appid');
      $appsecret = get_option('wf_secret');
     
      //判断文章小程序二维码是否存在，如不存在，在此生成并保存
      if(!is_file($qrcodeurl)) {
          //$ACCESS_TOKEN = getAccessToken($appid,$appsecret,$access_token);
          $access_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
           $access_token_result = https_request($access_token_url);
           if($access_token_result !="ERROR")
            {
              $access_token_array= json_decode($access_token_result,true);
              if(empty($access_token_array['errcode']))
              {
                $access_token =$access_token_array['access_token'];
                if(!empty($access_token))
                {

                  //接口A小程序码,总数10万个（永久有效，扫码进入path对应的动态页面）
                  $url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token='.$access_token;
                  //接口B小程序码,不限制数量（永久有效，将统一打开首页，可根据scene跟踪推广人员或场景）
                  //$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$ACCESS_TOKEN;
                  //接口C小程序二维码,总数10万个（永久有效，扫码进入path对应的动态页面）
                  //$url = 'http://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token='.$ACCESS_TOKEN;

                  //header('content-type:image/png');
                  $color = array(
                      "r" => "0",  //这个颜色码自己到Photoshop里设
                      "g" => "0",  //这个颜色码自己到Photoshop里设
                      "b" => "0",  //这个颜色码自己到Photoshop里设
                  );
                  $data = array(
                      //$data['scene'] = "scene";//自定义信息，可以填写诸如识别用户身份的字段，注意用中文时的情况
                      //$data['page'] = "pages/index/index";//扫码后对应的path，只能是固定页面
                      'path' => $path, //前端传过来的页面path
                      'width' => intval(100), //设置二维码尺寸
                      'auto_color' => false,
                      'line_color' => $color,
                  );
                  $data = json_encode($data);
                  //可在此处添加或者减少来自前端的字段
                  $QRCode = get_content_post($url,$data);//小程序二维码
                  if($QRCode !='error')
                  {
                    //输出二维码
                    file_put_contents($qrcodeurl,$QRCode);
                    //imagedestroy($QRCode);
                    $flag=true;
                  }
                  
                }
                else
                {
                  $flag=false;
                }

              }
              else
              {
                $flag=false;
              }

            }
            else
            {
              $flag=false;
            }
          
      }
      else
      {

        $flag=true;
      }

      if($flag)
      {
        $result["code"]="success";
          $result["message"]= "qrcode  creat  success"; 
          $result["status"]="200"; 
          return $result;

      }
      else {
          $result["code"]="success";
          $result["message"]= "qrcode  creat  error"; 
          $result["status"]="500"; 
          return $result;
      } 


      
    
  }