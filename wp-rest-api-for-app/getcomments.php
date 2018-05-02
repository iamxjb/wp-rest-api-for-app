<?php 

//error_reporting(E_ALL);
//ini_set('display_errors', '1'); 

add_action( 'rest_api_init', function () {
	register_rest_route( 'watch-life-net/v1', 'comment/getcomments', array(
		'methods' => 'get',
		'callback' => 'getcomments'
	) );
} );


function getcomments($request) {
	$postid =$request['postid'];
	$limit= $request['limit'];
	$page= $request['page'];
	$order =$request['order'];
	if(empty($order ))
	{
		$order ="asc";
	}

	if(empty($postid) || empty($limit) || empty($page))
	{
		return new WP_Error( 'error', ' postid or limit  or  page  is  empty', array( 'status' => 500 ) );
	}
	else
	{
		$data=get_comments_json($postid,$limit,$page,$order); 
		if (empty($data)) {
			return new WP_Error( 'error', 'add comment error', array( 'status' => 404 ) );
		}
		$response = new WP_REST_Response($data);
		$response->set_status( 200 ); 
		return $response;
	}

}

function get_comments_json($postid,$limit,$page,$order)
{
	global $wpdb;
	$page=($page-1)*$limit;


	$sql="SELECT t.*,(SELECT t2.meta_value  from ".$wpdb->commentmeta."  t2 where  t.comment_ID = t2.comment_id  AND t2.meta_key = 'formId')  AS formId FROM ".$wpdb->comments." t WHERE t.comment_post_ID =".$postid." and t.comment_parent=0 and t.comment_approved='1' order by t.comment_date ".$order." limit ".$page.",".$limit;
	//$sql  ="SELECT t2.comment_author as parent_name,t2.comment_date  as parent_date ,t1.user_id as user_id,(SELECT t3.meta_value  from ".$wpdb->commentmeta."  t3 where  t1.comment_ID = t3.comment_id  AND t3.meta_key = 'formId')  AS formId  from  ".$wpdb->comments." t1 LEFT JOIN ".$wpdb->comments." t2 on t1.comment_parent=t2.comment_ID  WHERE t1.comment_ID=".$comment_id;
	
	$comments = $wpdb->get_results($sql); 
	$commentslist  =array();
	foreach($comments as $comment){
		if($comment->comment_parent==0){
			$data["id"]=$comment->comment_ID;
			$data["author_name"]=$comment->comment_author;
			$author_url =$comment->comment_author_url;
			$data["author_url"]=strpos($author_url, "wx.qlogo.cn")?$author_url:"../../images/gravatar.png";
			$data["date"]=time_tran($comment->comment_date);
			$data["content"]=$comment->comment_content;
			$data["formId"]=$comment->formId;
			$data["userid"]=$comment->user_id;
			$order="asc";
			$data["child"]=getchaildcomment($postid,$comment->comment_ID,5,$order);
			$commentslist[] =$data;
		}
	}
	$result["code"]="success";
    $result["message"]= "get  comments success";
    $result["status"]="200";
    $result["data"]=$commentslist;
    //$result["sql"]= $sql;                 
    return $result;         

}

function getchaildcomment($postid,$comment_id,$limit,$order){
	global $wpdb;
	if($limit>0){
		$commentslist  =array();
		$sql="SELECT t.*,(SELECT t2.meta_value  from ".$wpdb->commentmeta."  t2 where  t.comment_ID = t2.comment_id  AND t2.meta_key = 'formId')  AS formId FROM ".$wpdb->comments." t WHERE t.comment_post_ID =".$postid. " and t.comment_parent=".$comment_id." and t.comment_approved='1' order by comment_date ".$order;
		$comments = $wpdb->get_results($sql); 
		foreach($comments as $comment){						
				$data["id"]=$comment->comment_ID;
				$data["author_name"]=$comment->comment_author;
				$author_url =$comment->comment_author_url;
				$data["author_url"]=strpos($author_url, "wx.qlogo.cn")?$author_url:"../../images/gravatar.png";
				$data["date"]=time_tran($comment->comment_date);
				$data["content"]=$comment->comment_content;
				$data["formId"]=$comment->formId;
				$data["userid"]=$comment->user_id;
				$data["child"]=getchaildcomment($postid,$comment->comment_ID,$limit-1,$order);
				//$data["sql"]=$sql;
				$commentslist[] =$data;			
		}
	}
	return $commentslist;
}