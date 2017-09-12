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
             $response->set_status( 201 ); 
             return $response;
            
        }
        
    
    }

}


function post_like_json($openid,$postid) { 
    if (empty(get_post_meta($postid, $openid,true)))
    {
        
        if(add_post_meta($postid, $openid,'like', true))
        {
            $result["code"]="success";
            $result["message"]= "post  like success  ";
            $result["status"]="201";    
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

