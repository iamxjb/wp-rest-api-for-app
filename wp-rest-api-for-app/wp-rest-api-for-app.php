<?php
/*
Plugin Name: WP REST API For App
Plugin URI: http://www.watch-life.net
Description: 为微信小程序、app提供定制WordPress rest api
Version: 0.5
Author: jianbo
Author URI: http://www.watch-life.net
License: GPL v3
*/


function set_rest_allow_anonymous_comments() {
    return true;
}

function custom_fields_rest_prepare_post( $data, $post, $request) { 
	$_data = $data->data;	 
	//$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
    $post_id =$post->ID;
    
    $content_first_image= get_post_content_first_image(get_the_content());
	$thumbnail_id = get_post_thumbnail_id($post_id);
	if($thumbnail_id ){
		$thumb = wp_get_attachment_image_src($thumbnail_id, 'thumbnail');
                $post_thumbnail_image = $thumb[0];
	}
    else if($content_first_image)
    {          
        $attachments = get_attached_media( 'image', $post_id ); //查找文章的附件
        $index = array_keys($attachments);
        $flag=0; 
        $post_thumbnail_image_150='';
        $post_medium_image_300='';
        $post_thumbnail_image_624='';
        for ($i = 0; $i < sizeof($index); $i++) {
            $arr =$attachments[$index[$i]];
            $imageName = $arr->{"post_title"};            
            if(strpos($content_first_image,$imageName)!==false){  //附件的名称如果和第一张图片相同,就取这个附件的缩略图
                {
                    $post_thumbnail_image_150 = wp_get_attachment_image_url($arr->{"ID"},'thumbnail');
                    $post_medium_image_300=wp_get_attachment_image_url($arr->{"ID"},'medium');
                    $post_thumbnail_image_624=wp_get_attachment_image_url($arr->{"ID"},'post-thumbnail');
                    $id =$arr->{"ID"};                    
                    $flag++;
                    break;
                }
            }
        }
        if($flag>0)
            {
                $post_thumbnail_image = $post_thumbnail_image_150;
            }
            else
            {
                $post_thumbnail_image = $content_first_image; 
            }            
                        
	}
    else
    {
        $post_thumbnail_image='';
    }
    
    
    
    if(strlen($post_medium_image_300)>0)
    {
        $_data['post_medium_image_300']=$post_medium_image_300; 
    }
    else
    {
         $_data['post_medium_image_300']=$content_first_image;
    }
    
    
    if(strlen($post_thumbnail_image_624)>0)
    {
        $_data['post_thumbnail_image_624']=$post_thumbnail_image_624; 
    }
    else
    {
         $_data['post_thumbnail_image_624']=$content_first_image;
    }
    
    
    $_data['post_thumbnail_image']=$post_thumbnail_image; 
    
    $_data['content_first_image']=$content_first_image;    

    $comments_count = wp_count_comments( $post_id);
    $_data['total_comments']=$comments_count->total_comments;
    $category =get_the_category($post_id);
    $_data['category_name'] =$category[0]->cat_name;
    
    //$tin_post_views =  get_post_meta( $post->ID, 'tin_post_views' );
        //$_data[view] = $tin_post_views;
    
	$data->data = $_data;
 
	return $data;
 
}

function custom_fields_rest_prepare_category( $data, $item, $request ) {	  
    $category_thumbnail_image='';
    $temp='';
    if($temp=get_term_meta($item->term_id,'thumbnail',true))
    {
        $category_thumbnail_image=$temp;
      
    }
    elseif($temp=get_term_meta($item->term_id,'catcover',true));
    {
        $category_thumbnail_image=$temp;
    }
    
	$data->data['category_thumbnail_image'] =$category_thumbnail_image;    
	return $data;
}


function get_post_content_first_image($post_content){
	if(!$post_content){
		$the_post		= get_post();
		$post_content	= $the_post->post_content;
	} 

	preg_match_all( '/class=[\'"].*?wp-image-([\d]*)[\'"]/i', $post_content, $matches );
	if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	
		$image_id = $matches[1][0];
		if($image_url = get_post_image_url($image_id, $size)){
			return $image_url;
		}
	}

	preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
	if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	   
		return $matches[1][0];
	}
}

function get_post_image_url($image_id, $size='full'){
	if($thumb = wp_get_attachment_image_src($image_id, $size)){
		return $thumb[0];
	}
	return false;	
}

add_filter('rest_allow_anonymous_comments','set_rest_allow_anonymous_comments'); //允许匿名评论
add_filter( 'rest_prepare_category', 'custom_fields_rest_prepare_category', 10, 3 ); //获取分类的封面图片
add_filter( 'rest_prepare_post', 'custom_fields_rest_prepare_post', 10, 3 ); //获取文章的缩略图，评论数目，分类名称


/*********   给分类添加微信小程序封面 *********/

add_action( 'category_add_form_fields', 'weixin_new_term_catcover_field' );
function weixin_new_term_catcover_field() {
    wp_nonce_field( basename( __FILE__ ), 'weixin_app_term_catcover_nonce' ); ?>

    <div class="form-field weixin-app-term-catcover-wrap">
        <label for="weixin-app-term-catcover">微信小程序封面</label>
        <input type="url" name="weixin_app_term_catcover" id="weixin-app-term-catcover"  class="type-image regular-text" data-default-catcover="" />
    </div>
<?php }
add_action( 'category_edit_form_fields', 'weixin_edit_term_catcover_field' );
function weixin_edit_term_catcover_field( $term ) {
    $default = '';
    $catcover   = get_term_meta( $term->term_id, 'catcover', true );

    if ( ! $catcover )
        $catcover = $default; ?>

    <tr class="form-field weixin-app-term-catcover-wrap">
        <th scope="row"><label for="weixin-app-term-catcover">微信小程序封面</label></th>
        <td>
            <?php echo wp_nonce_field( basename( __FILE__ ), 'weixin_app_term_catcover_nonce' ); ?>
            <input type="url" name="weixin_app_term_catcover" id="weixin-app-term-catcover" class="type-image regular-text" value="<?php echo esc_attr( $catcover ); ?>" data-default-catcover="<?php echo esc_attr( $default ); ?>" />
        </td>
    </tr>
<?php }

add_action( 'create_category', 'weixin_app_save_term_catcover' );
add_action( 'edit_category',   'weixin_app_save_term_catcover' );

function weixin_app_save_term_catcover( $term_id ) {
    if ( ! isset( $_POST['weixin_app_term_catcover_nonce'] ) || ! wp_verify_nonce( $_POST['weixin_app_term_catcover_nonce'], basename( __FILE__ ) ) )
        return;

    $catcover = isset( $_POST['weixin_app_term_catcover'] ) ? $_POST['weixin_app_term_catcover'] : '';

    if ( '' === $catcover ) {
        delete_term_meta( $term_id, 'catcover' );
    } else {
        update_term_meta( $term_id, 'catcover', $catcover );
    }
}

/*********  *********/




?>