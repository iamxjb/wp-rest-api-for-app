<?php

//获取本站一年内评论最多的top10文章
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
$response->set_status( 200 );
return $response;
}



// Get Top Commented Posts  this year 获取本年度评论最多的文章
function get_mostcommented_thisyear_json($limit = 10) {
    global $wpdb, $post, $tableposts, $tablecomments, $time_difference, $post;
    date_default_timezone_set('Asia/Shanghai');
    $today = date("Y-m-d H:i:s"); //获取今天日期时间   
   // $fristday = date( "Y-m-d H:i:s",  strtotime(date("Y",time())."-1"."-1"));  //本年第一天;
    $fristday= date("Y-m-d H:i:s", strtotime("-1 year"));  
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

            $like_count = $wpdb->get_var("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='like' and post_id=".$post_id);
            $_data['like_count']= $like_count;

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
$response->set_status( 200 ); 
// Add a custom header
//$response->header( 'Location', 'https://www.watch-life.net' );
return $response;
}


function get_mostcommented_json($limit = 10) {
    global $wpdb, $post, $tableposts, $tablecomments, $time_difference, $post;
    date_default_timezone_set('Asia/Shanghai');
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
            
            $like_count = $wpdb->get_var("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='like' and post_id=".$post_id);
            $_data['like_count']= $like_count;


            $images =getPostImages($post->post_content,$post_id);         
            
            $_data['post_thumbnail_image']=$images['post_thumbnail_image'];
            $_data['content_first_image']=$images['content_first_image'];
            $_data['post_medium_image_300']=$images['post_medium_image_300'];
            $_data['post_thumbnail_image_624']=$images['post_thumbnail_image_624'];          
                        
            $posts[] = $_data;    
            
    }

return $posts;    
    
}



//获取本站一年内点赞最多的top10文章
add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', '/post/likethisyear', array(
    'methods' => 'GET',
    'callback' => 'getTopLikePostsThisYear'    
  ) );
} );

function getTopLikePostsThisYear( $data ) {
$data=get_mostlike_thisyear_json(10); 
if ( empty( $data ) ) {
    return new WP_Error( 'noposts', 'noposts', array( 'status' => 404 ) );
  } 
// Create the response object
$response = new WP_REST_Response( $data ); 
// Add a custom status code
$response->set_status( 200 );
return $response;
}



// Get Top Commented Posts  this year 获取本年度评论最多的文章
function get_mostlike_thisyear_json($limit = 10) {
    global $wpdb, $post, $tableposts, $tablecomments, $time_difference, $post;
    date_default_timezone_set('Asia/Shanghai');
    $today = date("Y-m-d H:i:s"); //获取今天日期时间   
   // $fristday = date( "Y-m-d H:i:s",  strtotime(date("Y",time())."-1"."-1"));  //本年第一天;
    $fristday= date("Y-m-d H:i:s", strtotime("-1 year"));  
    $sql="SELECT  ".$wpdb->posts.".ID as ID, post_title, post_name,post_content,post_date, COUNT(".$wpdb->postmeta.".post_id) AS 'like_total' FROM ".$wpdb->posts." LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE ".$wpdb->postmeta.".meta_value ='like' AND post_date BETWEEN '".$fristday."' AND '".$today."' AND post_status = 'publish' AND post_password = '' GROUP BY ".$wpdb->postmeta.".post_id ORDER  BY like_total DESC LIMIT ". $limit;
    $mostlikes = $wpdb->get_results($sql);
    $posts =array();
    foreach ($mostlikes as $post) {
    
            $post_id = (int) $post->ID;
            $post_title = stripslashes($post->post_title);
            $like_total = (int) $post->like_total;
            $post_date =$post->post_date;
            $post_permalink = get_permalink($post->ID);            
            $_data["post_id"]  =$post_id;
            $_data["post_title"] =$post_title; 
            $_data["like_count"] =$like_total;  
            $_data["post_date"] =$post_date; 
            $_data["post_permalink"] =$post_permalink;
            
            $pageviews = (int) get_post_meta( $post_id, 'wl_pageviews',true);
            $_data['pageviews'] = $pageviews;

            $comment_total = $wpdb->get_var("SELECT COUNT(1) FROM ".$wpdb->comments." where  comment_approved = '1' and comment_post_ID=".$post_id);
            $_data['comment_total']= $comment_total;

            $images =getPostImages($post->post_content,$post_id);         
            
            $_data['post_thumbnail_image']=$images['post_thumbnail_image'];
            $_data['content_first_image']=$images['content_first_image'];
            $_data['post_medium_image_300']=$images['post_medium_image_300'];
            $_data['post_thumbnail_image_624']=$images['post_thumbnail_image_624'];
            $posts[] = $_data;
            
            
    } 
 return $posts;     
    
}


//获取本站一年内浏览最多的top10文章
add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', '/post/pageviewsthisyear', array(
    'methods' => 'GET',
    'callback' => 'getTopPageviewsPostsThisYear'    
  ) );
} );

function getTopPageviewsPostsThisYear( $data ) {
$data=get_pageviews_thisyear_json(10); 
if ( empty( $data ) ) {
    return new WP_Error( 'noposts', 'noposts', array( 'status' => 404 ) );
  } 
// Create the response object
$response = new WP_REST_Response( $data ); 
// Add a custom status code
$response->set_status( 200 );
return $response;
}




