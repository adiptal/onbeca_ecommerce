<?php
    require_once __DIR__ . '/../../dashboard/classes/ContentDetail.php';
    require_once __DIR__ . '/../../dashboard/classes/TrackerFetcher.php';
    $ContentDetail = new ContentDetail();
    $TrackerFetcher = new TrackerFetcher();

    $link_tracker_ids = [];

    $productDetail = json_decode( $ContentDetail->createProductDetails( $url_request[1] , $url_request[0] ) , true );
?>

<script>
    var timeout;
    var affiliate_ids = [];
    var affiliate_company = [];
    var link_tracker_company = [];
</script>

<section class="row container-details">
    <p class="col-12 warning">Product Price may differ a bit from Billing Price depending on Payment Methods Or Currencies. Report Misleading Information with Page Link via Contact Us Page.</p>

    
    <div class="breadcrump col-12"><ul>
        <?php
            // BREADCRUMP
            if( isset( $productDetail['breadcrump'] ) && !empty( $productDetail['breadcrump'] ) )
            {
        ?>
        <script>
            response = [];
            response['breadcrump'] = <?php echo json_encode( $productDetail['breadcrump'] );?>;
            if( response.hasOwnProperty('breadcrump') )
            {
                response['breadcrump'][0] = encodeURIComponent( response['breadcrump'][0] );
                response['breadcrump'][1] = encodeURIComponent( response['breadcrump'][1] );
                response['breadcrump'][2] = encodeURIComponent( response['breadcrump'][2] );
                
                $('.breadcrump ul').append('<li><a href="<?php echo $url;?>'+ params[0] +'/" title="<?php echo $site_name?>">Home</a></li>');
                $('.breadcrump ul').append('<li><a href="<?php echo $url;?>'+ params[0] +'/'+ response['breadcrump'][0] +'/1/" title="'+ decodeURIComponent( response['breadcrump'][0] ) +'">'+ decodeURIComponent( response['breadcrump'][0] ) +'</a></li>');
                $('.breadcrump ul').append('<li><a href="<?php echo $url;?>'+ params[0] +'/'+ response['breadcrump'][0] +'/'+ response['breadcrump'][1] +'/1/" title="'+ decodeURIComponent( response['breadcrump'][1] ) +'">'+ decodeURIComponent( response['breadcrump'][1] ) +'</a></li>');

                if( !response['breadcrump'][2].match(/Filter/) )
                {
                    $('.breadcrump ul').append('<li><a href="<?php echo $url;?>'+ params[0] +'/'+ response['breadcrump'][0] +'/'+ response['breadcrump'][1] +'/'+ response['breadcrump'][2] +'/1/" title="'+ decodeURIComponent( response['breadcrump'][2] ) +'">'+ decodeURIComponent( response['breadcrump'][2] ) +'</a></li>');
                }
                
                $( document ).trigger( "searchOptionsReady" , 'products' );
            }
        </script>
        <?php
            }
        ?>
    </ul></div>

    
    <div class="product">
    <?php
        // PRODUCT NAME
        echo'<header class="col-12"><h2>'. $productDetail[0] .'</h2></header>';

        if( !preg_match( '/^(http:|https:)\/\//' , $productDetail[1] ) )
        {
            $regex_image = $url .'dashboard/includes/blogimages/'. $productDetail[1];
        }
        else
        {
            $regex_image = $productDetail[1];
        }
        
        // PRODUCT IMAGE
        echo '<div class="col-12"><img class="col-10 offset-1 col-md-6 offset-md-3 lazy" src="data:image/png;base64," data-src="'. $regex_image .'" alt="'. $productDetail[0] .'"></div>';


        // AFFILIATE
        if( isset( $productDetail['affiliate'] ) && !empty( $productDetail['affiliate'] ) )
        {
            echo '<div class="affiliate"><div class="table"><table><thead><tr><th>Company</th><th>Condition</th><th>Price</th></tr></thead><tbody>';

            for( $i = 0 ; $i < sizeof( $productDetail['affiliate'] ) ; $i++ )
            {
                echo'<tr><td>'. $productDetail['affiliate'][$i][1] .'</td><td>'. $productDetail['affiliate'][$i][2] .'</td><td><button data-url="'. $productDetail['affiliate'][$i][4] .'">' . $productDetail['affiliate'][$i][3] .'</button></td></tr>';

                array_push( $link_tracker_ids , $productDetail['affiliate'][$i][5] );
                echo '
                <script>
                    affiliate_ids.push( '. $productDetail['affiliate'][$i][0] .' );

                    affiliate_company[ '. $productDetail['affiliate'][$i][0] .' ] = "'. $productDetail['affiliate'][$i][1].'";

                    link_tracker_company[ '. $productDetail['affiliate'][$i][5] .'] = "'. $productDetail['affiliate'][$i][1].'";
                </script>';
            }

            echo '</tbody></table></div></div>';
        }


        // PRICE ALERT
        echo '<div class="price-alert"><header><h3>Price Alert</h3></header>';
        if( isset( $_SESSION['user_id'] ) && !empty( $_SESSION['user_id'] ) )
        {
            echo '<div class="row"><div class="active-price-alerts"></div><div class="col-md-4 form-input"><select id="affiliate_id"></select></div><div class="col-md-4 form-input"><input id="alert_price" type="text" required=""><label for="alert_price">Alert Price</label></div><div class="col-md-4 form-input"><button id="alert-btn">SAVE</button></div></div>';
        }
        else
        {
            echo '<p><a class="price-alt-link link" title="Register" href="'. $url .'account/signUp/' .'">Register</a>&emsp;OR&emsp;<a class="price-alt-link link" title="Login" href="'. $url .'account/signIn/' .'">Login</a></p>';
        }
        echo '</div>';


        // PRICE HISTORY
        echo'<div class="price-history"><header><h3>Price History</h3></header><div id="chart_div"></div></div>';
        $productPricingHistory = $TrackerFetcher->getProductPricingHistory( implode( ',' , $link_tracker_ids ) );

        
        // PRODUCT ARTICLE
        if( isset( $productDetail[3] ) && !empty( $productDetail[3] ) )
        {
            echo preg_replace( '/url::((https?|http|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])::([\w ]+)::end/mi' , "<a class='link' href='$1' title='$3' target='_blank' rel='noopener noreferrer'>$3</a>" , $productDetail[3] );
        }
    ?>
    </div>
