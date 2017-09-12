<?php
//启用匿名评论
function set_rest_allow_anonymous_comments() {
    return true;
}
add_filter('rest_allow_anonymous_comments','set_rest_allow_anonymous_comments');


