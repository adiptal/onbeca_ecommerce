<?php
class Affiliate
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
    
    private function checkAffiliateExists( $str )
    {
        $query = "SELECT affiliate_id FROM affiliate WHERE $str" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $affiliate_id );
		if( $preparedStatement->fetch() )
		{
            return $affiliate_id;
        }
        else
        {
            return false;
        }
    }
    
    public function saveAffiliate( $product_id , $locale_id , $affiliate_company , $product_condition , $affiliate_pricing , $affiliate_url , $manageAffiliateTracking , $affiliate_id = null )
    {
        try
        {
            if( $affiliate_id == '' || $affiliate_id == null )
            {
                $get_affiliate_id = $this->checkAffiliateExists( 'product_id = ' . $product_id . ' AND locale_id = ' . $locale_id . ' AND affiliate_company LIKE "' . $affiliate_company . '"' );
            }
            else
            {
                $get_affiliate_id = $this->checkAffiliateExists( 'affiliate_id = ' . $affiliate_id );
            }

            if( $get_affiliate_id == false )
            {
                $query = "INSERT INTO affiliate ( product_id , locale_id , affiliate_company , product_condition , affiliate_pricing , affiliate_url , created_at , is_deleted ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? )";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "iisssssi" , $product_id , $locale_id , $affiliate_company , $product_condition , $affiliate_pricing , $affiliate_url , $this->current_time , $this->false );
                $preparedStatement->execute();
                $get_affiliate_id = $preparedStatement->insert_id;
            }
            else
            {
                $query = "UPDATE affiliate SET affiliate_company = ? , product_condition = ? , affiliate_pricing = ? , affiliate_url = ? , updated_at = ? , is_deleted = ? , link_tracker_id = 0 WHERE affiliate_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sssssii" , $affiliate_company , $product_condition , $affiliate_pricing , $affiliate_url , $this->current_time , $this->false , $get_affiliate_id );
                $preparedStatement->execute();
            }

            if( $manageAffiliateTracking == 'on' && !empty( $affiliate_url ) )
            {
                require_once __DIR__ . '/Automation.php';
                $Automation = new Automation();
                $Automation->manageAffiliateTracking( $get_affiliate_id , $affiliate_url , $affiliate_pricing );
            }
            
            header('Content-Type: application/json');
            return json_encode(array( 'success' , 'Affiliate List Updated' ));
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Affiliate' , 'Save Affiliate' , $th->getMessage() );
            header('Content-Type: application/json');
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function deleteAffiliate( $affiliate_id )
    {
        header('Content-Type: application/json');
        try
        {
            if( $this->checkAffiliateExists( 'affiliate_id = ' . $affiliate_id ) )
            {
                $query = "UPDATE affiliate SET deleted_at = ? , is_deleted = ? , link_tracker_id = 0 , affiliate_pricing = 0 , affiliate_url = '' WHERE affiliate_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sii" , $this->current_time , $this->true , $affiliate_id );
                $preparedStatement->execute();
                return json_encode(array( 'success' , 'Affiliate Deleted' ));
            }
            else
            {
                return json_encode(array( 'error' , 'Affiliate ID doesnot exists' ));
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Affiliate' , 'Delete Affiliate' , $th->getMessage() );
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function getAffiliates( $product_id , $locale_id )
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT * FROM affiliate WHERE product_id = $product_id AND locale_id = $locale_id AND is_deleted = 0 ORDER BY affiliate_pricing ASC";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['affiliate_id'] , $row['affiliate_company'] , $row['product_condition'] , $row['affiliate_pricing'] , $row['affiliate_url'] , $row['link_tracker_id'] ) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Affiliate' , 'Get Affiliates' , $th->getMessage() );
        }
    }

    public function getProductBestAffiliate( $product_id , $locale_id )
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT affiliate_id , affiliate_company , product_condition , affiliate_url , affiliate_pricing AS 'affiliate_pricing' FROM affiliate WHERE product_id = $product_id AND locale_id = $locale_id AND is_deleted = 0 ORDER BY affiliate_pricing ASC LIMIT 1 OFFSET 0";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['affiliate_id'] , $row['affiliate_company'] , $row['product_condition'] , $row['affiliate_pricing'] , $row['affiliate_url'] ) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Affiliate' , 'Get Best Affiliate' , $th->getMessage() );
        }
    }

    public function getAffiliateInfo( $affiliate_id )
    {
        try
        {
            $data_list_json = array();

            $query = "SELECT * FROM affiliate WHERE affiliate_id = $affiliate_id AND is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['affiliate_id'] , $row['affiliate_company'] , $row['product_condition'] , $row['affiliate_pricing'] , $row['affiliate_url'] , $row['link_tracker_id'] ) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Affiliate' , 'Get Affiliate Info' , $th->getMessage() );
        }
    }
}
?>