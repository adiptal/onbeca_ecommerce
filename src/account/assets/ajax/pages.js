$(function(){
    $('#form').show();

    if( window.location.href.split("account/")[1] == '' )
    {
        $('#form').html('<div class="spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div>');
        loadFunction( 'signIn' );
        window.history.replaceState( null , null , baseUrl + 'signIn/' );
    }

    setTimeout(function(){
        $.getScript("https://use.fontawesome.com/releases/v5.0.13/js/all.js");
    },2000);
});

window.onpopstate = function(e){
    switch( window.location.href.split("account/")[1] )
    {
        case 'signIn/'   : signInPage();
        break;
        
        case 'signUp/'   : signUpPage();
        break;
        
        case 'forgot/'   : forgotPage();
        break;
        
        case 'reset/'   : resetPage();
        break;
    }
};

function loadFunction( page , pushActive = false )
{
    switch( page )
    {
        case 'signIn'   : signInPage();
        break;
        
        case 'signUp'   : signUpPage();
        break;
        
        case 'forgot'   : forgotPage();
        break;
        
        case 'reset'   : resetPage();
        break;
    }
    if( pushActive )
    {
        window.history.pushState( null , null , baseUrl + page + '/' + sessionStorage.getItem('hashdata') );
    }
}

function signInPage(){
    $('#form').hide();
    $.ajax({
        url: baseUrl+'/includes/forms/signin.php'
    }).done(function (response) {    
        $(function() {
            $("#form").html(response);
            $('#form').show();
        });
    })
}

function signUpPage(){
    $('#form').hide();
    $.ajax({
        url: baseUrl+'/includes/forms/signup.php'
    }).done(function (response) {    
        $(function() {
            $("#form").html(response);
            $('#form').show();
        });
    })
}

function forgotPage(){
    $('#form').hide();
    $.ajax({
        url: baseUrl+'/includes/forms/forgot.php'
    }).done(function (response) {    
        $(function() {
            $("#form").html(response);
            $('#form').show();
        });
    })
}

function resetPage(){
    $('#form').hide();
    $.ajax({
        url: baseUrl+'/includes/forms/reset.php'
    }).done(function (response) {    
        $(function() {
            $("#form").html(response);
            $('#form').show();
        });
    })
}