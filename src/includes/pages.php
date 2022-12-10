<?php
switch( $page )
{
    case 'static' :   require_once __DIR__ . '/static/'.$url_request[1].'.php';
    break;

    case 'index' :   require_once __DIR__ . '/pages/main-content.php';
    break;

    case 'container-listing' :   require_once __DIR__ . '/pages/container-listing.php';
    break;

    case 'search' :   require_once __DIR__ . '/pages/search.php';
    break;

    case 'products' :   require_once __DIR__ . '/pages/products.php';
    break;

    case 'error' : 
        error404( $url . $locale_name , $site_name );
    break;
    
    default :
        http_response_code(404);
        echo '<script>window.location.replace( "'. $url . $locale_name .'/404/" );</script>';
}

function error404( $location , $site_name )
{
    echo '<section class="row container-details"><div class="breadcrump col-12"><ul><li><a href="'. $location .'/" title="'. $site_name .'">Home</a></li><li>Page Not Found</li></ul></div><div class="product col-12"><header><h2>Page Not Found</h2></header></div></section><script>$( document ).trigger( "stopLoading" );</script>';
}

header("Content-Type: text/html");
?>