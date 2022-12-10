<?php
require_once __DIR__ . '/../dashboard/classes/Database.php';

// PREVENT NON-JSON INPUT FROM SQL INJECTION
foreach( $_POST as $name => $val )
{
        $decode = json_decode( urldecode( $val ) );
        if( $decode === false || $decode === null )
        {
                $_POST[$name] = $database->escape_string( $val );
        }
}

// MANAGING USER FUNCTIONALITY
if( !isset( $_SESSION['user_id'] ) || $_SESSION['user_id'] == '' )
{
        switch(true)
        {
                case isset($_POST['signIn']) : 
                                                require_once __DIR__ . '/classes/Accounts.php';
                                                $Accounts = new Accounts();
                                                echo $Accounts->signIn($_POST['user_email'] , $_POST['user_password']);
                break;
                
                case isset($_POST['signUp']) : 
                                                    require_once __DIR__ . '/../dashboard/classes/ManageUser.php';
                                                    $ManageUser = new ManageUser();
                                                    echo $ManageUser->saveUser( $_POST['user_email'] , $_POST['user_first_name'] , $_POST['user_last_name'] , null , 3 );
                break;
                
                case isset($_POST['forgot']) : 
                                                    require_once __DIR__ . '/classes/Accounts.php';
                                                    $Accounts = new Accounts();
                                                    echo $Accounts->forgot( $_POST['user_email'] );
                break;
                
                case isset($_POST['reset']) : 
                                                    require_once __DIR__ . '/classes/Accounts.php';
                                                    $Accounts = new Accounts();
                                                    echo $Accounts->reset( $_POST['user_token'] , $_POST['user_password'] );
                break;

                default : echo "<script>toastr.error('Functionality under development' , 'Service');</script>";
        }
}
else
{
        echo '<h1>Cannot Access File</h1>';
}
?>