<?php 

//error_reporting(E_ALL);
//ini_set('display_errors', '1'); 

add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'comment/add', array(
    'methods' => 'POST',
    'callback' => 'addcomment'
  ) );
} );


function addcomment($request) {
    $post= (int)$request['post'];       
    $author_name=$request['author_name'];
    $author_email =$request['author_email'];
    $content =$request['content'];
    $author_url =$request['author_url'];    
    $openid =$request['openid'];
    $reqparent ='0';
    $userid=0;
    $formId='';

    if(isset($request['userid']))
    {
        $userid =(int)$request['userid']; 
    }

    if(isset($request['formId']))
    {
        $formId =$request['formId']; 
    }

    if(isset($request['parent']))
    {
        $reqparent =$request['parent']; 
    }
    $parent =0;
    if(is_numeric($reqparent))
    {
        $parent = (int)$reqparent;
        if($parent<0)
        {
            $parent=0;
        }
    }

    if($parent !=0)
    {
        $comment = get_comment($parent);
        if (empty( $comment ) ) {
            {
                return new WP_Error( 'error', 'parent id is error', array( 'status' => 500 ) );
            }
        }
    }

    if(empty($openid) || empty($post)  || empty($author_url)  || empty($author_email)  || empty($content) || empty($author_name))
    {
        return new WP_Error( 'error', ' openid   or post or author_name   or author_url  or author_email   or content  is  empty', array( 'status' => 500 ) );
    }
    else if(get_post($post)==null)
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
        
            $data=add_comment_json($post,$author_name,$author_email,$author_url,$content,$parent,$openid,$userid,$formId); 
            if (empty($data)) {
                return new WP_Error( 'error', 'add comment error', array( 'status' => 404 ) );
              }
             $response = new WP_REST_Response($data);
             $response->set_status( 200 ); 
             return $response;
            
        }
        
    
    }

}

function add_comment_json($post,$author_name,$author_email,$author_url,$content,$parent,$openid,$userid,$formId){
    
        global $wpdb;
        $user_id =0;
        $useropenid="";
        $sql ="SELECT ID FROM ".$wpdb->users ." WHERE user_login='".$openid."'";
        $users = $wpdb->get_results($sql);
        foreach ($users as $user) {
            $user_id = (int) $user->ID;
            
        }
        $commentdata = array(
        'comment_post_ID' => $post, // to which post the comment will show up
        'comment_author' => $author_name, //fixed value - can be dynamic 
        'comment_author_email' => $author_email, //fixed value - can be dynamic 
        'comment_author_url' => $author_url, //fixed value - can be dynamic 
        'comment_content' => $content, //fixed value - can be dynamic 
        'comment_type' => '', //empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks
        'comment_parent' => $parent, //0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here
        'user_id' => $user_id, //passing current user ID or any predefined as per the demand
    );

        $comment_id = wp_insert_comment( wp_filter_comment($commentdata));

        if($comment_id)
        {
            $useropenid="";
            if($userid !=0)
            {
                $sql ="SELECT user_login FROM ".$wpdb->users ." WHERE ID=".$userid;         
                $users = $wpdb->get_results($sql);
                foreach ($users as $user) {
                    $useropenid = $user->user_login;
                    
                }

            }

            $addcommentmetaflag=false;
            if($formId !='')
            {
                $addcommentmetaflag =add_comment_meta($comment_id, 'formId', $formId,false); 

            }

            $result["code"]="success";
            if($addcommentmetaflag)
            {
              $result["message"]= "add comment and formId success";  
            }
            else
             {
                $result["message"]= "add comment success,add formId fail";
             } 
            $result["status"]="200"; 
            $result["useropenid"]=$useropenid;  
            return $result;
        
        }
        else
        {
            $result["code"]="success";
            $result["message"]= "add  comment error";
            $result["status"]="500";                   
            return $result;
        }
     
}



add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'comment/get', array(
    'methods' => 'GET',
    'callback' => 'getcomment'
  ) );
} );


function getcomment($request) {
    $openid =$request['openid'];
    if(empty($openid))
    {
        return new WP_Error( 'error', ' openid   is  empty', array( 'status' => 500 ) );
    }
    else{

        if(!username_exists($openid))
        {
            return new WP_Error( 'error', 'Not allowed to submit', array('status' => 500 ));
        }
        else
        {
            $data=get_comment_json($openid); 
            if (empty($data)) {
                return new WP_Error( 'error', 'add comment error', array( 'status' => 404 ) );
              }
             $response = new WP_REST_Response($data);
             $response->set_status( 200 ); 
             return $response;
        }

    }

    

}

function get_comment_json($openid){

    global $wpdb;
    $user_id =0;
    $sql ="SELECT ID FROM ".$wpdb->users ." WHERE user_login='".$openid."'";
    $users = $wpdb->get_results($sql);
    foreach ($users as $user) {
        $user_id = (int) $user->ID;
    }

    if($user_id==0)
    {
        $result["code"]="success";
        $result["message"]= "user_id is  empty";
        $result["status"]="500";                   
        return $result;
    }
    else
    {

        $sql ="SELECT * from ".$wpdb->posts."  where ID in  
(SELECT comment_post_ID from ".$wpdb->comments." where user_id=".$user_id."   GROUP BY comment_post_ID order by comment_date ) LIMIT 20";        
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

}