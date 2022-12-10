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
        
        case isset( $_POST['getSubCategory'] ) :
                require_once __DIR__ . '/../classes/SubCategory.php';
                $SubCategory = new SubCategory();
                echo $SubCategory->getSubCategory( $_POST['sub_category_id'] );
        break;
        
        case isset( $_POST['getSubCategories'] ) :
                require_once __DIR__ . '/../classes/SubCategory.php';
                $SubCategory = new SubCategory();
                echo $SubCategory->getSubCategories( $_POST['category_id'] );
        break;
        
        case isset( $_POST['getProducts'] ) :
                require_once __DIR__ . '/../classes/Product.php';
                $Product = new Product();
                echo $Product->getProducts( $_POST['sub_category_id'] , $_POST['limit'] , $_POST['offset'] , $_POST['search'] );
        break;
        
        case isset( $_POST['getProductInfo'] ) :
                require_once __DIR__ . '/../classes/Product.php';
                $Product = new Product();
                echo $Product->getProductInfo( $_POST['product_id'] );
        break;

        case isset( $_POST['saveProduct'] ) :
                require_once __DIR__ . '/../classes/Product.php';
                $Product = new Product();
                echo $Product->saveProduct( $_POST['sub_category_id'] , $_POST['product_name'] , $_POST['product_image_url'] , $_POST['filter_json'] , $_POST['product_id'] , $_POST['locale_id'] );
        break;
        
        case isset( $_POST['manageProductArticle'] ) :
                require_once __DIR__ . '/../classes/Product.php';
                $Product = new Product();
                echo $Product->manageProductArticle( $_POST['product_id'] , $_POST['article_data'] );
        break;
        
        case isset( $_POST['editProductArticle'] ) :
                require_once __DIR__ . '/../classes/Product.php';
                $Product = new Product();
                echo $Product->editProductArticle( $_POST['product_id'] );
        break;
        
        case isset( $_POST['getProductLocations'] ) :
                require_once __DIR__ . '/../classes/ProductLocation.php';
                $ProductLocation = new ProductLocation();
                echo $ProductLocation->getProductLocations( $_POST['product_id'] );
        break;
        
        case isset( $_POST['getAffiliates'] ) :
                require_once __DIR__ . '/../classes/Affiliate.php';
                $Affiliate = new Affiliate();
                echo $Affiliate->getAffiliates( $_POST['product_id'] , $_POST['locale_id'] );
        break;
        
        case isset( $_POST['getAffiliateInfo'] ) :
                require_once __DIR__ . '/../classes/Affiliate.php';
                $Affiliate = new Affiliate();
                echo $Affiliate->getAffiliateInfo( $_POST['affiliate_id'] );
        break;

        case isset( $_POST['saveAffiliate'] ) :
                require_once __DIR__ . '/../classes/Affiliate.php';
                $Affiliate = new Affiliate();
                echo $Affiliate->saveAffiliate( $_POST['product_id'] , $_POST['locale_id'] , $_POST['affiliate_company'] , $_POST['product_condition'] , $_POST['affiliate_pricing'] , $_POST['affiliate_url'] , $_POST['manageAffiliateTracking'] , $_POST['affiliate_id'] );
        break;
        
        case isset( $_POST['deleteAffiliate'] ) :
                require_once __DIR__ . '/../classes/Affiliate.php';
                $Affiliate = new Affiliate();
                echo $Affiliate->deleteAffiliate( $_POST['affiliate_id'] );
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

        case isset( $_POST['uploadImage'] ) :
                require_once __DIR__ . '/../classes/Image.php';
                $image = new Image();
                echo $image->addImage();
        break;

        case isset( $_POST['getImageStack'] ) :
                require_once __DIR__ . '/../classes/Image.php';
                $image = new Image();
                echo $image->getImageStack();
        break;

        case isset($_POST['contact']) : userEmailToAdmin();
        break;
        
        default : echo '<h1>Cannot Access File</h1>';
    }
?>