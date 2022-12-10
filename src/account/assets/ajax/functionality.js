function signIn(){
    $('input').css('box-shadow' , '0 0 0 2px rgba(40,40,40, .25)');
    $('label').css('color' , 'rgba(40,40,40, .75)');

    $(function(){
        if($('#user_email').val() == '' || $('#user_password').val() == '' )
        {
            $('input').css('box-shadow' , '0 0 0 2px rgba(255, 16, 31, .5)');
            $('label:not(.error)').addClass('notallow').css('color' , '#FF101F');
            $('#error').html('<i class="fas fa-times"></i>&emsp;Fill all Credentials').fadeIn().css("display" , "block").delay(5000).fadeOut();
        }
        else
        {
            var user_email = $('#user_email').val();
            var user_password = $('#user_password').val()
            var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            
            if(regex.test(user_email))
            {
                $('#form').stop(true, true).fadeIn();
                $('#form').html('<div class="spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div>');
                $.ajax({
                    type: 'POST',
                    url: baseUrl+'functions.php',
                    data: 'signIn&user_email=' + user_email + '&user_password=' + user_password
                }).done(function (response) {
                    if( response != true )
                    {
                        $('body').append(response);
                        loadFunction('signIn');
                    }
                    else
                    {
                        if( sessionStorage.getItem('hashdata') == '' )
                        {
                            location.reload();
                        }
                        else
                        {
                            window.location.href = decodeURIComponent( window.location.hash.substring(1) );
                        }
                    }
                });
            }
            else
            {
                $('#user_email').css('box-shadow' , '0 0 0 2px rgba(255, 16, 31, .5)');
                $('#user_email ~ label').addClass('notallow').css('color' , '#FF101F');
                $('#error').html('<i class="fas fa-times"></i>&emsp;Invalid Email Format').fadeIn().css("display" , "block").delay(5000).fadeOut();
            }
        }
    });
}

function signUp(){
    $('input').css('box-shadow' , '0 0 0 2px rgba(40,40,40, .25)');
    $('label').css('color' , 'rgba(40,40,40, .75)');

    $(function(){
        if( $('input').val() == '' )
        {
            $('input').css('box-shadow' , '0 0 0 2px rgba(255, 16, 31, .5)');
            $('label:not(.error)').addClass('notallow').css('color' , '#FF101F');
            $('#error').html('<i class="fas fa-times"></i>&emsp;Fill all Credentials').fadeIn().css("display" , "block").delay(5000).fadeOut();
        }
        else
        {
            var user_first_name = $('#user_first_name').val();
            var user_last_name = $('#user_last_name').val();
            var user_email = $('#user_email').val();

            var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            
            if(regex.test(user_email))
            {
                $('#form').stop(true, true).fadeIn();
                $('#form').html('<div class="spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div>');
                $.ajax({
                    type: 'POST',
                    url: baseUrl+'functions.php',
                    data: 'signUp&user_first_name='+ user_first_name + '&user_last_name=' + user_last_name + '&user_email=' + user_email
                }).done(function (response) {
                    if( response != true )
                    {
                        toastr.error(response , 'SignUp');
                        loadFunction('signUp');
                    }
                    else
                    {
                        toastr.options = {
                          "timeOut": "25000"
                        }
                        toastr.success('Check your Email or Spam' , 'Registered');
                        window.history.replaceState( null , null , baseUrl + 'signIn/' + sessionStorage.getItem('hashdata') );
                        loadFunction('signIn');
                    }
                });
            }
            else
            {
                $('#user_email').css('box-shadow' , '0 0 0 2px rgba(255, 16, 31, .5)');
                $('#user_email ~ label').addClass('notallow').css('color' , '#FF101F');
                $('#error').html('<i class="fas fa-times"></i>&emsp;Invalid Email Format').fadeIn().css("display" , "block").delay(5000).fadeOut();
            }
        }
    });
}

