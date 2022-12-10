<main class="row">
    <div class="col-12 col-md-9 offset-md-3 col-lg-10 offset-lg-2">
<?php
    if( isset( $_SESSION['is_verified'] ) && ( $_SESSION['is_verified'] == 0 || $_SESSION['is_verified'] == '' || $_SESSION['is_verified'] == null ) )
    {
        require_once('includes/pages/settings.php');
    }
    else
    {
        $get_file = 'includes/pages/'. $page .'.php';
        switch( $page )
        {
            case ( ($page === null) ? true : false ) :   getError( $url );

            case ( file_exists($get_file) ? true : false )  :   require_once($get_file);
            break;
            
            default :   getError( $url );
        }
    }

    function getError( $url )
    {
        http_response_code(404);
        echo '<style>html, body, main{height: 100%;}.error{background: url('. $url .'img/404.jpg) no-repeat center center; width: 100%; height: 100%; display: table; background-size: cover;}.error .child{display: table-cell; vertical-align: middle; margin: 0 auto; padding-left: 3em;}.error h2{font-family: "Josefin Sans", sans-serif; letter-spacing: 2px; font-size: 6em; font-weight: 700; color: #FFF;}.error p{font-size: 2.5em; color: #FFF; letter-spacing: 4px}@media(max-width:767px){main{height: 42%;}.error{height: 100%}.error .child{padding-left: 1.5em;}.error h2{font-size: 3em; color: #FFF;}.error p{font-size: 1.5em; color: #FFF; letter-spacing: 1px}}@media(min-width:768px) and (max-width:1199px){main{height: 65%;}.error .child{padding-left: 2em;}}</style><section class="error"><div class="child"><h2>Error 404 !</h2><p>Sorry, Page Not Found.</p></div></section>';
    }
?>
    </div>
</main>