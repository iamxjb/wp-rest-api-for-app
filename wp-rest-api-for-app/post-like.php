<?php
//点赞
add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'post/like', array(
    'methods' => 'POST',
    'callback' => 'postLike'
  ) );
} );


function postLike($request) {
    $openid= $request['openid'];
    $postid=$request['postid'];

    if(empty($openid) || empty($postid) )
    {
        return new WP_Error( 'error', 'openid or postid is empty', array( 'status' => 500 ) );
    }
    else if(get_post($postid)==null)
    {
         return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
    }
    
    else
    { 
        if(!username_exists($openid))
        {
            return new WP_Error( 'error', 'Not allowed to submit', array( 'status' => 500 ) );
        }
        else if(is_wp_error(get_post($postid)))
        {
             return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
        }
        else
        {
        
            $data=post_like_json($openid,$postid); 
            if (empty($data)) {
                return new WP_Error( 'error', 'post like error', array( 'status' => 404 ) );
              }
             $response = new WP_REST_Response($data);
             $response->set_status( 200 ); 
             return $response;
            
        }
        
    
    }

}


function post_like_json($openid,$postid) { 
    $openid="_".$openid;
    $postmeta = get_post_meta($postid, $openid,true);
    if (empty($postmeta))
    {
        
        if(add_post_meta($postid, $openid,'like', true))
        {
            $result["code"]="success";
            $result["message"]= "post  like success  ";
            $result["status"]="200";    
            return $result;
        
        }
        else
        {
            $result["code"]="success";
            $result["message"]= "post like error";
            $result["status"]="500";                   
            return $result;
        }
        
        
        
    }
    else
    {
            $result["code"]="success";
            $result["message"]= "you have  posted like ";
            $result["status"]="501";                   
            return $result;
        
    }
    
}


add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'post/islike', array(
    'methods' => 'POST',
    'callback' => 'getIsLike'
  ) );
} );



function getIsLike($request) {
    $openid= $request['openid'];
    $postid=$request['postid'];

    if(empty($openid) || empty($postid) )
    {
        return new WP_Error( 'error', 'openid or postid is empty', array( 'status' => 500 ) );
    }
    else if(get_post($postid)==null)
    {
         return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
    }
    
    else
    { 
        if(!username_exists($openid))
        {
            return new WP_Error( 'error', 'Not allowed to submit', array( 'status' => 500 ) );
        }
        else if(is_wp_error(get_post($postid)))
        {
             return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
        }
        else
        {
        
            $data=post_islike_json($openid,$postid); 
            if (empty($data)) {
                return new WP_Error( 'error', 'post like error', array( 'status' => 404 ) );
              }
             $response = new WP_REST_Response($data);
             $response->set_status( 200 ); 
             return $response;
            
        }
        
    
    }

}


function post_islike_json($openid,$postid) {
    $openid="_".$openid; 
    $postmeta = get_post_meta($postid, $openid,true);
    if (!empty($postmeta))
    {
        
            $result["code"]="success";
            $result["message"]= "you have  posted like ";
            $result["status"]="200";                   
            return $result;
        
        
    }
    else
    {
            $result["code"]="success";
            $result["message"]= "you have not  posted like ";
            $result["status"]="501";                   
            return $result;
        
    }
    
}



add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'post/mylike', array(
    'methods' => 'GET',
    'callback' => 'getmyLike'
  ) );
} );



function getmyLike($request) {
    $openid= $request['openid'];   

    if(empty($openid))
    {
        return new WP_Error( 'error', 'openid is empty', array( 'status' => 500 ) );
    }
    
    else
    { 
        if(!username_exists($openid))
        {
            return new WP_Error( 'error', 'Not allowed to submit', array( 'status' => 500 ) );
        }        
        else
        {
        
            $data=post_mylike_json($openid); 
            if (empty($data)) {
                return new WP_Error( 'error', 'post like error', array( 'status' => 404 ) );
              }
             $response = new WP_REST_Response($data);
             $response->set_status( 200 ); 
             return $response;
            
        }
        
    
    }

}


function post_mylike_json($openid) {
    global $wpdb;
    $sql ="SELECT * from ".$wpdb->posts."  where ID in  
(SELECT post_id from ".$wpdb->postmeta." where meta_value='like' and meta_key='_".$openid."') ORDER BY post_date desc LIMIT 20";        
        $_posts = $wpdb->get_results($sql);
        $posts =array();
        foreach ($_posts as $post) {
            
            $_data["post_id"]  =$post->ID;
            $_data["post_title"]  =$post->post_title;
            $posts[]=$_data;
        }

        $result["code"]="success";
        $result["message"]= "get  comments success";
        $result["status"]="200";
        $result["data"]=$posts;                   
        return $result;         

}   