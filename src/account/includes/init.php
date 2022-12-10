<?php
    require_once('../dashboard/classes/Database.php');
    
    $parent_url = 'http://localhost/onbeca/';
    $url = $parent_url . 'account/';
    $site_name = 'ONBECA';
    $reg_url = preg_replace( '/\//m' , '\\\\/' , $url );
    
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url_request = explode( '/' , substr( $current_url , strlen($url) ) );

    if( !isset( $_SESSION['user_id'] ) || $_SESSION['user_id'] == '' )
    {
        switch( true )
        {
            case (preg_match('/.*?'. $reg_url .'$/',  $current_url) ? true : false) :
                    $page = 'signIn';
                    $title = 'SignIn Account | ' . $site_name;
                    $desc = $title;
            break;
                
            case (preg_match('/.*?('. $reg_url .')reset\/([^\/]\S[^\/]+)((\/$)|$)/', $current_url, $matches, PREG_OFFSET_CAPTURE, 0) ? true : false) :
                    require_once('classes/Accounts.php');
                    $Accounts = new Accounts();
                    if( $Accounts->checkTokenExists( $url_request[1] ) != false )
                    {
                        $page = $url_request[0];
                        $title = ucfirst( $url_request[0] ) . ' Account | ' . $site_name;
                        $desc = $title;
                    }
                    else
                    {
                        $page = 'error-token';
                        $title = 'Error 404 | ' . $site_name;
                        $desc = $title;
                    }
            break;
                
            case (( preg_match('/.*?('. $reg_url .')([^\/]\S[^\/]+)((\/$)|$)/', $current_url, $matches, PREG_OFFSET_CAPTURE, 0) && $url_request[0] != 'reset' ) ? true : false) :
                    $page = $matches[2][0];
                    $title = ucfirst( $matches[2][0] ) . ' Account | ' . $site_name;
                    $desc = $title;
            break;

            default :
                    $page = 'error';
                    $title = 'Error 404 | ' . $site_name;
                    $desc = $title;
            break;
        }
    }
    else
    {
        header('Location: '. $parent_url .'dashboard/');
        exit();
    }
?>