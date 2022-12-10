<?php
    switch( true )
    {
        case isset( $_POST['getLocations'] ) :
                require_once __DIR__ . '/../classes/Location.php';
                $Location = new Location();
                echo $Location->getLocations();
        break;
        
        case isset( $_POST['changePassword'] ) :
                require_once __DIR__ . '/../classes/ManageUser.php';
                $ManageUser = new ManageUser();
                echo $ManageUser->changePassword( $_POST['current_password'] , $_POST['user_password'] );
        break;
        
        case isset( $_POST['getBanners'] ) :
                require_once __DIR__ . '/../classes/Banner.php';
                $Banner = new Banner();
                echo $Banner->getBanners( $_POST['locale_id'] );
        break;

        case isset( $_POST['getDepartments'] ) :
                require_once __DIR__ . '/../classes/Department.php';
                $Department = new Department();
                echo $Department->getDepartments();
        break;
        
        case isset( $_POST['getCategories'] ) :
                require_once __DIR__ . '/../classes/Category.php';
                $Category = new Category();
                echo $Category->getCategories( $_POST['department_id'] );
        break;

        case isset( $_POST['createContentListing'] ) :
                require_once __DIR__ . '/../classes/ContentListing.php';
                $ContentListing = new ContentListing();
                echo $ContentListing->createContentListing( $_POST['param_table'] , json_decode($_POST['param_sql'] , true) , $_POST['locale_name'] , $_POST['filter'] , $_POST['search'] , $_POST['offset'] , $_POST['limit'] );
        break;

        case isset( $_POST['createFilterListing'] ) :
                require_once __DIR__ . '/../classes/ContentListing.php';
                $ContentListing = new ContentListing();
                echo $ContentListing->createFilterListing( $_POST['param_table'] , $_POST['section'] );
        break;
        
        case isset( $_POST['createProductDetails'] ) :
                require_once __DIR__ . '/../classes/ContentDetail.php';
                $ContentDetail = new ContentDetail();
                echo $ContentDetail->createProductDetails( $_POST['product_url'] , $_POST['locale_name'] );
        break;
        
        case isset( $_POST['getProductPricingHistory'] ) :
                require_once __DIR__ . '/../classes/TrackerFetcher.php';
                $TrackerFetcher = new TrackerFetcher();
                echo $TrackerFetcher->getProductPricingHistory( $_POST['link_tracker_ids'] );
        break;
        
        case isset( $_POST['createdRelatedProductListing'] ) :
                require_once __DIR__ . '/../classes/ContentDetail.php';
                $ContentDetail = new ContentDetail();
                echo $ContentDetail->createdRelatedProductListing( $_POST['product_url'] , $_POST['locale_name'] , $_POST['filter_json'] );
        break;

        case isset( $_POST['createSearchFilter'] ) :
                require_once __DIR__ . '/../classes/ContentListing.php';
                $ContentListing = new ContentListing();
                echo $ContentListing->createSearchFilter( $_POST['section'] , $_POST['search'] );
        break;
        
        case isset( $_POST['getPriceAlerts'] ) :
                require_once __DIR__ . '/../classes/PriceAlert.php';
                $PriceAlert = new PriceAlert();
                echo $PriceAlert->getPriceAlerts( $_POST['affiliate_ids'] );
        break;

        case isset( $_POST['savePriceAlert'] ) :
                require_once __DIR__ . '/../classes/PriceAlert.php';
                $PriceAlert = new PriceAlert();
                echo $PriceAlert->savePriceAlert( $_POST['affiliate_id'] , $_POST['alert_price'] , $_POST['price_alert_id'] );
        break;
        
        case isset( $_POST['deletePriceAlert'] ) :
                require_once __DIR__ . '/../classes/PriceAlert.php';
                $PriceAlert = new PriceAlert();
                echo $PriceAlert->deletePriceAlert( $_POST['price_alert_id'] );
        break;
        
        case isset( $_POST['addClickCount'] ) :
                require_once __DIR__ . '/../classes/ContentDetail.php';
                $ContentDetail = new ContentDetail();
                echo $ContentDetail->addClickCount( $_POST['product_url'] , $_POST['locale_name'] );
        break;

        case isset($_POST['contact']) : userEmailToAdmin();
        break;
        
        default : echo '<h1>Cannot Access File</h1>';
    }
?>