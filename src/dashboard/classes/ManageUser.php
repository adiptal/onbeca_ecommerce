<?php
class ManageUser
{
    private $IssueLogging;
	private $current_time;
    private $connection;
    private $false = 0;
    private $true = 1;

	function __construct()
	{
		// Default Values
        global $database;
        $this->connection = $database->getConnection();
		$this->current_time = date('Y-m-d h:i:s', time());

        set_error_handler(array($this, "customIssueLogger"));
        require_once __DIR__ . '/IssueLogging.php';
        $this->IssueLogging = new IssueLogging();
    }

    public function customIssueLogger( $errno , $errstr , $errfile , $errline )
    {
        throw new Exception($errline . ' : ' . $errstr . ' in File : ' . $errfile);
    }
    
    private function checkUserExists( $attr_name , $user_data )
    {
        $query = "SELECT user_id FROM users WHERE $attr_name LIKE '".$user_data."'" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $user_id );
		if( $preparedStatement->fetch() )
		{
            return $user_id;
        }
        else
        {
            return false;
        }
    }

	private function generateRandomString( $length = 10 )
	{
		// ********** Generating Random String **********//
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen( $characters );
		$randomString = '';
		for ( $i = 0; $i < $length; $i++ )
		{
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;

		// ********** xx ********** xx ********** xx ********** //
	}

	public function mailData( $user_email , $subject , $body )
	{
        $body = '<!DOCTYPE html><html lang="en"><head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <meta http-equiv="X-UA-Compatible" content="ie=edge"> <title>Onbeca</title> <style>*{color: #282828; padding: 0; margin: 0; font-size: 14px; font-family: "Montserrat", sans-serif; font-weight: 500; box-sizing: border-box;}a, button{border: none; outline: none; text-decoration: none; background: transparent; cursor: pointer;}html, body{width: 100%; max-width: 1920px; margin: 0 auto;}.mail-html{background: rgba(40, 40, 40, .075); padding: 2.5em;}.topbar{width: 100%; box-sizing: border-box; position: relative; padding: 1em 2.5em; background: #FFF; z-index: 100;}.topbar h1{display: inline-block;}.topbar h1 a{display: inline-block; font-size: 2em; font-weight: 600; color: #FFF; background: #282828; padding: .25em .5em; letter-spacing: 2px; transition: all .15s; text-transform: uppercase;}.mail-data{width: 100%; background: #FFF; margin: 2.5em auto; padding: 2em 2.5em;}.mail-data .table{width: 100%; overflow: auto;}.mail-data .table table{border-spacing: 0; width: 100%; border-collapse: collapse;}.mail-data .table table td{font-size: 1.1em; color: rgba(40, 40, 40, .8); text-align: left; padding: 1em; letter-spacing: .025em; border: 1px solid rgba(40, 40, 40, .175);}.mail-data p{color: #282828; font-size: 1.1em; margin: .6em auto;}.mail-data table a , .mail-data p a{background: rgba(40, 40, 40, .1); padding: .25em .5em; color: #282828; font-size: inherit;}.mail-data span{background: rgba(40, 40, 40, .8); padding: .25em .5em; color: #FFF; font-size: inherit;}.mail-data footer{margin-top: 2.5em; border-top: solid 1px rgba(40, 40, 40, .15);}.mail-data footer h2{font-size: 1.1em; font-weight: 600;}</style></head><body class="mail-html"> <header class="topbar"> <h1><a href="http://localhost/onbeca/">Onbeca</a></h1> </header> <div class="mail-data"> '. $body .' <footer> <p>Regards,</p><h2>Team Onbeca</h2> </footer> </div></body></html>';
        
        try
        {
            // ********** Mailing Data to Recipent **********//
            require_once __DIR__ . "/../../account/phpmailer/src/PHPMailer.php";
            require_once __DIR__ . "/../../account/phpmailer/src/SMTP.php";
            
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->IsSMTP(); // enable SMTP

            $mail->SMTPDebug = 0; // debugging: 1 = errors and messages, 2 = messages only
            $mail->SMTPAuth = true; // authentication enabled
            $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
            $mail->Host = "onbeca.com";
            $mail->Port = 465; // or 587
            $mail->IsHTML(true);
            $mail->Username = "admin@onbeca.com";
            $mail->Password = "admin@onbeca.com";
            $mail->SetFrom("admin@onbeca.com","Onbeca");
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AddAddress( $user_email );

            if( !$mail->Send() )
            {
                return false;
            }
            else
            {
                return true;
            }
            // ********** xx ********** xx ********** xx ********** //
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Manage User' , 'Mail Data' , $th->getMessage() );
        }
	}
    
    public function saveUser( $user_email , $user_first_name , $user_last_name , $user_id = null , $user_role_id = 2 )
    {
        try
        {
            if( $user_id == '' || $user_id == null )
            {
                $get_user_id = $this->checkUserExists( 'user_email' , $user_email );
            }
            else
            {
                $get_user_id = $this->checkUserExists( 'user_id' , $user_id );
            }

            if( $get_user_id == false )
            {
                $user_password = $this->generateRandomString();
                $password_hash = password_hash($user_password , PASSWORD_BCRYPT);
                $query = "INSERT INTO users ( user_email , user_password , user_first_name , user_last_name , created_at , is_deleted , user_role_id ) VALUES ( ? , ? , ? , ? , ? , ? , ? )";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sssssii" , $user_email , $password_hash , $user_first_name , $user_last_name , $this->current_time , $this->false , $user_role_id );
                $preparedStatement->execute();
                
                $subject = 'Account Registration - Onbeca';
                $body = '<p>Successfully Registered your account</p><p>To login and manage account go to <a href="http://localhost/onbeca/account/">Onbeca</a></p><p>Please use <span>' . $user_password .'</span> as your password to login into your account</p>';
                
                if( !$this->mailData( $user_email , $subject , $body ) && $user_role_id == 3 )
                {
                    return 'Issue Occoured';
                }
                else if( $user_role_id == 3 )
                {
                    return true;
                }
            }
            else if( $user_role_id == 2 )
            {
                $query = "UPDATE users SET user_email = ? , user_first_name = ? , user_last_name = ? , updated_at = ? , is_deleted = ? WHERE user_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "ssssii" , $user_email , $user_first_name , $user_last_name , $this->current_time , $this->false , $get_user_id );
                $preparedStatement->execute();
            }
            else if( $get_user_id == true && $user_role_id == 3 )
            {
                return 'Email Already Exists';
            }

            header('Content-Type: application/json');
            return json_encode(array( 'success' , 'User List Updated' ));
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Manage User' , 'Save User' , $th->getMessage() );
            header('Content-Type: application/json');
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function deleteUser( $user_id )
    {
        header('Content-Type: application/json');
        try
        {
            if( $this->checkUserExists( 'user_id' , $user_id ) )
            {
                $query = "UPDATE users SET deleted_at = ? , is_deleted = ? WHERE user_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sii" , $this->current_time , $this->true , $user_id );
                $preparedStatement->execute();
                return json_encode(array( 'success' , 'User Deleted' ));
            }
            else
            {
                return json_encode(array( 'error' , 'User ID doesnot exists' ));
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Manage User' , 'Delete User' , $th->getMessage() );
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function changePassword( $current_password , $user_password )
    {
        header('Content-Type: application/json');
        try
        {
            if( !password_verify( $current_password , $_SESSION['user_password'] ) )
            {
                return json_encode(array( 'error' , 'Incorrect Current Password Credentials' ));
            }
            else
            {
                if( $current_password === $user_password )
                {
                    return json_encode(array( 'error' , 'Current Password should not match New Password' ));
                }
                else
                {
                    $password_hash = password_hash($user_password , PASSWORD_BCRYPT);
                    $query = "UPDATE users SET user_password = ? , is_verified = 1 WHERE user_id = ?";
                    $preparedStatement = $this->connection->prepare( $query );
                    $preparedStatement->bind_param( "si" , $password_hash , $_SESSION['user_id'] );
                    $preparedStatement->execute();

                    $subject = 'Password Updated - Onbeca';
                    $body = '<p>Successfully Updated New Password on your account</p><p>To login and manage account go to <a href="http://localhost/onbeca/account/">Onbeca</a></p><p>You can now use your new password to login into your account</p>';

                    $this->mailData( $_SESSION['user_email'] , $subject , $body );
                    $_SESSION = null;
                    session_destroy();

                    return json_encode(array( 'success' , 'Password Updated Successfully' ));
                }
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Manage User' , 'Change User Password' , $th->getMessage() );
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function getUsers()
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT * FROM users WHERE user_role_id = 2 AND is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['user_id'] , $row['user_email'] , $row['user_first_name']  , $row['user_last_name']) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Manage User' , 'Get Users' , $th->getMessage() );
        }
    }

    public function getUserInfo( $user_id )
    {
        try
        {
            $data_list_json = array();

            $query = "SELECT * FROM users WHERE user_id = $user_id AND is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );
            if ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['user_email'] , $row['user_first_name']  , $row['user_last_name']) );
            }
            else
            {
                $data_list_json = false;
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Manage User' , 'Get User Info' , $th->getMessage() );
        }
    }
}
?>