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
        
            $data=add_comment_json($post,$author_name,$author_email,$author_url,$content,$parent); 
            if (empty($data)) {
                return new WP_Error( 'error', 'add comment error', array( 'status' => 404 ) );
              }
             $response = new WP_REST_Response($data);
             $response->set_status( 201 ); 
             return $response;
            
        }
        
    
    }

}

function add_comment_json($post,$author_name,$author_email,$author_url,$content,$parent){
    
        
        $commentdata = array(
		'comment_post_ID' => $post, // to which post the comment will show up
		'comment_author' => $author_name, //fixed value - can be dynamic 
		'comment_author_email' => $author_email, //fixed value - can be dynamic 
		'comment_author_url' => $author_url, //fixed value - can be dynamic 
		'comment_content' => $content, //fixed value - can be dynamic 
		'comment_type' => '', //empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks
		'comment_parent' => $parent, //0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here
		//'user_id' => $current_user->ID, //passing current user ID or any predefined as per the demand
	);

        $comment_id = wp_insert_comment( wp_filter_comment($commentdata));

        if($comment_id)
        {
            $result["code"]="success";
            $result["message"]= "add comment success";
            $result["status"]="201";    
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

