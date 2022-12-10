<?php
    require_once __DIR__ . '/../classes/Database.php';
    
    $parent_url = 'http://localhost/onbeca/';
    $url = $parent_url . 'dashboard/';
    $site_name = 'ONBECA';
    $reg_url = preg_replace( '/\//m' , '\\\\/' , $url );
    
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url_request = explode( '/' , substr( $current_url , strlen($url) ) );

    if( isset( $_SESSION['user_id'] ) && $_SESSION['user_id'] != '' )
    {
        switch( $_SESSION['user_role_id'] )
        {
                case 1 : require_once __DIR__ . '/user_inits/adminInit.php';
                break;

                case 2 : require_once __DIR__ . '/user_inits/employInit.php';
                break;

                case 3 : require_once __DIR__ . '/user_inits/usersInit.php';
                break;
        }
    }
    else
    {
        switch( true )
        {
            case (preg_match('/.*?('. $reg_url .')(doBackup)((\/$)|$)/', $current_url) ? true : false) :
                    require_once __DIR__ . '/../classes/Backup.php';
                    $Backup = new Backup();
                    $Backup->backupFiles();
                    exit();
            break;

            case (preg_match('/.*?('. $reg_url .')(refreshLinkTrackerPricing)((\/$)|$)/', $current_url) ? true : false) :
                    require_once __DIR__ . '/../classes/Automation.php';
                    $Automation = new Automation();
                    $Automation->refreshLinkTrackerPricing();
                    exit();
            break;

            case ( isset( $_GET['saveTrackerFetcher'] ) ? true : false) :
                    $price = preg_replace( '/[^0-9\.]/' , '' , $_GET['price'] );
                    require_once __DIR__ . '/../classes/TrackerFetcher.php';
                    $TrackerFetcher = new TrackerFetcher();
                    if( $TrackerFetcher->saveTrackerFetcher( $_GET['link_tracker_id'] , $price ) )
                    {
                        $TrackerFetcher->affiliatePricingUpdate( $_GET['link_tracker_id'] , $price );
                    }
                    exit();
            break;

            case ( isset( $_GET['pricingFetcher'] ) ? true : false) :
                    require_once __DIR__ . '/../classes/TrackerFetcher.php';
                    $TrackerFetcher = new TrackerFetcher();
                    $TrackerFetcher->pricingFetcher( $_GET['link_tracker_id'] , $_GET['link_url'] );
                    exit();
            break;

            default :
            header('Location: '. $parent_url .'account/');
            exit();
        }
    }
?>