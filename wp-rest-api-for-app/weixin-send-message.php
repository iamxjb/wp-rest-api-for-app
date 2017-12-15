<?php

add_action( 'rest_api_init', function () {
  register_rest_route( 'watch-life-net/v1', 'weixin/sendmessage', array(
    'methods' => 'POST',
    'callback' => 'sendmessage'
  ) );
} );


function sendmessage($request) {
    $openid= $request['openid'];
    $template_id=$request['template_id'];
    $postid=$request['postid'];
    $form_id=$request['form_id'];
    $total_fee=$request['total_fee'];
    $flag=$request['flag'];
    $fromUser='';
    $parent=0;

    if (isset($request['fromUser'])) {
        $fromUser =$request['fromUser'];
    }

    if (isset($request['parent'])) {
        $parent =(int)$request['parent'];
    }

    if(empty($openid)  || empty($template_id) || empty($postid) || empty($form_id) || empty($total_fee) || empty($flag))
    {
        return new WP_Error( 'error', 'openid or template_id  or postid or form_id  or total_fee or flag is empty', array( 'status' => 500 ) );
    }
    else if(!function_exists('curl_init')) {
        return new WP_Error( 'error', 'php  curl is not enabled ', array( 'status' => 500 ) );
    }
    
    else
    {
        $data=sendmessage_json($openid ,$template_id ,$postid,$form_id,$total_fee,$flag,$fromUser,$parent); 
        if (empty($data)) {
            return new WP_Error( 'error', 'get openid error', array( 'status' => 404 ) );
          }  
        // Create the response object
         $response = new WP_REST_Response($data); 
        // Add a custom status code
         $response->set_status( 200 ); 
        // Add a custom header
        //$response->header( 'Location', 'https://www.watch-life.net' );
        return $response;
    
    }    
    
}
function sendmessage_json($openid ,$template_id ,$postid,$form_id,$total_fee,$flag,$fromUser,$parent) {


        //$wl_name = get_post_meta( $postid, 'wl_name',true);
        //$wl_phone = get_post_meta( $postid, 'wl_phone',true);

        $appid = get_option('wf_appid');
        $appsecret = get_option('wf_secret');

        $page='';
        if($flag =='1'  || $flag=='2' )
        {
            $total_fee= $total_fee.'元';
        }

        
        if($flag=='1' || $flag=='3' )
        {
            $page='pages/detail/detail?id='.$postid;

        }
        elseif($flag=='2')
        {
            $page='pages/about/about';
        }

        if(empty($appid) || empty($appsecret) )
        {
                $result["code"]="success";
                $result["message"]= "appid  or  appsecret is  empty";
                $result["status"]="500";                   
                return $result;
        }
        else
        {
        
            $access_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            $access_result = https_request_get($access_url);
            if($access_result !="ERROR")
            {
                $access_array = json_decode($access_result,true);
                if(empty($access_array['errcode']))
                {
                    $access_token = $access_array['access_token']; 
                    $expires_in = $access_array['expires_in'];
                    $data = array();

                    

                    $data1 = array(
                            "keyword1"=>array(
                            "value"=>$total_fee,                     
                             "color" =>"#173177"
                            ),
                            "keyword2"=>array(
                                "value"=>'谢谢你的赞赏,你的支持,是我前进的动力.',
                                "color"=> "#173177"
                            )
                        );  

                     date_default_timezone_set('PRC');
                     $datetime =date('Y-m-d H:i:s');
                     $data2 = array(
                            "keyword1"=>array(
                            "value"=>$fromUser,                     
                             "color" =>"#173177"
                            ),
                            "keyword2"=>array(
                                "value"=>$total_fee,
                                "color"=> "#173177"
                            ),
                            "keyword3"=>array(
                                "value"=>$datetime,
                                "color"=> "#173177"
                            )
                        );  


                    if($flag=='1' || $flag=='2' )
                    {
                        
                       $postdata['data']=$data1;

                    }
                    elseif ($flag=='3') {
                       
                        $postdata['data']=$data2;
                        
                    }

                    $postdata['touser']=$openid;
                    $postdata['template_id']=$template_id;
                    $postdata['page']=$page;
                    $postdata['form_id']=$form_id;
                    $postdata['template_id']=$template_id;
                    

                    $url ="https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$access_token;

                    $access_result = https_curl_post($url,$postdata,'json');

                    if($access_result !="ERROR"){
                        $access_array = json_decode($access_result,true);
                        if($access_array['errcode'] =='0')
                        {

                            
                            if($parent  !=0)
                            {
                                $delFlag=delete_comment_meta($parent,"formId",$form_id);
                                if($delFlag)
                                {
                                  $result["message"]= "sent message  success,del formId success";  
                                }
                                else
                                {
                                   $result["message"]= "sent message  success,del formId fail"; 
                                }
                                
                            }
                            else
                            {
                                $result["message"]= "sent message  success";
                            }                           
                            
                            $result["code"]="success";                            
                            $result["status"]="200";                   
                            return $result;

                        }
                        else

                        {
                            $result["code"]=$access_array['errcode'];
                            $result["message"]= $access_array['errmsg'];
                            $result["status"]="500";                   
                            return $result;
                        }

                        
                    }
                    else{
                        $result["code"]="success";
                        $result["message"]= "https POST request error";
                        $result["status"]="500";                   
                        return $result;
                    }
                }               
                else
                {
                
                    $result["code"]=$access_array['errcode'];
                    $result["message"]= $access_array['errmsg'];
                    $result["status"]="500";                   
                    return $result;
                
                }
                
            }
            else
            {
                    $result["code"]="success";
                    $result["message"]= "https request error";
                    $result["status"]="500";                   
                    return $result;
            }
            
            
        }
}

//发起https请求
function https_request_get($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl,  CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);  
        $data = curl_exec($curl);
        if (curl_errno($curl)){
            return 'ERROR';
        }
        curl_close($curl);
        return $data;
    }
    

    function https_curl_post($url,$data,$type){
        if($type=='json'){
            //$headers = array("Content-type: application/json;charset=UTF-8","Accept: application/json","Cache-Control: no-cache", "Pragma: no-cache");
            $data=json_encode($data);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
        $data = curl_exec($curl);
        if (curl_errno($curl)){
            return 'ERROR';
        }
        curl_close($curl);
        return $data;
    }
 

