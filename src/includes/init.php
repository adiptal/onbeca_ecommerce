<?php
    require_once __DIR__ . '/../dashboard/classes/Database.php';
    
    $url = 'http://localhost/onbeca/';
    $site_name = 'ONBECA';
    $page_image = $url . 'img/logo.png';
    $reg_url = preg_replace( '/\//m' , '\\\\/' , $url );
    
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url_request = explode( '/' , substr( $current_url , strlen($url) ) );

    require_once __DIR__ . '/../dashboard/classes/Location.php';
    $Location = new Location();
    $locale_list = [];
    foreach (json_decode($Location->getLocations() , true ) as $key => $value) {
        array_push( $locale_list , $value[1] );
    }
    header("Content-Type: text/html");

    if( $url_request[0] == null || $url_request[0] == '' || !in_array( $url_request[0] , $locale_list ) )
    {
        $locale_input = $url_request[0];
        try {
                $geoplugin = json_decode( @file_get_contents('http://www.geoplugin.net/json.gp?ip=' . $_SERVER['REMOTE_ADDR'] ) , true );
                if( isset( $geoplugin['geoplugin_continentCode'] ) && in_array( $geoplugin['geoplugin_continentCode'] , $locale_list ) )
                {
                        $locale_name = $geoplugin['geoplugin_continentCode'];
                        array_unshift( $url_request , $locale_name );
                }
                else if( isset( $geoplugin['geoplugin_countryCode'] ) )
                {
                        $locale_name = $geoplugin['geoplugin_countryCode'];
                        array_unshift( $url_request , $locale_name );
                }
                else
                {
                        $locale_name = 'IN';
                }
        } catch (\Throwable $th) {
                $locale_name = 'IN';
        } finally {
                if( $locale_input != null && $locale_input != '' && !in_array( $locale_input , $locale_list ) )
                {
                        $current_url = $url . $locale_name . '/404/';
                }
        }
    }
    else
    {
        $locale_name = $url_request[0];
    }
    
    //  PAGE ROUTES CONFIGURATION
    $error_response_code_enable = true;
    require_once __DIR__ . '/routes.php';
?>