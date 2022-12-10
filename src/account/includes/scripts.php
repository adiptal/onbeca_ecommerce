<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script>var baseUrl = <?php echo '"' . $url . '"';?>;</script>
<script src="<?php echo $url;?>assets/ajax/pages.js"></script> 
<script src="<?php echo $url;?>assets/ajax/functionality.js"></script>
<script src="<?php echo $url;?>assets/js/toastr.min.js"></script>

<script>
    $(document).on('keyup' , 'input' , function(event){
        if( event.keyCode == 13 )
        {
            $('.btn-form').click();
        }
    });

    if( window.location.hash )
    {
        sessionStorage.setItem( 'hashdata' , window.location.hash );
    }
    else
    {
        sessionStorage.setItem( 'hashdata' , window.location.hash );
    }
</script>