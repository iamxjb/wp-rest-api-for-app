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

function GetIP()
{
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key)
    {
        if (array_key_exists($key, $_SERVER) === true)
        {
            foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip)
            {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
                {
                    return $ip;
                }
            }
        }
    }
}