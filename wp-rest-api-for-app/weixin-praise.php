<?php 

//error_reporting(E_ALL);
//ini_set('display_errors', '1'); 

add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'post/praise', array(
    'methods' => 'POST',
    'callback' => 'postPraise'
  ) );
} );


function postPraise($request) {
    $openid= $request['openid'];       
    $orderid=$request['orderid'];
    $postid =$request['postid'];
    $money =$request['money'];
    if(empty($openid) || empty($orderid) || empty($money) || empty($postid) )
    {
        return new WP_Error( 'error', 'openid or postid or money or orderid is empty', array( 'status' => 500 ) );
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
        
            $data=post_praise_json($openid,$postid,$orderid,$money); 
            if (empty($data)) {
                return new WP_Error( 'error', 'post like error', array( 'status' => 404 ) );
              }
             $response = new WP_REST_Response($data);
             $response->set_status( 201 ); 
             return $response;
            
        }
        
    
    }

}

function post_praise_json($openid,$postid,$orderid,$money){
    
        $openid="_".$openid;
        $orderid=$orderid;
        $meta_key=$openid."@"."$orderid";
        $meta_value=$money."_praise";
        if(update_post_meta($postid, $meta_key,$meta_value,true))
        {
            $result["code"]="success";
            $result["message"]= "post  praise success  ";
            $result["status"]="201";    
            return $result;
        
        }
        else
        {
            $result["code"]="success";
            $result["message"]= "post praise error";
            $result["status"]="500";                   
            return $result;
        }
     
}

