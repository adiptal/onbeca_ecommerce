<div class="row">
    <div class="col-10 offset-1 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-4 offset-lg-4 form">
        <h1><a href="<?php echo $parent_url;?>" title="<?php echo $site_name?>"><?php echo $site_name?></a></h1>
        <div id="form">
<?php
switch( $page )
{
    case 'signIn'  :   require_once('includes/forms/signin.php');
    break;
    
    case 'signUp'  :   require_once('includes/forms/signup.php');
    break;
    
    case 'forgot'  :   require_once('includes/forms/forgot.php');
    break;
    
    case 'reset'  :   require_once('includes/forms/reset.php');
    break;
    
    case 'error-token' :
        http_response_code(404);
        echo '<h2 class="no-page">Token Invalid</h2>';
    break;
    
    default :
        http_response_code(404);
        echo '<h2 class="no-page">Error 404 Page Not Found</h2>';
}
?>
        </div>
    </div>
</div>