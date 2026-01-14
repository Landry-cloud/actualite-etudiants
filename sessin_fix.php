<?php
function initSession(){
    if(session_id()){
        session_destroy();
    }
}

ini_set('session.cookie_secure',false);
ini_set('session.cookie_httponly',true);
ini_set('session.use_only_cookies',1);
session_start();
session_regenerate_id(true);
?>