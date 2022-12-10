<?php
    switch( true )
    {
        case (preg_match('/.*?'. $reg_url .'(\S[^\/]+)\/404((\/$)|$)/',  $current_url) ? true : false) :   $page = 'error';
                if( $error_response_code_enable )
                {
                    http_response_code(404);
                }
                $title = 'Error 404 | ' . $site_name;
                $desc = 'Error 404 Page Not Found | ' . $site_name;
        break;

        case (preg_match('/.*?'. $reg_url .'(\S[^\/]+)\/sitemap((\/$)|$)/',  $current_url) ? true : false) :   $page = 'sitemap';
                $title = 'Sitemap | ' . $site_name;
                $desc = 'Sitemap Page | ' . $site_name;
                require_once __DIR__ . '/static/sitemap.php';
                die();
        break;
        
        case ( isset( $url_request[1] ) && file_exists( __DIR__ . '/static/'.$url_request[1].'.php' ) && (preg_match('/.*?'. $reg_url .'(\S[^\/]+)\/(\S[^\/]+)((\/$)|$)/',  $current_url) ) ? true : false) :
                $page = 'static';
                switch( $url_request[1] )
                {
                        case 'advertiser-disclosure':
                                $title = 'Advertiser Disclosure | ' . $site_name;
                                $desc = 'Read It - Advertiser Disclosure | ' . $site_name;
                        break;
                        
                        case 'privacy-policy':
                                $title = 'Privacy Policy | ' . $site_name;
                                $desc = 'Read It - Privacy Policy | ' . $site_name;
                        break;
                        
                        case 'terms':
                                $title = 'Terms of Use | ' . $site_name;
                                $desc = 'Read It - Terms of Use | ' . $site_name;
                        break;
                        
                        case 'about-us':
                                $title = 'About Us | ' . $site_name;
                                $desc = 'Get In Touch - About Us | ' . $site_name;
                        break;
                        
                        case 'contact-us':
                                $title = 'Contact Us | ' . $site_name;
                                $desc = 'Get In Touch - Contact Us | ' . $site_name;
                        break;
                }
        break;
        
        case (preg_match('/.*?'. $reg_url .'(\S[^\/]+)?((\/$)|$)/',  $current_url) ? true : false) :
                $page = 'index';
                $title = 'Set Price Alerts, Track and Compare Product Prices | ' . $site_name;
                $desc = $site_name . ' provide users unique E-Commerce experience by features like Set Price Alerts, Track and Compare Product Prices that are provided by different companies';
        break;
        
        case (preg_match('/.*?'. $reg_url .'(\S[^\/]+)\/search\/(\S[^\/]+)\/(\S[^\/]+)?(\/\d+(\/(\S[^\/]+))?)?((\/$)|$)/',  $current_url) ? true : false) :
                $page = 'search';
                $title =  urldecode($url_request[3]) . ' - Product Search Page | ' . $site_name;
                $desc = urldecode($url_request[3]) . ' - Product Search Page | ' . $site_name;
                        
                $param_table = 'department';
                $url_params = [ urldecode( $url_request[2] ) , 'department_id' , 'department_name' ];

                if( isset( $url_request[4] ) && !empty( $url_request[4] ) )
                {
                        $offset = $url_request[4];
                }
                else
                {
                        $offset = 1;
                }
        break;

        case ((sizeof($url_request) < 4 && preg_match('/.*?'. $reg_url .'(\S[^\/]+)\/(\S[^\/]+)((\/$)|$)/',  $current_url)) ? true : false) :
                require_once __DIR__ . '/../dashboard/classes/ContentDetail.php';
                $ContentDetail = new ContentDetail();
                $product = json_decode( $ContentDetail->getProductImageUrl( $url_request[0] , $url_request[1] ) , true );
                header("Content-Type: text/html");
                
                if( $product != false )
                {
                        $page_image = $url . 'dashboard/includes/blogimages/' . $product[1];
        
                        $page = 'products';
                        $title = $product[0] . ' | ' . $site_name;
                        $desc = $product[0] . ' - Set Price Alerts, Track and Compare Product Prices that are provided by different companies | ' . $site_name;
                }
                else
                {
                        $page = '';
                        $title = 'Error 404 | ' . $site_name;
                        $desc = 'Error 404 Page Not Found | ' . $site_name;
                }
        break;

        case ((sizeof($url_request) < 6 && preg_match('/.*?'. $reg_url .'(\S[^\/]+)\/(\S[^\/]+)(\/\d+(\/(\S[^\/]+))?)?((\/$)|$)/',  $current_url)) ? true : false) :
                if( checkUrlVunerability( 'department' , [ $url_request[1] , 'department_id' , 'department_name' ] ) != false )
                {
                        $page = 'container-listing';
                        $title = urldecode( $url_request[1] ) . ' | ' . $site_name;
                        $desc = $title;
                        
                        $param_table = 'department';
                        $url_params = [ urldecode( $url_request[1] ) , 'department_id' , 'department_name' ];

                        $offset = $url_request[2];
                        $filter = $url_request[3];
                }
                else
                {
                        $page = '';
                        $title = 'Error 404 | ' . $site_name;
                        $desc = 'Error 404 Page Not Found | ' . $site_name;
                }
        break;

        case ((sizeof($url_request) < 7 && preg_match('/.*?'. $reg_url .'(\S[^\/]+)\/(\S[^\/]+)\/(\S[^\/]+)(\/\d+(\/(\S[^\/]+))?)?((\/$)|$)/',  $current_url)) ? true : false) :
                if(
                checkUrlVunerability( 'department' , [ $url_request[1] , 'department_id' , 'department_name' ] ) != false &&
                checkUrlVunerability( 'category' , [ $url_request[2] , 'category_id' , 'category_name' ] ) != false
                )
                {
                        $page = 'container-listing';
                        $title = urldecode( $url_request[2] ) . ' | ' . $site_name;
                        $desc = $title;
                        
                        $param_table = 'category';
                        $url_params = [ urldecode( $url_request[2] ) , 'category_id' , 'category_name' ];

                        $offset = $url_request[3];
                        $filter = $url_request[4];
                }
                else
                {
                        $page = '';
                        $title = 'Error 404 | ' . $site_name;
                        $desc = 'Error 404 Page Not Found | ' . $site_name;
                }
        break;

        case ((sizeof($url_request) < 8 && preg_match('/.*?'. $reg_url .'(\S[^\/]+)\/(\S[^\/]+)\/(\S[^\/]+)\/(\S[^\/]+)(\/\d+(\/(\S[^\/]+))?)?((\/$)|$)/',  $current_url)) ? true : false) :
                if(
                checkUrlVunerability( 'department' , [ $url_request[1] , 'department_id' , 'department_name' ] ) != false &&
                checkUrlVunerability( 'category' , [ $url_request[2] , 'category_id' , 'category_name' ] ) != false &&
                checkUrlVunerability( 'sub_category' , [ $url_request[3] , 'sub_category_id' , 'sub_category_name' ] ) != false
                )
                {
                        $page = 'container-listing';
                        $title = urldecode( $url_request[3] ) . ' | ' . $site_name;
                        $desc = $title;
                        
                        $param_table = 'sub_category';
                        $url_params = [ urldecode( $url_request[3] ) , 'sub_category_id' , 'sub_category_name' ];

                        $offset = $url_request[4];
                        $filter = $url_request[5];
                }
                else
                {
                        $page = '';
                        $title = 'Error 404 | ' . $site_name;
                        $desc = 'Error 404 Page Not Found | ' . $site_name;
                }
        break;

        default :   $page = '';
                $title = 'Error 404 | ' . $site_name;
                $desc = 'Error 404 Page Not Found | ' . $site_name;
        break;
    }

    $ContentDetail = null;
    function checkUrlVunerability( $param_table , $param_sql )
    {
        global $ContentDetail;
        
        if( !is_object( $ContentDetail ) )
        {
                require_once __DIR__ . '/../dashboard/classes/ContentDetail.php';
                $ContentDetail = new ContentDetail();
        }
        
        $param_sql[0] = urldecode( $param_sql[0] );
        return $ContentDetail->getID( $param_table , [ $param_sql[0] , $param_sql[1] , $param_sql[2] ] );
    }
?>