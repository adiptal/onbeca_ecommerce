<?php
class Accounts
{
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
	}

	private function checkEmailExists( $user_email )
	{
		// ********** Checking if Email Exists and Returning Resultset **********//
		
		// Fetching via Class Account_Crud Model
        $query = "SELECT user_id , user_role_id , is_verified , user_password , user_first_name , user_token FROM users WHERE user_email = ? AND is_deleted = ?";
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->bind_param( "si" , $user_email , $this->false );
        $preparedStatement->execute();
        $preparedStatement->store_result();
		$count = $preparedStatement->num_rows();
		return $preparedStatement;

		// ********** xx ********** xx ********** xx ********** xx ********** //
	}
    
    public function checkTokenExists( $user_token )
    {
        $query = "SELECT user_email FROM users WHERE user_token LIKE '".$user_token."'" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $user_email );
		if( $preparedStatement->fetch() )
		{
            return $user_email;
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

	public function signIn( $user_email , $verify_password )
	{
		// ********** SignIn Process ********** //

		// Verifying Email Exists 
		$query = $this->checkEmailExists( $user_email );
		$query->bind_result( $user_id , $user_role_id , $is_verified , $user_password , $user_first_name , $user_token );
		$query->fetch();


		if( $query->num_rows() > 0 )
		{
			// Check Token Exists
			if( $user_token == '' )
			{
				// Verifying Password
				if( !password_verify( $verify_password , $user_password ) )
				{
					// Showing Password Error
					return "<script>toastr.error('Invalid Password' , 'Signin');</script>";
				}
				else
				{
					$_SESSION['user_id'] = $user_id;
					$_SESSION['user_role_id'] = $user_role_id;
					$_SESSION['is_verified'] = $is_verified;
					$_SESSION['user_email'] = $user_email;
					$_SESSION['user_first_name'] = $user_first_name;
					$_SESSION['user_password'] = $user_password;
					return true;
				}
			}
			else
			{
				// Showing User Forget Password Error
				return "<script>toastr.error('Requested to Reset Password' , 'Signin');</script>";
			}
		}
		else
		{
			// Showing User Error
			return "<script>toastr.error('Invalid User Email' , 'Signin');</script>";
		}

		// ********** xx ********** xx ********** //
	}

	public function forgot( $user_email )
	{ 
		$query = $this->checkEmailExists( $user_email );
		$query->bind_result( $user_id , $user_role_id , $is_verified , $user_password , $user_first_name , $user_token );
		$query->fetch();

		if( $query->num_rows() > 0 )
		{
			$user_token = sha1( $this->generateRandomString( 10 ) . $this->current_time );
			$query = "UPDATE users SET user_token = ? WHERE user_email = ?";
			$preparedStatement = $this->connection->prepare( $query );
			$preparedStatement->bind_param( "ss" , $user_token , $user_email );
			$preparedStatement->execute();

			$subject = 'Password Forgot - Onbeca';
			$body = '<p>Got request to reset your password as you forgot it</p><p>Click on <a href="http://localhost/onbeca/account/reset/'. $user_token .'/">Reset Password</a></p>';

			require_once __DIR__ . "/../../dashboard/classes/ManageUser.php";
			$ManageUser = new ManageUser();
			$ManageUser->mailData( $user_email , $subject , $body );
			return "<script>toastr.success('Check your Email or Spam' , 'Forgot');</script>";
		}
		else
		{
			return "<script>toastr.error('Email doesnot Exists' , 'Forgot');</script>";
		}
	}

	public function reset( $user_token , $user_password )
	{
		$user_email = $this->checkTokenExists( $user_token );
		
		if( $user_email != false )
		{
			$password_hash = password_hash($user_password , PASSWORD_BCRYPT);
			$query = "UPDATE users SET user_password = ? , user_token = '' WHERE user_email = ?";
			$preparedStatement = $this->connection->prepare( $query );
			$preparedStatement->bind_param( "ss" , $password_hash , $user_email );
			$preparedStatement->execute();

			$subject = 'Password Reset - Onbeca';
			$body = '<p>Successfully Reset New Password on your account</p><p>To login and manage account go to <a href="http://localhost/onbeca/account/">Onbeca</a></p><p>You can now use your new password to login into your account</p>';

			require_once __DIR__ . "/../../dashboard/classes/ManageUser.php";
			$ManageUser = new ManageUser();
			$ManageUser->mailData( $user_email , $subject , $body );
			return true;
		}
		else
		{
			return 'Token Invalid';
		}
	}
}
?>