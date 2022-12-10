<?php
    require_once __DIR__ . '/../../dashboard/classes/ContentListing.php';
    $ContentListing = new ContentListing();

    $param_sql = [ $url_params[0] , $param_table . '_id' , $param_table . '_name' ];

    $productsFetchJSON = json_decode( $ContentListing->createContentListing( $param_table , $param_sql , $locale_name , null , urldecode( $url_request[3] ) , $offset , 12 ) , true );

    $sidebarFetchJSON = json_decode( $ContentListing->createSearchFilter( $url_params[0] , urldecode( $url_request[3] ) , $url_request[0] ) , true );

    if( !function_exists( 'getEncodedURL' ) )
    {
        function getEncodedURL( $exploded_url )
        {
            $exploded_url = explode( '/' , $exploded_url );
            foreach ( $exploded_url as $key => $value )
            {
                $exploded_url[$key] = preg_replace("/[ ]/", '%20' , $value );
            }
            return implode( '/' , $exploded_url );
        }
    }

    function renderSidebarJSON()
    {
        global $url;
        global $locale_name;

        global $sidebarFetchJSON;


        echo '<button class="d-mob times" aria-labelledby="close" id="close"><span>Close</span><i class="fas fa-times"></i></button><header class="card"><h2>Filter</h2></header>';

        echo '<nav class="card">';
        foreach ($sidebarFetchJSON as $deptkey => $deptvalue)
        {
            $deptkey_empty = true;
            foreach ($deptvalue as $key => $value)
            {
                if( !empty( $value ) )
                {
                    if( $deptkey_empty )
                    {
                        echo '<header><h3>'. $deptkey .'</h3></header>';
                    }
                    $deptkey_empty = false;
                    $key = preg_replace( '/[-]/' , ' ' , $key );
                    $encodedURL = $url . getEncodedURL( $locale_name . '/' . $deptkey . '/' . $key . '/1/' );
                    
                    echo '<ul><li><a href="'. $encodedURL .'" title="' . $key . '">' . $key . '</a><ul>';

                    for( $i = 0 ; $i < sizeof( $value ) ; $i++ )
                    {
                        if( !preg_match( "/Filter/" , $value[$i] )  )
                        {
                            $encodedURL = $url . getEncodedURL( $locale_name . '/' . $deptkey . '/' . $key . '/' . $value[$i] . '/1/' );

                            echo '<li><a href="'. $encodedURL .'" title="' . $value[$i] . '">'. $value[$i] .'</a></li>';
                        }
                    }

                    echo '</ul></li></ul>';
                }
            }
        }
        echo '</nav>';
    }
    
    function renderProductJSON()
    {
        global $url;
        global $locale_name;
        
        global $productsFetchJSON;


        echo '<div class="col-12"><button class="d-mob" aria-labelledby="filter" id="filter"><span>Menu</span><i class="fas fa-filter"></i></button></div>';
        
        if( isset( $productsFetchJSON['products'] ) && !empty( $productsFetchJSON['products'] ) )
        {
            for( $i = 0 ; $i < sizeof( $productsFetchJSON['products'] ) ; $i++ )
            {
                if( !preg_match( '/^(http:|https:)\/\//' , $productsFetchJSON['products'][$i][3] ) )
                {
                    $regex_image = $url .'dashboard/includes/blogimages/' . $productsFetchJSON['products'][$i][3];
                }
                else
                {
                    $regex_image = $productsFetchJSON['products'][$i][3];
                }
                
                echo '<div class="col-sm-6 col-xl-4"><a href="' . $url . $locale_name . '/' . $productsFetchJSON['products'][$i][2] . '/" title="'.$productsFetchJSON['products'][$i][1].'" class="card"><img src="data:image/png;base64," class="lazy" data-src="' . $regex_image .'" alt="'.$productsFetchJSON['products'][$i][1].'"><span>'.$productsFetchJSON['products'][$i][1].'</span></a>';

                if( isset( $productsFetchJSON['products'][$i][4] ) && !empty( $productsFetchJSON['products'][$i][4] ) )
                {
                    echo '<button class="external" data-url="'.$productsFetchJSON['products'][$i][4][4].'"><i class="fas fa-external-link-alt"></i> '.$productsFetchJSON['products'][$i][4][1].'</button><small>'. $productsFetchJSON['products'][$i][4][3] .'</small>';
                }

                echo '</div>';
            }
        }
        else
        {
            echo '<div class="col-12"><div class="card"><h2>No Products</h2></div></div>';
        }
    }
?>

<section class="row container-listing">
    <div class="col-md-3 sidebar"><?php renderSidebarJSON();?></div>
    <div class="col-md-9"><div class="products row"><?php renderProductJSON(); ?></div></div>
</section>

