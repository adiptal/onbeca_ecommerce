<h2 class="breadcrump">
    Settings
</h2>

<div class="form-input col-md-10 offset-md-1 col-lg-8 offset-lg-2">
    <input type="password" id="current_password" required>
    <label for="current_password">Current Password</label>
</div>

<div class="form-input col-md-10 offset-md-1 col-lg-8 offset-lg-2">
    <input type="password" id="user_password" required>
    <label for="user_password">New Password</label>
</div>

<div class="form-input col-md-10 offset-md-1 col-lg-8 offset-lg-2">
    <input type="password" id="user_confirm" required>
    <label for="user_confirm">Confirm New Password</label>
</div>

<div class="form-input col-md-10 offset-md-1 col-lg-8 offset-lg-2 toolbar">
    <p></p>
    <button class="save">Save</button>
</div>

<script>
    $(document).on('click' , '.save' , function(){
        $('.save').removeClass('save').addClass('save-disabled').html('Saving');
        if( $('.form-input input').val() == null || $('.form-input input').val() == '' )
        {
            $('.toolbar>p').html('<i class="fas fa-times"></i>&emsp;Provide All Credential').stop().fadeIn().css('display' , 'inline-block').delay(5000).fadeOut();

            $('.save-disabled').removeClass('save-disabled').addClass('save').html('Save');
        }
        else
        {
            var current_password = $('#current_password').val();
            var user_password = $('#user_password').val();
            var user_confirm = $('#user_confirm').val();

            if( current_password !== user_password )
            {
                var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)((.*[!@#\$%\^&\*\.])?)[a-zA-Z\d!@#\$%\^&\*\.]{8,}$/;
                if( !regex.test( user_password ) )
                {
                    $('.toolbar>p').html('<i class="fas fa-times"></i>&emsp;8 Credentials Required<br/>Password Should Contain Atleast:<br/>1 uppercase letter<br/>1 lowercase letter<br/>1 number<br/>Optional - Special Characters Allowed ( !@#$%^&*. )').fadeIn().css("display" , "block");
                
                    $('.save-disabled').removeClass('save-disabled').addClass('save').html('Save');
                }
                else if( user_password === user_confirm )
                {
                    $.ajax({
                        method: "POST",
                        url: <?php echo '"' . $url . '"';?> + 'functions.php',
                        data: 'changePassword&current_password=' + encodeURIComponent(current_password) + '&user_password=' + encodeURIComponent(user_password),
                        timeout: 10000
                    }).fail(function(request , error){
                        console.log(error);
                    }).done(function(response) {
                        Swal.mixin({
                            toast: true,
                            position: 'bottom-end',
                            showConfirmButton: false,
                            timer: 5000
                        }).fire({
                            type: response[0],
                            title: response[1]
                        })
                        if( response[0] == 'success' )
                        {
                            $('.save-disabled').remove();
                            $('.toolbar>p').html(
                                'LockDown !<br/>'+
                                'Login with New Password.<br/>'+
                                'Redirect Login Page in <span>25</span> secs'
                            ).stop().fadeIn().css('display' , 'inline-block').delay(25000).fadeOut();
                            var i = 24;
                            setInterval(function(){
                                $('.toolbar>p>span').html(i);
                                i--;
                            } , 1000);
                            setTimeout( function(){
                                location.reload();
                            } , 25000);
                        }
                        else
                        {
                            $('.save-disabled').removeClass('save-disabled').addClass('save').html('Save');
                        }
                    });
                }
                else
                {
                    $('.toolbar>p').html('<i class="fas fa-times"></i>&emsp;New Password and Confirm Password should match').stop().fadeIn().css('display' , 'inline-block').delay(5000).fadeOut();
                    $('.save-disabled').removeClass('save-disabled').addClass('save').html('Save');
                }
            }
            else
            {
                $('.toolbar>p').html('<i class="fas fa-times"></i>&emsp;Current Password should not match New Password').stop().fadeIn().css('display' , 'inline-block').delay(5000).fadeOut();
                $('.save-disabled').removeClass('save-disabled').addClass('save').html('Save');
            }
        }
    });
</script>