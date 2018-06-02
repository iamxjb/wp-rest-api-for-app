<?php 
add_filter( 'rest_prepare_post', 'custom_fields_rest_prepare_post', 10, 3 ); //获取文章的缩略图，评论数目，分类名称

//在rest api 增加显示字段
function custom_fields_rest_prepare_post( $data, $post, $request) { 

    global $wpdb;

    $_data = $data->data;    
    //$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
    $post_id =$post->ID;

    $content =get_the_content();
     
     $siteurl = get_option('siteurl');
     $upload_dir = wp_upload_dir();
     $content = str_replace( 'http:'.strstr($siteurl, '//'), 'https:'.strstr($siteurl, '//'), $content);
     $content = str_replace( 'http:'.strstr($upload_dir['baseurl'], '//'), 'https:'.strstr($upload_dir['baseurl'], '//'), $content);
      
    //$_data['siteurl']=$content;       
    
    $images =getPostImages($content, $post_id); 
    $_data['post_thumbnail_image']=$images['post_thumbnail_image'];
    $_data['content_first_image']=$images['content_first_image'];
    $_data['post_medium_image_300']=$images['post_medium_image_300'];
    $_data['post_thumbnail_image_624']=$images['post_thumbnail_image_624'];
    $comments_count = wp_count_comments($post_id);
    
   
    
    $_data['total_comments']=$comments_count->total_comments;
    $category =get_the_category($post_id);
    $_data['category_name'] =$category[0]->cat_name; 

    //$date = str_replace(,"T"," ");

    $post_date =$post->post_date;

    $_data['date'] =time_tran($post_date);

    /*
    $content  =get_the_content();    
    $_content['rendered'] =$content;
    $_data['content']= $_content; 
    */

    
    $like_count = $wpdb->get_var("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='like' and post_id=".$post_id);
    $_data['like_count']= $like_count; 
    $post_views = (int)get_post_meta($post_id, 'wl_pageviews', true);     
    $params = $request->get_params();
     if ( isset( $params['id'] ) ) {

        $sql="SELECT meta_key , (SELECT display_name from ".$wpdb->users." WHERE user_login=substring(meta_key,2)) as avatarurl FROM ".$wpdb->postmeta." where meta_value='like' and post_id=".$post_id;
        $likes = $wpdb->get_results($sql);
        $avatarurls =array();
        foreach ($likes as $like) {
            $_avatarurl['avatarurl']  =$like->avatarurl;   
            $avatarurls[] = $_avatarurl;        
        }

      $post_views =$post_views+1;  
      if(!update_post_meta($post_id, 'wl_pageviews', $post_views))   
      {  
        add_post_meta($post_id, 'wl_pageviews', 1, true);  
      } 
      $_data['avatarurls']= $avatarurls;
        
    }
    else 
    {
        unset($_data['content'] );   
        unset($_data['author']); 
        unset($_data['excerpt']);
    }
    $pageviews =$post_views ;   
    $_data['pageviews'] = $pageviews;

    $category_id=$category[0]->term_id;
    $next_post = get_next_post($category_id, '', 'category');
    $previous_post = get_previous_post($category_id, '', 'category');
    $_data['next_post_id'] = !empty($next_post->ID)?$next_post->ID:null;
    $_data['next_post_title'] = !empty($next_post->post_title)?$next_post->post_title:null;
    $_data['previous_post_id'] = !empty($previous_post->ID)?$previous_post->ID:null;
    $_data['previous_post_title'] = !empty($previous_post->post_title)?$previous_post->post_title:null;
        
     
    
    //unset($_data['featured_media']);
    unset($_data['format']);
    unset($_data['ping_status']);
    unset($_data['template']);
    unset($_data['type']);
    //unset($_data['slug']);
    unset($_data['modified_gmt']);
    unset($_data['date_gmt']);
    unset($_data['meta']);
    unset($_data['guid']);
    unset($_data['curies']);
    unset($_data['modified']);
    unset($_data['status']);
    unset($_data['comment_status']);
    unset($_data['sticky']);
    unset($_data['author']); 
      
    $data->data = $_data; 
    
    return $data; 
}


add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'post/swipe', array(
    'methods' => 'GET',
    'callback' => 'getPostSwipe'
  ) );
} );


function getPostSwipe($request) {    
    
    $data=post_swipe_json(); 
    if (empty($data)) {
        return new WP_Error( 'error', 'post swipe is  error', array( 'status' => 404 ) );
      }
     $response = new WP_REST_Response($data);
     $response->set_status( 200 ); 
     return $response;

}

function post_swipe_json(){
        global $wpdb;
        $postSwipeIDs = get_option('wf_swipe');
        $posts =array();                  
        if(!empty($postSwipeIDs))
        {
            $sql="SELECT *  from ".$wpdb->posts." where id in(".$postSwipeIDs.")";
            $_posts = $wpdb->get_results($sql);
            
            foreach ($_posts as $post) {    
                $post_id = (int) $post->ID;
                $post_title = stripslashes($post->post_title);                
                $post_date =$post->post_date;
                $post_permalink = get_permalink($post->ID);            
                $_data["id"]  =$post_id;
                $_data["post_title"] =$post_title;
                $_data["post_date"] =$post_date; 
                $_data["post_permalink"] =$post_permalink;
                $_data['type']="detailpage";  
                
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

            // $_data["post_title"] ="";
            // $_data['post_thumbnail_image']="https://www.watch-life.net/images/weixinapp.jpg";
            // $_data['content_first_image']="https://www.watch-life.net/images/weixinapp.jpg";
            // $_data['post_medium_image_300']="https://www.watch-life.net/images/weixinapp.jpg";
            // $_data['post_thumbnail_image_624']="https://www.watch-life.net/images/weixinapp.jpg"; 
            // $_data['appid']="" ;        
            // $_data['type']="apppage"; 
            // $_data['url']="../applist/applist" ; 
            // $_data['id']="-1" ;       
            // $posts[] = $_data; 


            $result["code"]="success";
            $result["message"]= "get post  swipe success  ";
            $result["status"]="200";
            $result["posts"]=$posts;      
            return $result;
  

            
        
        }
        else
        {
            $result["code"]="success";
            $result["message"]= " get post swipe error";
            $result["status"]="500";                   
            return $result;
        }
     
}
