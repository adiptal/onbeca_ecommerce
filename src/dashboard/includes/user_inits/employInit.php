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
        
    case (preg_match('/.*?('. $reg_url .')(products)((\/\d+(\/[^\/]\S[^\/]+)?)?)((\/$)|$)/', $current_url, $matches, PREG_OFFSET_CAPTURE, 0) ? true : false) :
            $page = $matches[2][0];
            $title = ucfirst( $matches[2][0] ) . ' | ' . $site_name;
            $desc = $title;
    break;
        
    case (preg_match('/.*?('. $reg_url .')(products|images|settings)((\/$)|$)/', $current_url, $matches, PREG_OFFSET_CAPTURE, 0) ? true : false) :
            $page = $matches[2][0];
            $title = ucfirst( $matches[2][0] ) . ' | ' . $site_name;
            $desc = $title;
    break;
        
    case (preg_match('/.*?('. $reg_url .')([^\/]\S[^\/]+)\/(\d+)((\/$)|$)/', $current_url, $matches, PREG_OFFSET_CAPTURE, 0) ? true : false) :
            $product_id = $matches[3][0];

            require_once('./classes/Product.php');
            $Product = new Product();
            $Product->getProductInfo( $product_id );
            if( json_decode( $Product->getProductInfo( $product_id ) , true ) != false )
            {
                $page = $matches[2][0];
                $title = ucfirst( $matches[2][0] ) . ' | ' . $site_name;
                $desc = $title;
            }
            else
            {
                $page = 'error';
                $title = 'Error 404 | ' . $site_name;
                $desc = $title;
            }
            header("Content-Type: text/html");
    break;

    default :
            $page = 'error';
            $title = 'Error 404 | ' . $site_name;
            $desc = $title;
    break;
}
?>