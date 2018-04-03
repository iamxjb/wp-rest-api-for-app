<?php 
//获取是否启动小程序评论选项
add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', '/options/enableComment', array(
    'methods' => 'GET',
    'callback' => 'getEnableComment'    
  ) );
} );


function getEnableComment($data) {
$data=get_enableComment_json(); 
if ( empty( $data ) ) {
    return new WP_Error( 'no  options', 'no  options', array( 'status' => 404 ) );
  } 
// Create the response object
$response = new WP_REST_Response( $data ); 
// Add a custom status code
$response->set_status( 200 );
return $response;
}

function get_enableComment_json() {
    $wf_enable_comment_option  =get_option('wf_enable_comment_option');
    if(empty($wf_enable_comment_option ))
    {
        $result["code"]="success";
        $result["message"]= "get  enableComment success  ";
        $result["status"]="200";
        $result["enableComment"]="0";
        return $result;
    }
    else
    {
        $result["code"]="success";
        $result["message"]= "get  enableComment success  ";
        $result["status"]="200";
        $result["enableComment"]="1";
        return $result;
    }

}
