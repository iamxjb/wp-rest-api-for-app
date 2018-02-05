<?php 
//获取文章的第一张图片
function get_post_content_first_image($post_content){
	if(!$post_content){
		$the_post		= get_post();
		$post_content	= $the_post->post_content;
	} 

	preg_match_all( '/class=[\'"].*?wp-image-([\d]*)[\'"]/i', $post_content, $matches );
	if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	
		$image_id = $matches[1][0];
		if($image_url = get_post_image_url($image_id)){
			return $image_url;
		}
	}

	preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
	if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	   
		return $matches[1][0];
	}
}

//获取文章图片的地址
function get_post_image_url($image_id, $size='full'){
	if($thumb = wp_get_attachment_image_src($image_id, $size)){
		return $thumb[0];
	}
	return false;	
}

function getPostImages($post_content,$post_id){
    $content_first_image= get_post_content_first_image($post_content);
    if(empty($content_first_image))
    {
        $content_first_image='';
    }

    $post_thumbnail_image_150='';
    $post_medium_image_300='';
    $post_thumbnail_image_624=''; 
    $post_thumbnail_image='';           
    $_data =array();
    $thumbnail_id = get_post_thumbnail_id($post_id);
    if($thumbnail_id ){
        $thumb = wp_get_attachment_image_src($thumbnail_id, 'thumbnail');
                $post_thumbnail_image = $thumb[0];
    }
    else if($content_first_image)
    {          
        $attachments = get_attached_media( 'image', $post_id ); //查找文章的附件
        $index = array_keys($attachments);
        $flag=0; 
        
        for ($i = 0; $i < sizeof($index); $i++) {
            $arr =$attachments[$index[$i]];
            $imageName = $arr->{"post_title"};            
            if(strpos($content_first_image,$imageName)!==false){  //附件的名称如果和第一张图片相同,就取这个附件的缩略图
                {
                    $post_thumbnail_image_150 = wp_get_attachment_image_url($arr->{"ID"},'thumbnail');
                    $post_medium_image_300=wp_get_attachment_image_url($arr->{"ID"},'medium');
                    $post_thumbnail_image_624=wp_get_attachment_image_url($arr->{"ID"},'post-thumbnail');
                    $id =$arr->{"ID"};                    
                    $flag++;
                    break;
                }
            }
        }
        if($flag>0)
            {
                $post_thumbnail_image = $post_thumbnail_image_150;
            }
            else
            {
                $post_thumbnail_image = $content_first_image; 
            }          
    }
    else
    {
        $post_thumbnail_image='';
    }   

    if(strlen($post_medium_image_300)>0)
    {
        $_data['post_medium_image_300']=$post_medium_image_300; 
    }
    else
    {
         $_data['post_medium_image_300']=$content_first_image;
    }  
    if(strlen($post_thumbnail_image_624)>0)
    {
        $_data['post_thumbnail_image_624']=$post_thumbnail_image_624; 
    }
    else
    {
         $_data['post_thumbnail_image_624']=$content_first_image;
    }            
    $_data['post_thumbnail_image']=$post_thumbnail_image;
    $_data['content_first_image']=$content_first_image; 
    return  $_data;             
           
}

// function GetIP()
// {
//     foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key)
//     {
//         if (array_key_exists($key, $_SERVER) === true)
//         {
//             foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip)
//             {
//                 if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
//                 {
//                     return $ip;
//                 }
//             }
//         }
//     }
// }


// function httpGet($url) {
//     $curl = curl_init();
//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($curl, CURLOPT_TIMEOUT, 500);
//     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//     curl_setopt($curl, CURLOPT_URL, $url);

//     $res = curl_exec($curl);
//     curl_close($curl);

//     return $res;
// }
//等比例缩小图片，处理二维码
function PicCompress($src,$out_with=100){
    // 获取图片基本信息
    list($width, $height, $type, $attr) = getimagesize($src);
    // 获取图片后缀名
    $pictype = image_type_to_extension($type,false);
    // 拼接方法
    $imagecreatefrom = "imagecreatefrom".$pictype;
    // 打开传入的图片
    $in_pic = $imagecreatefrom($src);
    // 压缩后的图片长宽
    $new_width = $out_with;
    $new_height = $out_with/$width*$height;
    // 生成中间图片
    $temp = imagecreatetruecolor($new_width,$new_height);
    // 图片按比例合并在一起。
    imagecopyresampled($temp,$in_pic,0,0,0,0,$new_width,$new_height,$width,$height);
    // 销毁输入图片
    imagedestroy($in_pic);

    return $temp;

}

