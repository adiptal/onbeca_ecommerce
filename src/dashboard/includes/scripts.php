<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
    var current_url = null;
    var params = null;
    
    function refreshURL()
    {
        current_url = window.location.href;
        params = current_url.substring( "<?php echo $url;?>".length ).split( "/" );
    }

    $.ajaxSetup({
        cache: true,
        timeout: 5000
    });
    
    $(window).on("load", function(){
        $.ready.then(function(){
            $('html').addClass('html-active');
            $.getScript( "https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js" , function(){
                WebFont.load({
                    google: {
                        families: ['Montserrat:300,400,500,600,700,800']
                    }
                });
            });
            $.getScript( <?php echo '"' . $url . '"';?> + "assets/js/sweetalert2.min.js" );
            $.getScript( "https://use.fontawesome.com/releases/v5.6.3/js/all.js" );
        });
    });

    $(document).on('click' , '#menu' , function(){
        $('.sidebar').show();
    });

    $(document).on('click' , '#close' , function(){
        $('.sidebar').hide();
    });

    <?php if( $_SESSION['user_role_id'] == 1 ){ ?>
    getIssuesCount();
    setInterval(getIssuesCount , 60000);
    <?php } ?>

    function getIssuesCount()
    {
        $.ajax({
            method: "POST",
            url: <?php echo '"' . $url . '"';?> + 'functions.php',
            data: 'getIssuesCount',
            dataType: "json"
        }).done(function(response) {
            $('.sidebar li span').html(response);
        });
    }
</script>