<?php
class ProductLocation
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
    
    private function checkProductLocationExists( $str )
    {
        $query = "SELECT product_locale_id FROM product_locale WHERE $str" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $product_locale_id );
		if( $preparedStatement->fetch() )
		{
            return $product_locale_id;
        }
        else
        {
            return false;
        }
    }
    
    public function manageProductLocation( $product_id , $locale_ids )
    {
        try
        {
            $query = "UPDATE product_locale SET deleted_at = ? , is_deleted = ? WHERE product_id = ?";
            $preparedStatement = $this->connection->prepare( $query );
            $preparedStatement->bind_param( "sii" , $this->current_time , $this->true , $product_id );
            $preparedStatement->execute();
            if( $locale_ids != null )
            {
                $locale_id_array = explode(',' , $locale_ids);

                foreach ($locale_id_array as $locale_id) {
                    if( $this->checkProductLocationExists( 'locale_id =' . $locale_id . ' AND product_id =' . $product_id ) )
                    {
                        $query = "UPDATE product_locale SET is_deleted = ? WHERE product_id = ? AND locale_id = ?";
                        $preparedStatement = $this->connection->prepare( $query );
                        $preparedStatement->bind_param( "iii" , $this->false , $product_id , $locale_id );
                        $preparedStatement->execute();
                    }
                    else
                    {
                        $query = "INSERT INTO product_locale ( product_id , locale_id , created_at , is_deleted ) VALUES ( ? , ? , ? , ? )";
                        $preparedStatement = $this->connection->prepare( $query );
                        $preparedStatement->bind_param( "iisi" , $product_id , $locale_id , $this->current_time , $this->false );
                        $preparedStatement->execute();
                    }
                }
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Product Location' , 'Manage Product Location' , $th->getMessage() );
        }
    }

    public function getProductLocations( $product_id )
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT locale.locale_id , locale.locale_name FROM product_locale , locale WHERE locale.locale_id = product_locale.locale_id AND product_locale.product_id = $product_id AND product_locale.is_deleted = 0 ORDER BY product_locale.created_at DESC";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['locale_id'] , $row['locale_name'] ) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Product Location' , 'Get Product Locations' , $th->getMessage() );
        }
    }
}
?>