function get_pageviews_thisyear_json($limit = 10) {
    global $wpdb, $post, $tableposts, $tablecomments, $time_difference, $post;
    date_default_timezone_set('Asia/Shanghai');
    $today = date("Y-m-d H:i:s"); //获取今天日期时间   
   // $fristday = date( "Y-m-d H:i:s",  strtotime(date("Y",time())."-1"."-1"));  //本年第一天;
    $fristday= date("Y-m-d H:i:s", strtotime("-1 year"));  
    $sql="SELECT  ".$wpdb->posts.".ID as ID, post_title, post_name,post_content,post_date, CONVERT(".$wpdb->postmeta.".meta_value,SIGNED) AS 'pageviews_total' FROM ".$wpdb->posts." LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE ".$wpdb->postmeta.".meta_key ='wl_pageviews' AND post_date BETWEEN '".$fristday."' AND '".$today."' AND post_status = 'publish' AND post_password = '' ORDER  BY pageviews_total DESC LIMIT ". $limit;
    $mostlikes = $wpdb->get_results($sql);
    $posts =array();
    foreach ($mostlikes as $post) {
    
            $post_id = (int) $post->ID;
            $post_title = stripslashes($post->post_title);
            $pageviews = (int) $post->pageviews_total;
            $post_date =$post->post_date;
            $post_permalink = get_permalink($post->ID);            
            $_data["post_id"]  =$post_id;
            $_data["post_title"] =$post_title; 
            $_data["pageviews"] =$pageviews;  
            $_data["post_date"] =$post_date; 
            $_data["post_permalink"] =$post_permalink;

            
            
            $like_count = $wpdb->get_var("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='like' and post_id=".$post_id);
            $_data['like_count'] = $like_count;

            $comment_total = $wpdb->get_var("SELECT COUNT(1) FROM ".$wpdb->comments." where  comment_approved = '1' and comment_post_ID=".$post_id);
            $_data['comment_total']= $comment_total;

            $images =getPostImages($post->post_content,$post_id);         
            
            $_data['post_thumbnail_image']=$images['post_thumbnail_image'];
            $_data['content_first_image']=$images['content_first_image'];
            $_data['post_medium_image_300']=$images['post_medium_image_300'];
            $_data['post_thumbnail_image_624']=$images['post_thumbnail_image_624'];
            $posts[] = $_data;
            
            
    } 
 return $posts;     
    
}


//获取本站一年内赞赏最多的top10文章
add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', '/post/praisethisyear', array(
    'methods' => 'GET',
    'callback' => 'getTopPraisePostsThisYear'    
  ) );
} );

function getTopPraisePostsThisYear( $data ) {
$data=get_praise_thisyear_json(10); 
if ( empty( $data ) ) {
    return new WP_Error( 'noposts', 'noposts', array( 'status' => 404 ) );
  } 
// Create the response object
$response = new WP_REST_Response( $data ); 
// Add a custom status code
$response->set_status( 200 );
return $response;
}




function get_praise_thisyear_json($limit = 10) {
    global $wpdb, $post, $tableposts, $tablecomments, $time_difference, $post;
    date_default_timezone_set('Asia/Shanghai');
    $today = date("Y-m-d H:i:s"); //获取今天日期时间   
   // $fristday = date( "Y-m-d H:i:s",  strtotime(date("Y",time())."-1"."-1"));  //本年第一天;
    $fristday= date("Y-m-d H:i:s", strtotime("-1 year"));  
    $sql="SELECT  ".$wpdb->posts.".ID as ID, post_title, post_name,post_content,post_date, count(".$wpdb->postmeta.".post_id) AS 'praise_total' FROM ".$wpdb->posts." LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE ".$wpdb->postmeta.".meta_value like '%praise' AND post_date BETWEEN '".$fristday."' AND '".$today."' AND post_status = 'publish' and  post_type='post' AND post_password = '' GROUP BY ".$wpdb->postmeta.".post_id ORDER  BY praise_total DESC LIMIT ". $limit;
    $mostlikes = $wpdb->get_results($sql);
    $posts =array();
    foreach ($mostlikes as $post) {
    
            $post_id = (int) $post->ID;
            $post_title = stripslashes($post->post_title);
            $pageviews=0;
            if(!empty($post->pageviews_total))
            {
                $pageviews = (int) $post->pageviews_total;
            }
            
            $post_date =$post->post_date;
            $post_permalink = get_permalink($post->ID);            
            $_data["post_id"]  =$post_id;
            $_data["post_title"] =$post_title; 
            //$_data["pageviews"] =$pageviews;  
            $_data["post_date"] =$post_date; 
            $_data["post_permalink"] =$post_permalink;

            $pageviews = (int) get_post_meta( $post_id, 'wl_pageviews',true);
            $_data['pageviews'] = $pageviews;
            
            $like_count = $wpdb->get_var("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='like' and post_id=".$post_id);
            $_data['like_count'] = $like_count;

            $comment_total = $wpdb->get_var("SELECT COUNT(1) FROM ".$wpdb->comments." where  comment_approved = '1' and comment_post_ID=".$post_id);
            $_data['comment_total']= $comment_total;

            $images =getPostImages($post->post_content,$post_id);         
            
            $_data['post_thumbnail_image']=$images['post_thumbnail_image'];
            $_data['content_first_image']=$images['content_first_image'];
            $_data['post_medium_image_300']=$images['post_medium_image_300'];
            $_data['post_thumbnail_image_624']=$images['post_thumbnail_image_624'];
            $posts[] = $_data;
            
            
    } 
 return $posts;     
    
}
