<?php
//订阅
add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'category/postsubscription', array(
    'methods' => 'POST',
    'callback' => 'postSubscription'
  ) );
} );


function postSubscription($request) {
    global $wpdb;
    $openid= $request['openid'];
    $categoryid=$request['categoryid'];
    if(empty($openid) || empty($categoryid) )
    {
        return new WP_Error( 'error', 'openid or categoryid is empty', array( 'status' => 500 ) );
    }    
    else
    { 
        if(!username_exists($openid))
        {
            return new WP_Error( 'error', 'Not allowed to submit', array( 'status' => 500 ) );
        }       
        else
        {
            $user_id =0;
            $sql ="SELECT ID FROM ".$wpdb->users ." WHERE user_login='".$openid."'";
            $users = $wpdb->get_results($sql);
            foreach ($users as $user) {
                $user_id = (int) $user->ID;
            }

            if($user_id !=0)                
            {
                $data=post_subscription_json($user_id,$categoryid); 
                if (empty($data)) {
                    return new WP_Error( 'error', 'post subscription error', array( 'status' => 404 ) );
                  }
                 $response = new WP_REST_Response($data);
                 $response->set_status( 200 ); 
                 return $response;
            }
            else
            {
                return new WP_Error( 'error', 'userid id is error ', array( 'status' => 500 ) );

            }  
        }
    }
}


function post_subscription_json($user_id,$categoryid) {     
    //$usermeta = get_user_meta($user_id, "wl_sub",true);
    global $wpdb;
    $sql ="SELECT *  FROM ".$wpdb->usermeta ." WHERE user_id='".$user_id."' and meta_key='wl_sub' and meta_value='".$categoryid."'";
     //$count=0;
     $usermetas = $wpdb->get_results($sql);
     $count =count($usermetas);
    // foreach ($usermeta as $usermetas) {
    //     $count = (int) $usermeta->n;
    // }
    if ($count==0)
    {
        
        if(add_user_meta($user_id, "wl_sub",$categoryid,false))
        {
            $result["code"]="success";
            $result["message"]= "post  subscription success  ";
            $result["status"]="200";    
            return $result;
        
        }
        else
        {
            $result["code"]="success";
            $result["message"]= "post subscription error";
            $result["status"]="500";                   
            return $result;
        }

        
        
        
    }
    else
    {
            if (delete_user_meta($user_id,'wl_sub',$categoryid))
            {
                
                    $result["code"]="success";
                    $result["message"]= "you have  delete success subscription  ";
                    $result["status"]="201";                           
                    return $result;
                
                
            }
            else
            {
                    $result["code"]="success";
                    $result["message"]= "delete subscription fail ";
                    $result["status"]="501";                   
                    return $result;
                
            }

    }

   

           
}


add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'category/getsubscription', array(
    'methods' => 'GET',
    'callback' => 'getSubscription'
  ) );
} );


function getSubscription($request) {
    global $wpdb;
    $openid= $request['openid'];
    if(empty($openid) )
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
            $user_id =0;
            $sql ="SELECT ID FROM ".$wpdb->users ." WHERE user_login='".$openid."'";
            $users = $wpdb->get_results($sql);
            foreach ($users as $user) {
                $user_id = (int) $user->ID;
            }

            if($user_id !=0)
            {
                $data=get_subscription_json($user_id); 
                if (empty($data)) {
                    return new WP_Error( 'error', 'post subscription error', array( 'status' => 404 ) );
                  }
                 $response = new WP_REST_Response($data);
                 $response->set_status( 200 ); 
                 return $response;
            }
            else
            {
                return new WP_Error( 'error', 'userid id is error ', array( 'status' => 500 ) );
            }
        
            
            
        }
        
    
    }

}


function get_subscription_json($user_id) {
    global $wpdb;
    //$sql ="SELECT t.meta_value as catid ,(SELECT t2.name  from ".$wpdb->term_taxonomy ." t1, ".$wpdb->terms." t2 where t1.taxonomy='category' and t1.term_id=t2.term_id and t1.term_id=t.meta_value) as catname  FROM ".$wpdb->usermeta ." t WHERE user_id='".$user_id."' and meta_key='wl_sub'";
    $usermeta = get_user_meta($user_id);
    if (!empty($usermeta))
    {
            //$usermetaList =$wpdb->get_results($sql);        
            $result["code"]="success";
            $result["message"]= "get subscription  success ";
            $result["status"]="200";
            
            if(!empty($usermeta['wl_sub']))
            {
            	$result["subscription"]=$usermeta['wl_sub'];
            	$substr=implode(",",$usermeta['wl_sub']);
            	$result["substr"]=$substr; 
            	$sql="SELECT SQL_CALC_FOUND_ROWS  ".$wpdb->posts.".ID ,".$wpdb->posts.".post_title  FROM ".$wpdb->posts."  LEFT JOIN ".$wpdb->term_relationships." ON (".$wpdb->posts.".ID = ".$wpdb->term_relationships.".object_id) WHERE 1=1  AND ( ".$wpdb->term_relationships.".term_taxonomy_id IN (".$substr.")) AND ".$wpdb->posts.".post_type = 'post' AND (".$wpdb->posts.".post_status = 'publish') GROUP BY ".$wpdb->posts.".ID ORDER BY ".$wpdb->posts.".post_date DESC LIMIT 0, 20";
	            $usermetaList =$wpdb->get_results($sql); 
	            $result["usermetaList"]=$usermetaList;

            }
            
            //$sql="SELECT SQL_CALC_FOUND_ROWS  wp_posts.ID ,wp_posts.post_title  FROM wp_posts  LEFT JOIN wp_term_relationships ON (wp_posts.ID = wp_term_relationships.object_id) WHERE 1=1  AND ( wp_term_relationships.term_taxonomy_id IN (".$substr.")) AND wp_posts.post_type = 'post' AND (wp_posts.post_status = 'publish') GROUP BY wp_posts.ID ORDER BY wp_posts.post_date DESC LIMIT 0, 20";
            
            //$result["sql"]=$sql;                   
            return $result;        
        
    }
    else
    {
            $result["code"]="success";
            $result["message"]= "you have not  posted subscription ";
            $result["status"]="501";                   
            return $result;
        
    }
    
}


