<?php 
add_filter( 'rest_prepare_post', 'custom_fields_rest_prepare_post', 10, 3 ); //获取文章的缩略图，评论数目，分类名称

//在rest api 增加显示字段
function custom_fields_rest_prepare_post( $data, $post, $request) { 

    global $wpdb;

	$_data = $data->data;	 
	//$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
    $post_id =$post->ID;
    
    $images =getPostImages(get_the_content(), $post_id); 
    $_data['post_thumbnail_image']=$images['post_thumbnail_image'];
    $_data['content_first_image']=$images['content_first_image'];
    $_data['post_medium_image_300']=$images['post_medium_image_300'];
    $_data['post_thumbnail_image_624']=$images['post_thumbnail_image_624'];
    $comments_count = wp_count_comments($post_id);
    
    $pageviews = (int) get_post_meta( $post_id, 'wl_pageviews',true);
    $_data['pageviews'] = $pageviews;
    
    $_data['total_comments']=$comments_count->total_comments;
    $category =get_the_category($post_id);
    $_data['category_name'] =$category[0]->cat_name; 
    /*
    $content  =get_the_content();    
    $_content['rendered'] =$content;
    $_data['content']= $_content; 
    */
     
    $like_count = $wpdb->get_var("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='like' and post_id=".$post_id);
    $_data['like_count']= $like_count;
    $sql="SELECT meta_key , (SELECT display_name from ".$wpdb->users." WHERE user_login=meta_key) as avatarurl FROM ".$wpdb->postmeta." where meta_value='like' and post_id=".$post_id;
    $likes = $wpdb->get_results($sql);
    $avatarurls =array();
    foreach ($likes as $like) {
        $_avatarurl['avatarurl']  =$like->avatarurl;
        $_avatarurl['openid'] = $like->meta_key;      
        $avatarurls[] = $_avatarurl;        
    }
    
    $_data['avatarurls']= $avatarurls; 
        
        
        
    
    //$unset( $_data['content'] );
    
	$data->data = $_data; 
    
	return $data; 
}