function forgot(){
    $('input').css('box-shadow' , '0 0 0 2px rgba(40,40,40, .25)');
    $('label').css('color' , 'rgba(40,40,40, .75)');

    $(function(){
        if($('#user_email').val() == '')
        {
            $('input').css('box-shadow' , '0 0 0 2px rgba(255, 16, 31, .5)');
            $('label:not(.error)').addClass('notallow').css('color' , '#FF101F');
            $('#error').html('<i class="fas fa-times"></i>&emsp;Fill all Credentials').fadeIn().css("display" , "block").delay(5000).fadeOut();
        }
        else
        {
            var user_email = $('#user_email').val();
            var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            
            if(regex.test(user_email))
            {
                $('#form').stop(true, true).fadeIn();
                $('#form').html('<div class="spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div>');
                $.ajax({
                    type: 'POST',
                    url: baseUrl+'functions.php',
                    data: 'forgot&user_email=' + user_email
                }).done(function (response) {
                    toastr.options = {
                      "timeOut": "25000"
                    }
                    $('body').append(response);
                    loadFunction('forgot');
                });
            }
            else
            {
                $('input').css('box-shadow' , '0 0 0 2px rgba(255, 16, 31, .5)');
                $('label:not(.error)').addClass('notallow').css('color' , '#FF101F');
                $('#error').html('<i class="fas fa-times"></i>&emsp;Invalid Email Format').fadeIn().css("display" , "block").delay(5000).fadeOut();
            }
        }
    });
}

function resetPassword(){
    $('input').css('box-shadow' , '0 0 0 2px rgba(40,40,40, .25)');
    $('label').css('color' , 'rgba(40,40,40, .75)');

    var user_token = window.location.href.split("reset/")[1].replace(/\//g, '');
    $(function(){
        var user_password = $('#user_password').val();
        var user_confirm = $('#user_confirm').val();
        
        if( user_password == '' || user_confirm == '' )
        {
            $('input').css('box-shadow' , '0 0 0 2px rgba(255, 16, 31, .5)');
            $('label:not(.error)').addClass('notallow').css('color' , '#FF101F');
            $('#error').html('<i class="fas fa-times"></i>&emsp;Fill all Credentials').fadeIn().css("display" , "block").delay(5000).fadeOut();
        }
        else
        {
            var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)((.*[!@#\$%\^&\*\.])?)[a-zA-Z\d!@#\$%\^&\*\.]{8,}$/;

            if(regex.test(user_password))
            {
                if( user_password != user_confirm )
                {
                    $('input').css('box-shadow' , '0 0 0 2px rgba(255, 16, 31, .5)');
                    $('label:not(.error)').addClass('notallow').css('color' , '#FF101F');
                    $('#error').html('<i class="fas fa-times"></i>&emsp;Password doesnot match').fadeIn().css("display" , "block").delay(5000).fadeOut();
                }
                else
                {
                    $('#form').stop(true, true).fadeIn();
                    $('#form').html('<div class="spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div>');
                    $.ajax({
                        type: 'POST',
                        url: baseUrl+'functions.php',
                        data: 'reset&user_token='+ user_token +'&user_password=' + user_password
                    }).done(function (response) {
                        if( response != true )
                        {
                            toastr.error(response , 'Reset');
                            loadFunction('reset');
                        }
                        else
                        {
                            toastr.success('Password Reset Successfully' , 'Reset');
                            window.history.replaceState( null , null , baseUrl + 'signIn/' + sessionStorage.getItem('hashdata') );
                            loadFunction('signIn');
                        }
                    });
                }
            }
            else
            {
                $('input').css('box-shadow' , '0 0 0 2px rgba(255, 16, 31, .5)');
                $('label:not(.error)').addClass('notallow').css('color' , '#FF101F');
                $('#error').html('<i class="fas fa-times"></i>&emsp;8 Credentials Required<br/>Password Should Contain Atleast:<br/>1 uppercase letter<br/>1 lowercase letter<br/>1 number<br/>Optional - Special Characters Allowed ( !@#$%^&*. )').fadeIn().css("display" , "block");
            }
        }
    });
}