//添加文字到图片上，需要设置字体
function FontToPic($text,$font,$font_size=10,$pic_hight=50,$pic_width=300){
    // header("Content-type: image/jpeg");
    mb_internal_encoding("UTF-8");
    $im =imagecreate($pic_width,$pic_hight);
    $background_color = ImageColorAllocate ($im, 255, 255, 255);
    $col = imagecolorallocate($im, 0, 0, 0);
    $come=$text;
    /*水平居中（换行），固定字号*/
    $txt_max_width = intval(0.9*$pic_width);
    $content = "";
    for ($i=0;$i<mb_strlen($come);$i++) {
        $letter[] = mb_substr($come, $i, 1);
    }
    // var_dump($letter);die;
    foreach ($letter as $l) {
        $teststr = $content." ".$l;
        $testbox = imagettfbbox($font_size,0,$font,$teststr);
        // var_dump($testbox);die;
        // 判断拼接后的字符串是否超过预设的宽度
        if (($testbox[2] > $txt_max_width) && ($content !== "")) {
            $content .= "\n";
        }
        $content .= $l;
    }
    $test = explode("\n",$content);
    // var_dump($test);die;
    // $fbox = imagettfbbox(10,0,$font,$come);
    // echo  1;die;
    $txt_width = $testbox[2]-$testbox[0];

    $txt_height = $testbox[0]-$testbox[7];

    $y = ($pic_hight * 0.8)-((count($test)-1)*$txt_height); // baseline of text at 90% of $img_height
    // var_dump($txt_height);die;
    // imagettftext($im,$font_size,0,$x,$y,$col,$font,$content); //写 TTF 文字到图中
    foreach ($test as $key => $value) {
        $textbox = imagettfbbox($font_size,0,$font,$value);
        $txt_height = $textbox[0]-$textbox[7];
        $text_width = $textbox[2]-$textbox[0];
        $x = ($pic_width - $text_width) / 2;
        imagettftext($im,$font_size,0,$x,$y,$col,$font,$value);
        $y = $y+$txt_height+2; // 加2为调整行距
    }

    return $im;

}
/** 画圆角
 * @param $radius 圆角位置
 * @param $color_r 色值0-255
 * @param $color_g 色值0-255
 * @param $color_b 色值0-255
 * @return resource 返回圆角
 */
function get_lt_rounder_corner($radius, $color_r, $color_g, $color_b)
{
    // 创建一个正方形的图像
    $img = imagecreatetruecolor($radius, $radius);
    // 图像的背景
    $bgcolor = imagecolorallocate($img, $color_r, $color_g, $color_b);
    $fgcolor = imagecolorallocate($img, 0, 0, 0);
    imagefill($img, 0, 0, $bgcolor);
    // $radius,$radius：以图像的右下角开始画弧
    // $radius*2, $radius*2：已宽度、高度画弧
    // 180, 270：指定了角度的起始和结束点
    // fgcolor：指定颜色
    imagefilledarc($img, $radius, $radius, $radius * 2, $radius * 2, 180, 270, $fgcolor, IMG_ARC_PIE);
    // 将弧角图片的颜色设置为透明
    imagecolortransparent($img, $fgcolor);
    return $img;
}
/**
 * @param $im  大的背景图，也是我们的画板
 * @param $lt_corner 我们画的圆角
 * @param $radius  圆角的程度
 * @param $image_h 图片的高
 * @param $image_w 图片的宽
 */
function myradus($im, $lift, $top, $lt_corner, $radius, $image_h, $image_w)
{
/// lt(左上角)
    imagecopymerge($im, $lt_corner, $lift, $top, 0, 0, $radius, $radius, 100);
// lb(左下角)
    $lb_corner = imagerotate($lt_corner, 90, 0);
    imagecopymerge($im, $lb_corner, $lift, $image_h - $radius + $top, 0, 0, $radius, $radius, 100);
// rb(右上角)
    $rb_corner = imagerotate($lt_corner, 180, 0);
    imagecopymerge($im, $rb_corner, $image_w + $lift - $radius, $image_h + $top - $radius, 0, 0, $radius, $radius, 100);
// rt(右下角)
    $rt_corner = imagerotate($lt_corner, 270, 0);
    imagecopymerge($im, $rt_corner, $image_w - $radius + $lift, $top, 0, 0, $radius, $radius, 100);
}
//需要填写AppId和AppSecret
// function getAccessToken($appid,$appsecret) {
//     $AppId = $appid; //小程序APPid
//     $AppSecret = $appsecret; //小程序APPSecret
//     $data = json_decode(file_get_contents("access_token.json"));
//     if ($data->expire_time < time()) {
//         $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$AppId.'&secret='.$AppSecret;
//         $res = json_decode(httpGet($url));
//         $access_token = $res->access_token;
//         if ($access_token) {
//             $data->expire_time = time() + 7000;
//             $data->access_token = $access_token;
//             $fp = fopen("access_token.json", "w");
//             fwrite($fp, json_encode($data));
//             fclose($fp);
//         }
//     } else {
//        $access_token = $data->access_token;
//     }
//       return $access_token;
// }

function get_content_post($url,$post_data=array(),$header=array()){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_AUTOREFERER,true);
    $content = curl_exec($ch);
    $info = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($code == "200"){
        return $content;
    }else{
        return "错误码：".$code;
    }
}
//删除二维码海报，清理两分钟前创建的文件
// function del_file($path = '.') {
//     $current_dir = opendir($path);    //opendir()返回一个目录句柄,失败返回false
//     while(($file = readdir($current_dir)) !== false) {    //readdir()返回打开目录句柄中的一个条目
//         $sub_dir = $path . DIRECTORY_SEPARATOR . $file;    //构建子目录路径
//         if($file == '.' || $file == '..') {
//             continue;
//         } else if(is_dir($sub_dir)) {    //如果是目录,进行递归
//             del_file($sub_dir);
//         } else {    //如果是文件,判断是24小时以前的文件进行删除
//             $files = fopen($path.'/'.$file,"r");
//             $f =fstat($files);
//             fclose($files);
//             if($f['mtime']<(time()-60*5)){
//                 if(@unlink($path.'/'.$file)){
//                     echo "删除文件【".$path.'/'.$file."】成功！<br />";
//                 }else{
//                     echo "删除文件【".$path.'/'.$file."】失败！<br />";
//                 }
//             }
//         }
//     }
// }

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
