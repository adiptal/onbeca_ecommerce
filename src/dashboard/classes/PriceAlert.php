<?php
class PriceAlert
{
    private $IssueLogging;
    private $ManageUser;

	private $current_time;
    private $connection;
    private $false = 0;
    private $true = 1;
    private $user_id;

	function __construct()
	{
		// Default Values
        global $database;
        $this->connection = $database->getConnection();
        $this->current_time = date('Y-m-d h:i:s', time());

        if( isset( $_SESSION['user_id'] ) && !empty( $_SESSION['user_id'] ) )
        {
            $this->user_id = $_SESSION['user_id'];
        }
        
        require_once __DIR__ . '/ManageUser.php';
        $this->ManageUser = new ManageUser();
        
        set_error_handler(array($this, "customIssueLogger"));
        require_once __DIR__ . '/IssueLogging.php';
        $this->IssueLogging = new IssueLogging();
    }

    public function customIssueLogger( $errno , $errstr , $errfile , $errline )
    {
        throw new Exception($errline . ' : ' . $errstr . ' in File : ' . $errfile);
    }
    
    private function checkPriceAlertExists( $str )
    {
        $query = "SELECT price_alert_id FROM price_alert WHERE user_id = ". $this->user_id ." AND $str" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $price_alert_id );
		if( $preparedStatement->fetch() )
		{
            return $price_alert_id;
        }
        else
        {
            return false;
        }
    }
    
    public function savePriceAlert( $affiliate_id , $alert_price , $price_alert_id = null )
    {
        try
        {
            if( $price_alert_id == '' || $price_alert_id == null )
            {
                $get_price_alert_id = $this->checkPriceAlertExists( 'affiliate_id = ' . $affiliate_id );
            }
            else
            {
                $get_price_alert_id = $this->checkPriceAlertExists( 'price_alert_id = ' . $price_alert_id );
            }

            if( $get_price_alert_id == false )
            {
                $query = "INSERT INTO price_alert ( user_id , affiliate_id , alert_price , created_at , is_deleted ) VALUES ( ? , ? , ? , ? , ? )";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "iissi" , $this->user_id , $affiliate_id , $alert_price , $this->current_time , $this->false );
                $preparedStatement->execute();
            }
            else
            {
                $query = "UPDATE price_alert SET affiliate_id = ? , alert_price = ? , updated_at = ? , is_deleted = ? WHERE price_alert_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "issii" , $affiliate_id , $alert_price , $this->current_time , $this->false , $get_price_alert_id );
                $preparedStatement->execute();
            }
            
            header('Content-Type: application/json');
            return json_encode(array( 'success' , 'Price Alert List Updated' ));
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Price Alert' , 'Save Price Alert' , $th->getMessage() );
            header('Content-Type: application/json');
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function deletePriceAlert( $price_alert_id )
    {
        header('Content-Type: application/json');
        try
        {
            if( $this->checkPriceAlertExists( 'price_alert_id=' . $price_alert_id ) )
            {
                $query = "UPDATE price_alert SET deleted_at = ? , is_deleted = ? WHERE price_alert_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sii" , $this->current_time , $this->true , $price_alert_id );
                $preparedStatement->execute();
                return json_encode(array( 'success' , 'Price Alert Deleted' ));
            }
            else
            {
                return json_encode(array( 'error' , 'Price Alert ID doesnot exists' ));
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Price Alert' , 'Delete Price Alert' , $th->getMessage() );
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function getPriceAlerts( $affiliate_ids )
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT * FROM price_alert WHERE user_id = ". $this->user_id ." AND affiliate_id in ( $affiliate_ids ) AND is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['price_alert_id'] , $row['affiliate_id'] , $row['alert_price'] ) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Price Alert' , 'Get Price Alerts' , $th->getMessage() );
        }
    }

    public function AlertPriceUpdateToUsers( $affiliate_id , $product_url , $affiliate_company , $product_name , $previous_price , $current_price , $locale_currency )
    {
        $query = "SELECT user_id FROM price_alert WHERE affiliate_id = $affiliate_id AND alert_price > $current_price AND is_deleted = 0" ;
        $data = mysqli_query( $this->connection , $query );
        while ( $row = mysqli_fetch_assoc($data) )
        {
            $body = '<div class="table">
                <table>
                    <tr>
                        <td>'. $product_name .'</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Previous Price</td>
                        <td>'. $locale_currency . $previous_price .'</td>
                    </tr>
                    <tr>
                        <td>Current Price</td>
                        <td>'. $locale_currency . $current_price .'</td>
                    </tr>
                    <tr>
                        <td>Check Offer</td>
                        <td><a href="'. $product_url .'">Onbeca</a></td>
                    </tr>
                </table>
            </div>';
            
            $query = "SELECT user_email FROM users WHERE user_id = " . $row['user_id'] ;
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                $this->ManageUser->mailData( $row['user_email'] , 'Product Price Alert - Onbeca' , $body );
            }
        }
    }
}
?>