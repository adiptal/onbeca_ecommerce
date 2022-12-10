<div class="main-section">
    <!-- BANNER IMAGES -->
    <div class="banner">
        <?php
            $connection = $database->getConnection();
            $query = "SELECT locale_id FROM locale WHERE locale_name LIKE '". $locale_name ."'" ;
            $preparedStatement = $connection->prepare( $query );
            $preparedStatement->execute();
            $preparedStatement->store_result();
            
            $preparedStatement->bind_result( $locale_id );
            if( $preparedStatement->fetch() )
            {
                require_once __DIR__ . '/../../dashboard/classes/Banner.php';
                $Banner = new Banner();
                $bannerList = json_decode( $Banner->getBanners( $locale_id ) , true );
                if( sizeof( $bannerList ) != 0 )
                {
                    echo '<button class="previous"><i class="fas fa-chevron-left"></i></button><button class="next"><i class="fas fa-chevron-right"></i></button><div class="row">';
                    for( $i = 0 ; $i < sizeof( $bannerList ) ; $i++ )
                    {
                        if( preg_match( '/^(http:|https:)\/\//' , $bannerList[$i][1] ) )
                        {
                            if( !preg_match( '/^(http:|https:)\/\//' , $bannerList[$i][2] ) )
                            {
                                $regex_image = $url .'dashboard/includes/blogimages/'. $bannerList[$i][2];
                            }
                            else
                            {
                                $regex_image = $bannerList[$i][2];
                            }

                            echo 
                            '<div class="col-12"><a class="link" target="_blank" href="'. $bannerList[$i][1] .'" title="Banner Link '. ( $i+1 ) .'"><img src="data:image/png;base64," class="lazy" data-src="'. $regex_image .'" alt="'. $bannerList[$i][2] .'"></a></div>';
                        }
                        else
                        {
                            if( !preg_match( '/^(http:|https:)\/\//' , $bannerList[$i][2] ) )
                            {
                                $regex_image = $url .'dashboard/includes/blogimages/'. $bannerList[$i][2];
                            }
                            else
                            {
                                $regex_image = $bannerList[$i][2];
                            }

                            echo 
                            '<div class="col-12"><a href="'. $url . $locale_name . '/' . $bannerList[$i][1] .'" title="Banner Link '. ( $i+1 ) .'"><img src="data:image/png;base64," class="lazy" data-src="'. $regex_image .'" alt="'. $bannerList[$i][2] .'"></a></div>';
                        }
                    }
                    echo '</div>';
                }
            }
        ?>
    </div>
    <!-- END BANNER IMAGES -->

    <!-- PRODUCTS DEPARTMENT WISE -->
    <?php
        require_once __DIR__ . '/../../dashboard/classes/Department.php';
        $Department = new Department();
        $departmentList = json_decode( $Department->getDepartments() , true );
        
        if( sizeof( $departmentList ) != 0 )
        {
            require_once __DIR__ . '/../../dashboard/classes/ContentListing.php';
            $ContentListing = new ContentListing();
            
            for( $i = 0 ; $i < sizeof( $departmentList ) ; $i++ )
            {
                echo '<section id="section-'. $i .'" class="product-sections"><header><h2>'. $departmentList[$i][1] .'</h2></header><a href="'. $url . $locale_name .'/'. $departmentList[$i][1] .'/1/" title="'. $departmentList[$i][1] .'" class="view">View More</a><button class="previous"><i class="fas fa-chevron-left"></i></button><button class="next"><i class="fas fa-chevron-right"></i></button><div class="row">';
                
                $param_sql = [ $departmentList[$i][1] , 'department_id' , 'department_name' ];
                $contentList = json_decode( $ContentListing->createContentListing( 'department' , $param_sql , $locale_name , '' , '' , 0 , 7 ) , true );

                if( isset( $contentList['products'] ) && sizeof( $contentList['products'] ) != 0 )
                {
                    $products = $contentList['products'];
                    for( $j = 0 ; $j < sizeof( $products ) ; $j++ )
                    {
                        if( !preg_match( '/^(http:|https:)\/\//' , $products[$j][3] ) )
                        {
                            $regex_image = $url .'dashboard/includes/blogimages/'. $products[$j][3];
                        }
                        else
                        {
                            $regex_image = $products[$j][3];
                        }

                        echo
                        '<div class="col-12 col-sm-6 col-md-4 col-lg-3"><a href="'. $url . $locale_name . '/' . $products[$j][2] . '/" title="'. $products[$j][1] .'" class="card"><img src="data:image/png;base64," class="lazy" data-src="'. $regex_image .'" alt="'.$products[$j][1].'"><span>' . $products[$j][1] . '</span></a>';

                        if( isset( $products[$j][4] ) && !empty( $products[$j][4] ) )
                        {
                            echo '<button class="external" data-url="'.$products[$j][4][4].'"><i class="fas fa-external-link-alt"></i> '.$products[$j][4][1].'</button><small>' . $products[$j][4][3] .'</small>';
                        }

                        echo '</div>';
                    }
                }

                echo '</div></section>';
            }
        }
    ?>
    <!-- END PRODUCTS DEPARTMENT WISE -->
</div>

<script>
    $('[data-url]').css({
        'width' : 'calc( ' + $('[data-url]').closest('[class^="col"]').find('.card').outerWidth() + 'px - .25em )'
    });
    
    $( document ).trigger( "searchOptionsReady" );
    $( document ).trigger( "stopLoading" );
    
    $( document ).on( "localeReady" , function() {
        $('small').prepend(currency);
    });

    $('main').on('click' , '.next:not(.next-disable)' , function(){
        $(this).addClass('next-disable');
        var container = $(this).find('~ .row');
        var leftPosition = container.scrollLeft();
        container.stop( true , true ).animate({scrollLeft: leftPosition + container.width() }, 500).promise().done(function () {
            $('.next-disable').removeClass('next-disable');
        });
    });

    $('main').on('click' , '.previous:not(.previous-disable)' , function(){
        $(this).addClass('previous-disable');
        var container = $(this).find('~ .row');
        var leftPosition = container.scrollLeft();
        container.stop( true , true ).animate({scrollLeft: leftPosition - container.width() }, 500).promise().done(function () {
            $('.previous-disable').removeClass('previous-disable');
        });
    });
</script>