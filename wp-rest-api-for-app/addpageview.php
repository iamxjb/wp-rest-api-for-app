<?php
add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'post/addpageview/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'updatepageviews'
  ) );
} );


function updatepageviews($data) {
    $post_ID =$data['id'];
    if(!is_numeric($post_ID))
    {
        return new WP_Error( 'error', 'ID is not numeric', array( 'status' => 500 ) );
    }    
    else if(get_post($post_ID)==null)
    {
         return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
    }    
    else
    {
        $data=post_pageviews_json($post_ID); 
        if (empty($data)) {
            return new WP_Error( 'error', 'no find post', array( 'status' => 404 ) );
          }  
        // Create the response object
         $response = new WP_REST_Response($data); 
        // Add a custom status code
         $response->set_status( 201 ); 
        // Add a custom header
        //$response->header( 'Location', 'https://www.watch-life.net' );
        return $response;
    
    }
    
    
}
function post_pageviews_json($post_ID) {
          $posts = get_post($post_ID);         
          if (empty( $posts ) ) {
            return null;
            }
          else
          {
             
              $post_views = (int)get_post_meta($post_ID, 'wl_pageviews', true);  
              if(!update_post_meta($post_ID, 'wl_pageviews', ($post_views+1)))   
              {  
                add_post_meta($post_ID, 'wl_pageviews', 1, true);  
              } 
              $result =array();
              $result["code"]="success";
              $result["message"]= "update pageviews success  ";
              $result["status"]="201";
              return $result;
          }
}