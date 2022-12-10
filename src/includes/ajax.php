<?php
        require_once __DIR__ . '/../dashboard/classes/Database.php';
        if( isset( $_POST['checkSessionActive'] ) )
        {
                if( isset( $_SESSION['user_id'] ) && !empty( $_SESSION['user_id'] ) )
                {
                        echo true;
                }
        }

        if( isset( $_POST['getPage'] ) && !empty( $_POST['getPage'] ) )
        {
                $url = 'http://localhost/onbeca/';
                $site_name = 'ONBECA';
                $page_image = $url . 'img/logo.png';
                $reg_url = preg_replace( '/\//m' , '\\\\/' , $url );
                
                $current_url = $_POST['getPage'];
                $url_request = explode( '/' , substr( $current_url , strlen($url) ) );
                $locale_name = $url_request[0];
                
                //  PAGE ROUTES CONFIGURATION
                $error_response_code_enable = false;
                require_once __DIR__ . '/routes.php';

                //  ROUTE PAGES ACCESS
                require_once __DIR__ . '/pages.php';

                
                //  TITLE DISPLAY
                echo '<title>'. $title .'</title>';
        }
?>