</section>

<script>
    $( document ).on( "localeReady" , function() {
        $('.price-alt-link').each(function( index ) {
            $(this).attr('href' , $(this).attr('href') + '#' + encodeURIComponent( current_url ) );
        });
        $('tbody td:nth-child(3) button').prepend(currency);
        
        // PRODUCT PRICING HISTORY
        var chartData = <?php echo $productPricingHistory;?>;
        $.getScript( "https://www.gstatic.com/charts/loader.js" , function(){
            if( chartData[0].length > 0 )
            {
                function drawChart() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Date');

                    for( var i = 0 ; i < chartData[0].length ; i++ )
                    {
                        data.addColumn( 'number' , link_tracker_company[ chartData[0][i] ] );
                    }

                    for( var i = 0 ; i < chartData['chart'].length ; i++ )
                    {
                        data.addRow(chartData['chart'][i]);
                    }

                    var options = {
                        focusTarget: 'category',
                        hAxis: {
                            title: 'Date',
                            textStyle: {
                                fontSize: 'inherit'
                            }
                        },
                        vAxis: {
                            title: 'Price ( Lower is better )',
                            textStyle: {
                                fontSize: 'inherit'
                            }
                        },
                        legend: {
                            position: 'top',
                            textStyle: {
                                fontSize: 'inherit'
                            }
                        },
                        tooltip: {
                            isHtml: true
                        }
                    };

                    var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_div'));
                    chart.draw(data, options);
                }
            }
            else
            {
                function drawChart() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Date');
                    data.addColumn( 'number' , 'No Data' );
                    
                    data.addRows([ [ 'No Data' , 0 ] ]);

                    var options = {
                        legend: {
                            position: 'top',
                            textStyle: {
                                fontSize: 'inherit'
                            }
                        },
                        tooltip: {
                            isHtml: true
                        }
                    };

                    var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_div'));
                    chart.draw(data, options);
                }
            }
            
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawChart);
        });
        $( document ).trigger( "stopLoading" );
    });

    $('main').on( 'click' , '#clear' , function(){
        $('#alert_price').val('');
        $('#affiliate_id').val('').niceSelect('update');
        $('#clear').remove();
    });

    $('main').on( 'click' , '#alert-btn' , function(){
        $('.price-alert .form-input>span').remove();
        clearTimeout(timeout);

        var regex = /^[0-9]+(\.[0-9]{1,2})?$/;
        if( $('#affiliate_id').val() != '' && $('#affiliate_id').val() != null && regex.test( String( $('#alert_price').val() ) ) )
        {
            $.ajax({
                method: "POST",
                url: <?php echo '"' . $url . '"';?> + 'dashboard/functions.php',
                data: 'savePriceAlert&price_alert_id'
                +'&affiliate_id='+ $('#affiliate_id').val()
                +'&alert_price='+ encodeURIComponent($('#alert_price').val()),
                dataType: "json"
            }).done(function(response) {
                getPriceAlerts( affiliate_ids );
                $('<span>'+ response[0] +'</span>').insertBefore( $('#alert-btn') );
                timeout = setTimeout( function(){
                    $('.price-alert .form-input>span').remove();
                } , 5000);
            });
            
            $('#clear').click();
        }
        else
        {
            $('<span>Fill All Valid Inputs</span>').insertBefore( $('#alert-btn') );
            timeout = setTimeout( function(){
                $('.price-alert .form-input>span').remove();
            } , 5000);
        }
    });

    var active_price_alert = [];
    function getPriceAlerts( affiliate_ids )
    {
        $('.active-price-alerts').html('');
        active_price_alert = [];
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'dashboard/functions.php',
            data: 'getPriceAlerts'
            +'&affiliate_ids='+ affiliate_ids,
            dataType: "json"
        }).done(function(response) {
            active_price_alert = response;
            if( active_price_alert != false && active_price_alert != '' )
            {
                refreshActivePriceAlerts();
            }
        });
    }

    function refreshActivePriceAlerts()
    {
        $.each( active_price_alert , function( index , value ){
            $('.active-price-alerts').append('<div class="active-box"><button class="alert-edit" onclick="editData('+ value[0] +')"><span><i class="fas fa-edit"></i></span>&nbsp;'+ affiliate_company[value[1]] +'</button><button class="alert-delete" onclick="deleteData('+ value[0] +')"><i class="fas fa-times"></i></button></div>');
        });
    }

    function deleteData( price_alert_id )
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'dashboard/functions.php',
            data: 'deletePriceAlert'
            +'&price_alert_id='+ price_alert_id,
            dataType: "json"
        }).done(function(response) {
            getPriceAlerts( affiliate_ids );
        });
    }

    function editData( price_alert_id )
    {
        $('#clear').remove();
        $.each( active_price_alert , function( index , value ){
            if( value[0] == price_alert_id )
            {
                $('#alert_price').val(value[2]);
                $('#affiliate_id').val(value[1]).niceSelect('update');
                $('<button id="clear">Clear</button>').insertBefore( $('#alert-btn') );
            }
        });
    }

    $('main').on( 'change' , '#affiliate_id' , function(){
        $('#clear').remove();
        $('#alert_price').val('');
        
        affiliate = $(this).val();
        $.each( active_price_alert , function( index , value ){
            if( value[1] == affiliate )
            {
                $('#alert_price').val(value[2]);
                $('<button id="clear">Clear</button>').insertBefore( $('#alert-btn') );
            }
        });
    });
    
    $('.container-details .product article>header').remove();
    if( $('#affiliate_id').length != 0 )
    {
        try {
            getPriceAlerts( affiliate_ids );
            $('#affiliate_id').append('<option value="" selected disabled>Company</option>');
            $.each( affiliate_company , function( index , value ) {
                if( value != '' && value != undefined )
                {
                    $('#affiliate_id').append('<option value="'+ index +'">'+ value +'</option>');
                }
            });
            
            $('#affiliate_id').niceSelect();
        }
        catch (error){}
    }
</script>