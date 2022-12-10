<div class="loader"><div class="spinner"></div></div>
<script>
    $('html,body').css('overflow' , 'auto').addClass('html-active');
    var current_url = null;
    var params = null;
    var currency = null;
    var window_loaded = false;
    var instanceLazyJS = null;
    var refreshGoogleAds;
    
    function refreshURL()
    {
        if( current_url == '' || current_url == null )
        {
            <?php
                if( isset($geoplugin) && $page != 'error' )
                {
                    echo 'window.history.replaceState( {} , "'.$locale_name.'" , "'.$url . $locale_name . '/" );';
                }
            ?>
        }
        current_url = window.location.href;
        params = current_url.substring( '<?php echo $url;?>'.length ).split( '/' );
    }

    $.ajaxSetup({
        cache: true
    });
    
    $(function(){
        gtag('config', 'UA-137006531-1', {'page_path': location.pathname});
    });
    
    $(window).on("load", function(){
        $.ready.then(function(){
            window_loaded = true;
            
            $.getScript( "<?php echo $url;?>assets/js/jquery.nice-select.min.js" , function(){
                $('select').niceSelect();
            });
            $.getScript( "https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js" , function(){
                WebFont.load({
                    google: {
                            families: ['Montserrat:300,400,500,600,700,800']
                    },
                    timeout: 2000,
                    active: function(familyName, fvd) { $('html').addClass('dynamic-font-loaded') }
                });
            });
            $.getScript( '<?php echo $url;?>dashboard/assets/js/jquery.lazy.min.js' , function(){
                $( document ).trigger( "stopLoading" );
            });
            $.getScript( "https://use.fontawesome.com/releases/v5.6.3/js/all.js" );

            document.addEventListener('scroll', function (event) {
                var scroll = $(window).scrollTop();
                if (scroll > 50) {
                    $('body>header').css('padding-top' , $('.topbar').height()).addClass('fixed');
                    if (screen.width < 767) {
                        $('body>header .form-input').hide();
                    }
                } else {
                    $('body>header').css('padding-top' , 0).removeClass('fixed');
                    if (screen.width < 767) {
                        $('body>header .form-input').show();
                    }
                }
            }, true);
            
            manageUserSessions();
            if( !sessionStorage.getItem('cookies-alert') )
            {
                $('body').append(
                    '<div class="cookies row">'+
                    '<div class="col-md-6">'+
                    '<p>We use cookies to improve your website experience</p>'+
                    '</div>'+
                    '<div class="col-md-6">'+
                    '<a href="<?php echo $url;?>'+ params[0] +'/privacy-policy/">Learn More</a>'+
                    '<button id="cookies-dismiss">OK</button>'+
                    '</div>'+
                    '</div>'
                );
                sessionStorage.setItem( 'cookies-alert' , true );
            }
        });
    });
    
    function getLocation()
    {
        let deferred = $.Deferred();

        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'dashboard/functions.php',
            data: 'getLocations',
            dataType: "json"
        }).done(function(response) {
            $('#locale_id').html('');
            for(var i=0 ; i<response.length ; i++)
            {
                if( response[i][1] == params[0] )
                {
                    currency = decodeURIComponent(response[i][2]);
                    $('#locale_id').append('<option selected value="'+ response[i][0] +'">'+ response[i][1] +'</option>');
                }
                else
                {
                    $('#locale_id').append('<option value="'+ response[i][0] +'">'+ response[i][1] +'</option>');
                }
            }
            try {
                $('#locale_id').niceSelect('update');
            } catch (error) {}
            finally{
                $( document ).trigger( "localeReady" );
            }
            
            deferred.resolve();
        });
        
        return deferred.promise();
    }

    function updateLocationChanges()
    {
        $('a:not(.no-refresh-link)').each(function() {
            href = this.href.substring( '<?php echo $url;?>'.length ).split( '/' );
            href[0] = params[0];
            $(this).attr ('href' , '<?php echo $url;?>' + href.join('/') );
        });
        
        getLocation().then(ajaxRedirect);
    }

    $(document).on('change' , '#locale_id' , function(){
        params[0] = $('#locale_id option:selected').html();
        history.pushState( null , null , '<?php echo $url;?>' + params.join('/') );
        updateLocationChanges()
    });

    $(document).on('click' , '[data-url]' , function(){
        try {
            var trim = ( "<?php echo $url;?>" + params[0] + '/' ).length;
            product_url = $(this).closest('[class^=col]').find('a').attr('href').substring( trim ).split('/')[0];
            $.ajax({
                method: "POST",
                url: <?php echo '"' . $url . '"';?> + 'dashboard/functions.php',
                data: 'addClickCount&product_url=' + product_url + '&locale_name=' + params[0]
            });
            gtag('event', 'conversion', {'send_to': 'AW-752424154/P2U6CMfetJgBENqp5OYC' });
        } catch (error) {}
        finally{
            window.open( $(this).data('url') , '_blank' );
        }
    });
    
    $(window).on('resize', function(){
        $('[data-url]').css({
            'width' : $('[data-url]').closest('[class^="col"]').find('.card').outerWidth()
        });
    });
    
    $( document ).on( "searchOptionsReady" , function( event , page ) {
        if( page == 'container-listing' )
        {
            if( $('#department option:contains('+ params[1] +')').length )
            {
                $('#department').val(decodeURIComponent(params[1]));
            }
        }
        else if( page == 'search' )
        {
            $('#search').val(decodeURIComponent(params[3]));
            if( $('#department option:contains('+ params[1] +')').length || $('#department option:contains("All")').length )
            {
                $('#department').val(decodeURIComponent(params[2]));
            }
        }
        else if( page == 'products' )
        {
            $('#department').val(decodeURIComponent($('.breadcrump ul li:nth-of-type(2) a').html()));
        }
        else
        {
            $('#department').val('All');
        }
        
        try { $('#department').niceSelect('update'); } catch (error) {}
    });
    
    $( document ).on( "stopLoading" , function() {
        if( window_loaded && ( instanceLazyJS == null || instanceLazyJS == '' ) )
        {
            registerLazyJS();
            $('.lazy').hide().parent().prepend('<span class="img-loader"></span>');
            $('.loader').remove();

            if( $('.container-listing').length != 0 )
            {
                if ( $(window).width() < 767 )
                {
                    $('body').css('overflow' , 'auto');
                    $('.products').fadeIn( 500 );
                }
                else
                {
                    $('.sidebar , .products').fadeIn( 500 );
                }
                $('[data-url]').css({
                    'width' : $('[data-url]').closest('[class^="col"]').find('.card').outerWidth()
                });
            }
            
            loadGoogleAds();
        }
    });

    function registerLazyJS()
    {
        instanceLazyJS = $('.lazy').lazy({
            bind: "event",
            afterLoad: function(element) {
                element.removeClass('lazy').show().parent().find('.img-loader').remove();
            }
        });
    }

    $(document).on('click' , '#search-btn' , function(){
        if( $('#search').val() != '' )
        {
            history.pushState( null , null , '<?php echo $url;?>'+ params[0] +'/search/' + encodeURIComponent($('#department').val()) + '/' + $('#search').val() + '/' );
            ajaxRedirect();
        }
    });
    
    $(document).on('keyup' , '#search' , function(event){
        if( event.keyCode == 13 )
        {
            $('#search-btn').click();
        }
    });

    $(document).on('click' , '#cookies-dismiss' , function(){
        $(this).closest('.cookies').fadeOut(250).promise().done(function(){
            $(this).remove();
        });
    });

    $(document).on('click' , '#dismiss-data' , function(){
        $(this).parent().parent().find('ins').slideUp(250);
    });

    $(document).on('click' , '.search-form' , function(){
        $('body>header.fixed .topbar .form-input').toggle();
    });

    $(document).on('click' , 'a:not(.link)' , function(){
        var href =  $(this).attr('href');
        event.preventDefault();
        event.stopPropagation();
        
        history.pushState( null , null , href );
        ajaxRedirect();
    });
    
    window.onpopstate = function(event) {
        if( params[0] != window.location.href.substring( '<?php echo $url;?>'.length ).split( '/' )[0] )
        {
            refreshURL();
            updateLocationChanges();
        }
        else
        {
            ajaxRedirect();
        }
    };

    refreshURL();
    getLocation();

    function manageUserSessions()
    {
        setInterval(function(){
            $.ajax({
                method: "POST",
                url: <?php echo '"' . $url . '"';?> + 'includes/ajax.php',
                data: 'checkSessionActive',
                timeout: 2500
            }).done(function(response) {
                if( response == true && $('.topbar>ul>li:nth-child(2) a').length == 0 )
                {
                    $( '<li><a class="link no-refresh-link" title="Logout" href="<?php echo $url;?>dashboard/logout/"><i class="fas fa-sign-out-alt"></i></a></li>' ).insertAfter( '.topbar>ul>li.search-form' );
                    ajaxRedirect();
                }
                else if( response == false && $('.topbar>ul>li:nth-child(2) a').length == 1 )
                {
                    $('.topbar>ul>li .link.no-refresh-link').parent().remove();
                    ajaxRedirect();
                }
            });
        } , 5000);
    }
    
    $(document).on('keydown' , function(event){
        if ( event.keyCode == 116 || ( event.ctrlKey && event.keyCode == 82 ) )
        {
            event.preventDefault();
            event.stopPropagation();
            
            ajaxRedirect();
        }
    });

    $ajaxPageRedirect = null;
    function ajaxRedirect()
    {
        if( $.type( $ajaxPageRedirect ) != "null" )
        {
            $ajaxPageRedirect.abort();
        }

        if( $('.loader').length == 0 )
        {
            $('body').append('<div class="loader"><div class="spinner"></div></div>');
        }
        $('html,body').animate({ scrollTop: 0 }, 150);
        
        refreshURL();

        instanceLazyJS = null;
        $(document).off('localeReady');
        $('main').off();
        
        $(function(){
            gtag('config', 'UA-137006531-1', {'page_path': location.pathname});
            gtag('config', 'AW-752424154');
        });

        $ajaxPageRedirect = $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'includes/ajax.php',
            data: 'getPage=' + current_url,
            timeout: 3000
        }).fail(function(response , error) {
            if( error == 'timeout' )
            {
                ajaxRedirect();
            }
            else
            {
                $('main').html(response['responseText']);
            }
        }).done(function(response) {
            $('head>title').html($($(response)[$(response).length - 1]).html());
            $('main').html(response).find('title').remove();
            $( document ).trigger( "localeReady" );
        });
    }

    function loadGoogleAds()
    {
        $('main>aside').remove();
        $('main').prepend(
            '<aside>'+
            '<header>'+
            '<h2>Google Ads .</h2>'+
            '<button id="dismiss-data"><i class="fas fa-minus"></i></button>'+
            '</header>'+
            '<ins><div class="spinner"></div></ins>'+
            '</aside>'
        );

        setTimeout(fetchGoogleAds , 2500);
        clearInterval(refreshGoogleAds);
        refreshGoogleAds = setInterval(fetchGoogleAds , 60000);
    }

    function fetchGoogleAds()
    {
        $('main>aside ins').remove();
        $.getScript( "//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js" , function(){
            $('main>aside').append(
                '<ins class="adsbygoogle"'+
                'style="display:block"'+
                'data-ad-format="fluid"'+
                'data-ad-layout-key="-gw-3+1f-3d+2z"'+
                'data-ad-client="ca-pub-1896388126591048"'+
                'data-ad-slot="9565757159"></ins>'
            );
            (adsbygoogle = window.adsbygoogle || []).push({});

            $('main>aside').append(
                '<ins class="adsbygoogle"'+
                'style="display:block"'+
                'data-ad-format="fluid"'+
                'data-ad-layout-key="-gw-3+1f-3d+2z"'+
                'data-ad-client="ca-pub-1896388126591048"'+
                'data-ad-slot="3229803696"></ins>'
            );
            (adsbygoogle = window.adsbygoogle || []).push({});
        });
    }
</script>