<?php
    require_once __DIR__ . '/../../dashboard/classes/ContentListing.php';
    $ContentListing = new ContentListing();

    $param_sql = [ $url_params[0] , $param_table . '_id' , $param_table . '_name' ];

    $productsFetchJSON = json_decode( $ContentListing->createContentListing( $param_table , $param_sql , $locale_name , $filter , null , $offset , 12 ) , true );

    $sidebarFetchJSON = json_decode( $ContentListing->createFilterListing( $param_table , $url_params[0] ) , true );

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
        global $url_request;

        global $url_params;
        global $param_table;
        global $sidebarFetchJSON;

        
        echo '<button class="d-mob times" aria-labelledby="close" id="close"><span>Close</span><i class="fas fa-times"></i></button><header class="card"><h2>Filter</h2></header>';

        if( isset( $sidebarFetchJSON['url_links'] ) && !empty( $sidebarFetchJSON['url_links'] ) )
        {
            echo '<nav class="card"><ul>';

            switch( $param_table )
            {
                case 'department' :
                    echo '<li><button class="active">' . $sidebarFetchJSON['url_links']['current'][0] . '</button><ul>';

                    $child_url = $sidebarFetchJSON['url_links']['child'];
                    for( $i = 0 ; $i < sizeof( $child_url ) ; $i++ )
                    {
                        $encodedURL = $url . getEncodedURL( $locale_name . '/' . $url_params[0] . '/' . $child_url[$i] . '/1/' );

                        echo '<li><a href="'. $encodedURL .'" title="' . $child_url[$i] . '">' . $child_url[$i] . '</a></li>';
                    }

                    echo '</ul></li>';
                break;

                case 'category' :
                    $encodedURL = $url . getEncodedURL( $locale_name . '/' . $url_request[1] . '/1/' );
                    echo '<li><a class="back" href="'. $encodedURL .'" title="' . urldecode( $url_request[1] ) . '">' . urldecode( $url_request[1] ) . '</a></li>';

                    echo '<li><button class="active" >' . $sidebarFetchJSON['url_links']['current'][0] . '</button><ul>';

                    $child_url = $sidebarFetchJSON['url_links']['child'];
                    for( $i = 0 ; $i < sizeof( $child_url ) ; $i++ )
                    {
                        $encodedURL = $url . getEncodedURL( $locale_name . '/' . $url_request[1] . '/' . $url_params[0] . '/' . $child_url[$i] . '/1/' );

                        echo '<li><a href="'. $encodedURL .'" title="' . $child_url[$i] . '">' . $child_url[$i] . '</a></li>';
                    }
                    
                    echo '</ul></li>';
                break;
            }

            echo '</ul></nav>';
        }

        if( $param_table == 'sub_category' || ( isset( $sidebarFetchJSON[0] ) && $sidebarFetchJSON[0] == 'sub_category' ) )
        {
            echo '<nav class="card"><ul>';
            
            $encodedURL = $url . getEncodedURL( $locale_name . '/' . $url_request[1] . '/1/' );
            echo '<li><a class="back" href="'. $encodedURL .'" title="' . urldecode( $url_request[1] ) . '">' . urldecode( $url_request[1] ) . '</a></li>';

            if( isset( $sidebarFetchJSON[0] ) && $sidebarFetchJSON[0] == 'sub_category' )
            {
                // IF DIRECT CATEGORY FILTER
                $sidebarFetchJSON = $sidebarFetchJSON[1];
            }
            else
            {
                // IF NOT
                $encodedURL = $url . getEncodedURL( $locale_name . '/' . $url_request[1] . '/' . $url_request[2] . '/1/' );
                echo '<li><a class="back" href="'. $encodedURL .'" title="' . urldecode( $url_request[2] ) . '">' . urldecode( $url_request[2] ) . '</a></li>';
            }
            
            echo '<li><button class="active">' . urldecode( $url_params[0] ) . '</button></li>';

            echo '</ul></nav>';

            foreach ( $sidebarFetchJSON as $key => $value )
            {
                echo '<nav class="card" id='. $key .'><header><h3>'. preg_replace("/[-]/", ' ' , $key ) .'</h3></header><ul>';

                for( $i = 0 ; $i < sizeof( $value ) ; $i++ )
                {
                    echo '<li><label><input type="checkbox" name="filter[]" value="'. $i .'">'. preg_replace("/[-]/", ' ' , $value[$i] ) .'</label></li>';
                }

                echo'</ul></nav>';
            }
        }
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
        $( document ).trigger( "searchOptionsReady" , 'container-listing' );
        $( document ).trigger( "stopLoading" );
        
        <?php
            // CREATE PAGINATION
            echo "createPagination( ". $productsFetchJSON[0] ." , ". $productsFetchJSON[1] ." , '$param_table' , $offset );";

            if( isset( $filter ) && !empty( $filter ) )
            {
                echo 'getRefreshCheckBox( '. json_encode( $filter ) .' )';
            }
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
        switch( param_table )
        {
            case 'department' : 
                params[2] = offset;
            break;

            case 'category' : 
                params[3] = offset;
            break;

            case 'sub_category' : 
                params[4] = offset;
            break;
        }
        history.pushState( null , null , "<?php echo $url;?>" + params.join('/') );
        
        refreshURL();
        getContentListing();
    }

    function getParamArray()
    {
        switch( true )
        {
            case (params.length < 6 && /^<?php echo $reg_url;?>(\S[^\/]+)\/(\S[^\/]+)(\/\d+(\/(\S[^\/]+))?)?((\/$)|$)/.test(String(current_url).toLowerCase()) ? true : false) :
                return [ 'department' , params[1] , params[2] , params[3] ];
            break;

            case (params.length < 7 && /^<?php echo $reg_url;?>(\S[^\/]+)\/(\S[^\/]+)\/(\S[^\/]+)(\/\d+(\/(\S[^\/]+))?)?((\/$)|$)/.test(String(current_url).toLowerCase()) ? true : false) :
                return [ 'category' , params[2] , params[3] , params[4] ];
            break;
            
            case (params.length < 8 && /^<?php echo $reg_url;?>(\S[^\/]+)\/(\S[^\/]+)\/(\S[^\/]+)\/(\S[^\/]+)(\/\d+(\/(\S[^\/]+))?)?((\/$)|$)/.test(String(current_url).toLowerCase()) ? true : false) :
                return [ 'sub_category' , params[3] , params[4] , params[5] ];
            break;
        }
    }

    function getRefreshCheckBox( filter )
    {
        $.each( JSON.parse( decodeURIComponent( filter ) ) , function( key, values ){
            if( $('.sidebar .card#'+ key ).length == 1 )
            {
                values.forEach(value => {
                    $('.sidebar .card#'+ key ).closest('.card').find('input[value="'+ value +'"]').prop('checked', true);
                    $('.sidebar .card#'+ key ).closest('.card').find('input[value="'+ value +'"]').parent().addClass('active');
                });
            }
        });
    }

    function getContentListing( search = '' , limit = 12 )
    {
        instanceLazyJS = null;
        $(function(){
            gtag('config', 'UA-137006531-1', {'page_path': location.pathname});
        });
        $('html,body').animate({ scrollTop: 0 }, 150);
        
        var ajax_params = getParamArray();

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
                if( param_table == 'department' )
                {
                    window.location.replace("<?php echo $url;?>"+ locale_name +"/404/");
                }
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
    
    $('main').on('change' , '.sidebar .card ul input' , function(){
        if( $(this).prop('checked') )
        {
            $(this).parent().addClass('active');
        }
        else
        {
            $(this).parent().removeClass('active');
        }
        
        var filter = {};
        $('.sidebar .card ul input:checked').each(function(){
            var key = $(this).closest('.card').attr('id');
            if( !filter.hasOwnProperty( key )  )
            {
                filter[ key ] = [];
            }
            if( jQuery.inArray( $(this).val() , filter ) )
            {
                filter[ key ].push( $(this).val() );
            }
        });
        
        var param_table = getParamArray()[0];
        var locale_name = params[0];
        var offset = 1;
        var url = '';
        var filter = encodeURIComponent(JSON.stringify(filter));

        switch( param_table )
        {
            case 'department' : url = locale_name + '/' + params[1] + '/' + offset + '/' + filter + '/';
            break;

            case 'category' : url = locale_name + '/' + params[1] + '/' + params[2] + '/' + offset + '/' + filter + '/';
            break;

            case 'sub_category' : url = locale_name + '/' + params[1] +  '/' + params[2] + '/' + params[3] + '/' + offset + '/' + filter + '/';
            break;
        }

        history.pushState( null , null , "<?php echo $url;?>" + url );
        refreshURL();
        getContentListing();
    });
</script>