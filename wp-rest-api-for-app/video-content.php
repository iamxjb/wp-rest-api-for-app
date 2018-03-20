<?php
//解析腾讯视频，只支持一个腾讯视频
function video_content_filter($content) {

	//$content  =get_the_content();
    preg_match('/https\:\/\/v.qq.com\/x\/(\S*)\/(\S*)\.html/',$content,$matches);
    if($matches)
    {
    	$vids=$matches[2];
	    $url='http://vv.video.qq.com/getinfo?vid='.$vids.'&defaultfmt=auto&otype=json&platform=1&defn=fhd&charge=0';
	    $res = file_get_contents($url);
	    if($res)
	    {
	    	$str = substr($res,13,-1);
		    $newStr =json_decode($str,true);	    
		    $videoUrl= $newStr['vl']['vi'][0]['ul']['ui'][2]['url'].$newStr['vl']['vi'][0]['fn'].'?vkey='.$newStr['vl']['vi'][0]['fvkey']; 
		    $contents = preg_replace('~<video (.*?)></video>~s','<video src="'.$videoUrl.'" controls="controls"></video>',$content);
		    return $contents;

	    }
	    else
	    {
	    	return $content;
	    }
	    
    }
    else
    {
    	return $content;
    }
    
      

     }
add_filter( 'the_content', 'video_content_filter' );