<script>
    $( document ).on( "localeReady" , function() {
        $('small').prepend(currency);
        $( document ).trigger( "searchOptionsReady" , 'search' );
        $( document ).trigger( "stopLoading" );
        
        <?php
            // CREATE PAGINATION
            echo "createPagination( ". $productsFetchJSON[0] ." , ". $productsFetchJSON[1] ." , '$param_table' , $offset );";
        ?>
    });
    
    $('main').on('click' , '#filter' , function(){
        $('.sidebar').show();
        $('body').css({
            'overflow' : 'hidden'
        });
    });

    $('main').on('click' , '#close' , function(){
        $('.sidebar').hide();
        $('body').css({
            'overflow' : 'auto'
        });
    });

    function createPagination( pagination_offset , total_pagination_count , param_table , offset )
    {
        $('.products').append('<div class="col-12 pagination" id="pagination"></div>');
        if( total_pagination_count != 0 )
        {
            // FIRST
            $('#pagination').append('<button onclick="managePagination('+ "'" + param_table + "'" +' , 1 )" class="btn"><i class="fas fa-angle-double-left"></i></button>');
            // PREVIOUS
            $('#pagination').append('<button onclick="managePagination('+ "'" + param_table + "'" +' ,' + (offset-1) +')" class="btn"><i class="fas fa-angle-left"></i></button>');
        }
        for(var j=total_pagination_count-4 ; j < total_pagination_count+11 ; j++)
        {
            if( j > 0 && j < 11 && j < pagination_offset )
            {
                $('#pagination').append('<button onclick="managePagination('+ "'" + param_table + "'" +' ,' +  j +')" class="btn">'+ j +'</button>');
            }
            else if( j > 0 && j <= pagination_offset && ( j < total_pagination_count-3 || j < total_pagination_count+6 ) )
            {
                $('#pagination').append('<button onclick="managePagination('+ "'" + param_table + "'" +' ,' +  j +')" class="btn">'+ j +'</button>');
            }
        }
        if( total_pagination_count+1 < pagination_offset )
        {
            // NEXT PAGE
            $('#pagination').append('<button onclick="managePagination('+ "'" + param_table + "'" +' ,' +  (total_pagination_count+2) +')" class="btn"><i class="fas fa-angle-right"></i></button>');
            // LAST PAGE
            $('#pagination').append('<button onclick="managePagination('+ "'" + param_table + "'" +' ,' +  pagination_offset +')" class="btn"><i class="fas fa-angle-double-right"></i></button>');
        }
        $('.products #pagination button:contains('+ offset +')').addClass('active');
    }

    function managePagination( param_table , offset )
    {
        params[4] = offset;
        history.pushState( null , null , "<?php echo $url;?>" + params.join('/') );
        
        refreshURL();
        getContentListing( params[3] );
    }

    function getContentListing( search = '' , limit = 12 )
    {
        instanceLazyJS = null;
        $(function(){
            gtag('config', 'UA-137006531-1', {'page_path': location.pathname});
        });
        $('html,body').animate({ scrollTop: 0 }, 150);
        
        var ajax_params = [ 'department' , params[2] , params[4] , params[5] ];

        $.each( ajax_params, function( key, value ) {
            if( value === undefined ||  value === '' )
            {
                ajax_params[key] = 0;
            }
        });

        var param_table = ajax_params[0];
        var section = ajax_params[1];
        var offset = ajax_params[2];
        var filter = ajax_params[3];
        var locale_name = params[0];
        var param_sql = [decodeURIComponent(section) , param_table + '_id' , param_table + '_name'];

        // PRODUCT
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'dashboard/functions.php',
            data: 'createContentListing'
            +'&param_table='+ param_table
            +'&param_sql='+ JSON.stringify(param_sql)
            +'&locale_name='+ locale_name
            +'&filter='+ filter
            +'&search='+ search
            +'&offset='+ offset
            +'&limit='+ limit ,
            // dataType: "json"
        }).done(function(response) {
            $('.products').html('<div class="col-12"><button class="d-mob" aria-labelledby="filter" id="filter"><span>Menu</span><i class="fas fa-filter"></i></button></div>');
            if( response === false || !response.hasOwnProperty( 'products' ) || response['products'].length <= 0 )
            {
                $('.products').append(
                    '<div class="col-12">'+
                    '<div class="card">'+
                    '<h2>No Products</h2>'+
                    '</div>'+
                    '</div>'
                );
            }
            else
            {
                var products = response['products'];
                for( var i=0 ; i<products.length ; i++ )
                {
                    if( !(/^(http:|https:)\/\//).test( products[i][3] ) )
                    {
                        regex_image = '<?php echo $url;?>dashboard/includes/blogimages/' + products[i][3];
                    }
                    else
                    {
                        regex_image = products[i][3];
                    }

                    $('.products').append(
                        '<div class="col-sm-6 col-xl-4">'+
                        '<a href="<?php echo $url;?>' + locale_name + '/' + products[i][2] + '/" title="'+ products[i][1] +'" class="card">'+
                        '<img src="data:image/png;base64," class="lazy" data-src="'+ regex_image +'" alt="'+products[i][1]+'">'+
                        '<span>'+products[i][1]+'</span>'+
                        '</a>'+
                        '</div>'
                    );

                    if( products[i].hasOwnProperty(4) && products[i][4][4] != null && products[i][4][4] != '' )
                    {
                        $('.products>div:last').append(
                            '<button class="external" data-url="'+products[i][4][4]+'"><i class="fas fa-external-link-alt"></i> '+products[i][4][1]+'</button><small>'+ currency + products[i][4][3] +'</small>'
                        );
                    }
                }
                
                $('[data-url]').css({
                    'width' : $('[data-url]').closest('[class^="col"]').find('.card').outerWidth()
                });

                // PAGINATION
                createPagination( response[0] , response[1] , param_table , offset );
            }
            $( document ).trigger( "stopLoading" );
        });
    }
</script>