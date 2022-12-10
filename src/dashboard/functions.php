<?php
require_once __DIR__ . '/classes/Database.php';

// PREVENT NON-JSON INPUT FROM SQL INJECTION
foreach( $_POST as $name => $val )
{
        $decode = json_decode( urldecode( $val ) );
        if( $decode === false || $decode === null )
        {
                $_POST[$name] = $database->escape_string( $val );
        }
}

// MANAGING FUNCTIONALITY BASED ON USER SIGNIN AND USER ROLE
if( isset( $_SESSION['user_id'] ) && $_SESSION['user_id'] != '' )
{
        switch( $_SESSION['user_role_id'] )
        {
                case 1 : require_once __DIR__ . '/functions/adminFunctions.php';
                break;

                case 2 : require_once __DIR__ . '/functions/employFunctions.php';
                break;

                case 3 : require_once __DIR__ . '/functions/usersFunctions.php';
                break;
        }
}
else
{
        require_once __DIR__ . '/functions/usersFunctions.php';
}

function userEmailToAdmin()
{
        $body = '<div class="table"><table> <tr> <td>Name</td><td>'. $_POST['name'] .'</td></tr><tr> <td>Email</td><td>'. $_POST['email_id'] .'</td></tr><tr> <td>Message</td><td>'. $_POST['message'] .'</td></tr></table></div>';
        
        $subject = $_POST['email_id'];
        $user_email = 'admin@onbeca.com';

        require_once __DIR__ . '/classes/ManageUser.php';
        $ManageUser = new ManageUser();
        echo $ManageUser->mailData( $user_email , $subject , $body );
}
?>