<?php
switch( true )
{
    case (preg_match('/.*?'. $reg_url .'$/',  $current_url) ? true : false) :
            $page = 'dashboard';
            $title = 'Dashboard | ' . $site_name;
            $desc = $title;
    break;
        
    case (preg_match('/.*?('. $reg_url .')(logout)((\/$)|$)/', $current_url) ? true : false) :      $_SESSION = null;
            session_destroy();
            header('Location: ' . $url);
            exit();
    break;
        
    case (preg_match('/.*?('. $reg_url .')(settings)((\/$)|$)/', $current_url, $matches, PREG_OFFSET_CAPTURE, 0) ? true : false) :
            $page = $matches[2][0];
            $title = ucfirst( $matches[2][0] ) . ' | ' . $site_name;
            $desc = $title;
    break;

    default :
            $page = 'error';
            $title = 'Error 404 | ' . $site_name;
            $desc = $title;
    break;
}
?>