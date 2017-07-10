<?php
/*
Plugin Name: WP REST API For App
Plugin URI: http://www.watch-life.net.net
Description: 为微信小程序、app提供定制WordPres rest api
Version: 0.1
Author: jianbo
Author URI: http://www.watch-life.net
License: GPL
*/


function set_rest_allow_anonymous_comments() {
    return true;
}

function custom_fields_rest_prepare_post( $data, $post, $request, $post_id) { 
	$_data = $data->data;	 
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
    
    $content_first_image= get_post_content_first_image(get_the_content());
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
        $post_thumbnail_image_150='';
        $post_medium_image_300='';
        $post_thumbnail_image_624='';
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

    $comments_count = wp_count_comments( $post_id);
    $_data['total_comments']=$comments_count->total_comments;
    $category =get_the_category($post_id);
    $_data['category_name'] =$category[0]->cat_name;
    
    //$tin_post_views =  get_post_meta( $post->ID, 'tin_post_views' );
        //$_data[view] = $tin_post_views;
	$data->data = $_data;
 
	return $data;
 
}

function custom_fields_rest_prepare_category( $data, $item, $request ) {
	
    $term_meta=get_term_meta($item->term_id,'thumbnail');
    if($term_meta)
    {
     $category_thumbnail_image=$term_meta[0];
    }
    else
    {
     $category_thumbnail_image ='';
    }
	$data->data['category_thumbnail_image'] =$category_thumbnail_image;
    
	return $data;
}


function get_post_content_first_image($post_content){
	if(!$post_content){
		$the_post		= get_post();
		$post_content	= $the_post->post_content;
	} 

	preg_match_all( '/class=[\'"].*?wp-image-([\d]*)[\'"]/i', $post_content, $matches );
	if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	
		$image_id = $matches[1][0];
		if($image_url = wpjam_get_post_image_url($image_id, $size)){
			return $image_url;
		}
	}

	preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
	if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	   
		return $matches[1][0];
	}
}

add_filter('rest_allow_anonymous_comments','set_rest_allow_anonymous_comments');
add_filter( 'rest_prepare_category', 'custom_fields_rest_prepare_category', 10, 3 );
add_filter( 'rest_prepare_post', 'custom_fields_rest_prepare_post', 10, 3 );

?>