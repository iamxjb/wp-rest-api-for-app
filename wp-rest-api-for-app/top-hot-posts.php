<?php

//获取本站本年度最受欢迎的top10文章
add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', '/post/hotpostthisyear', array(
    'methods' => 'GET',
    'callback' => 'getTopHotPostsThisYear'    
  ) );
} );
function getTopHotPostsThisYear( $data ) {
$data=get_mostcommented_thisyear_json(10); 
if ( empty( $data ) ) {
    return new WP_Error( 'noposts', 'noposts', array( 'status' => 404 ) );
  } 
// Create the response object
$response = new WP_REST_Response( $data ); 
// Add a custom status code
$response->set_status( 201 ); 
// Add a custom header
//$response->header( 'Location', 'https://www.watch-life.net' );
return $response;
}



// Get Top Commented Posts  this year 获取本年度评论最多的文章
function get_mostcommented_thisyear_json($limit = 10) {
    global $wpdb, $post, $tableposts, $tablecomments, $time_difference, $post;
    $today = date("Y-m-d H:i:s"); //获取今天日期时间   
    $fristday = date( "Y-m-d H:i:s",  strtotime(date("Y",time())."-1"."-1"));  //本年第一天
    $sql="SELECT  ".$wpdb->posts.".ID as ID, post_title, post_name,post_content,post_date, COUNT(".$wpdb->comments.".comment_post_ID) AS 'comment_total' FROM ".$wpdb->posts." LEFT JOIN ".$wpdb->comments." ON ".$wpdb->posts.".ID = ".$wpdb->comments.".comment_post_ID WHERE comment_approved = '1' AND post_date BETWEEN '".$fristday."' AND '".$today."' AND post_status = 'publish' AND post_password = '' GROUP BY ".$wpdb->comments.".comment_post_ID ORDER  BY comment_total DESC LIMIT ". $limit;
    $mostcommenteds = $wpdb->get_results($sql);
    $posts =array();
    foreach ($mostcommenteds as $post) {
    
			$post_id = (int) $post->ID;
			$post_title = stripslashes($post->post_title);
			$comment_total = (int) $post->comment_total;
            $post_date =$post->post_date;
            $post_permalink = get_permalink($post->ID);            
            $_data["post_id"]  =$post_id;
            $_data["post_title"] =$post_title; 
            $_data["comment_total"] =$comment_total;  
            $_data["post_date"] =$post_date; 
            $_data["post_permalink"] =$post_permalink;
            
            $pageviews = (int) get_post_meta( $post_id, 'wl_pageviews',true);
            $_data['pageviews'] = $pageviews;

            $images =getPostImages($post->post_content,$post_id);         
            
            $_data['post_thumbnail_image']=$images['post_thumbnail_image'];
            $_data['content_first_image']=$images['content_first_image'];
            $_data['post_medium_image_300']=$images['post_medium_image_300'];
            $_data['post_thumbnail_image_624']=$images['post_thumbnail_image_624'];
            $posts[] = $_data;
            
            
    } 
 return $posts;     
    
}

add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', '/post/hotpost', array(
    'methods' => 'GET',
    'callback' => 'getTopHotPosts'
  ) );
} );


//获取本站最受欢迎的top10文章
function getTopHotPosts($data ) {
$data=get_mostcommented_json(10); 
if ( empty( $data ) ) {
    return new WP_Error( 'noposts', 'noposts', array( 'status' => 404 ) );
  }  
// Create the response object
$response = new WP_REST_Response($data); 
// Add a custom status code
$response->set_status( 201 ); 
// Add a custom header
//$response->header( 'Location', 'https://www.watch-life.net' );
return $response;
}


function get_mostcommented_json($limit = 10) {
    global $wpdb, $post, $tableposts, $tablecomments, $time_difference, $post;
    $sql="SELECT  ".$wpdb->posts.".ID as ID, post_title, post_name, post_content,post_date, COUNT(".$wpdb->comments.".comment_post_ID) AS 'comment_total' FROM ".$wpdb->posts." LEFT JOIN ".$wpdb->comments." ON ".$wpdb->posts.".ID = ".$wpdb->comments.".comment_post_ID WHERE comment_approved = '1' AND post_date < '".date("Y-m-d H:i:s", (time() + ($time_difference * 3600)))."' AND post_status = 'publish' AND post_password = '' GROUP BY ".$wpdb->comments.".comment_post_ID ORDER  BY comment_total DESC LIMIT ". $limit;
    $mostcommenteds = $wpdb->get_results($sql);
    $posts =array();  
    foreach ($mostcommenteds as $post) {
			$post_id = (int) $post->ID;
			$post_title = stripslashes($post->post_title);
            $comment_total = (int) $post->comment_total;
			$post_date =$post->post_date;
            $post_permalink = get_permalink($post->ID);            
            $_data["post_id"]  =$post_id;
            $_data["post_title"] =$post_title; 
            $_data["comment_total"] =$comment_total;  
            $_data["post_date"] =$post_date;
            $_data["post_permalink"] =$post_permalink;
            $pageviews = (int) get_post_meta( $post_id, 'wl_pageviews',true);
            $_data['pageviews'] = $pageviews;
            
            $images =getPostImages($post->post_content,$post_id);         
            
            $_data['post_thumbnail_image']=$images['post_thumbnail_image'];
            $_data['content_first_image']=$images['content_first_image'];
            $_data['post_medium_image_300']=$images['post_medium_image_300'];
            $_data['post_thumbnail_image_624']=$images['post_thumbnail_image_624'];          
                        
            $posts[] = $_data;    
            
    }

return $posts;    
    
}