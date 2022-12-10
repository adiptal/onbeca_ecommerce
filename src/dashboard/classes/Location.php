<?php
class Location
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
    
    private function checkLocationExists( $attr_name , $locale_data )
    {
        $query = "SELECT locale_id FROM locale WHERE $attr_name LIKE '".$locale_data."'" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $locale_id );
		if( $preparedStatement->fetch() )
		{
            return $locale_id;
        }
        else
        {
            return false;
        }
    }
    
    public function saveLocation( $locale_name , $locale_currency , $locale_id = null )
    {
        try
        {
            if( $locale_id == '' || $locale_id == null )
            {
                $get_locale_id = $this->checkLocationExists( 'locale_name' , $locale_name );
            }
            else
            {
                $get_locale_id = $this->checkLocationExists( 'locale_id ' , $locale_id );
            }

            if( $get_locale_id == false )
            {
                $query = "INSERT INTO locale ( locale_name , created_at , is_deleted ) VALUES ( ? , ? , ? )";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "ssi" , $locale_name , $this->current_time , $this->false );
                $preparedStatement->execute();
            }
            else
            {
                $query = "UPDATE locale SET locale_name = ? , locale_currency = ? , updated_at = ? , is_deleted = ? WHERE locale_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sssii" , $locale_name , $locale_currency , $this->current_time , $this->false , $get_locale_id );
                $preparedStatement->execute();
            }
            
            header('Content-Type: application/json');
            return json_encode(array( 'success' , 'Location List Updated' ));
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Location' , 'Save Location' , $th->getMessage() );
            header('Content-Type: application/json');
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function deleteLocation( $locale_id )
    {
        header('Content-Type: application/json');
        try
        {
            if( $this->checkLocationExists( 'locale_id' , $locale_id ) )
            {
                $query = "UPDATE locale SET deleted_at = ? , is_deleted = ? WHERE locale_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sii" , $this->current_time , $this->true , $locale_id );
                $preparedStatement->execute();
                return json_encode(array( 'success' , 'Location Deleted' ));
            }
            else
            {
                return json_encode(array( 'error' , 'Locale ID doesnot exists' ));
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Location' , 'Delete Location' , $th->getMessage() );
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function getLocations()
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT * FROM locale WHERE is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['locale_id'] , $row['locale_name'] , $row['locale_currency'] ) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Location' , 'Get Locations' , $th->getMessage() );
        }
    }

    public function getLocation( $locale_id )
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT * FROM locale WHERE locale_id = $locale_id AND is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , $row['locale_name'] , $row['locale_currency'] );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Location' , 'Get Locations' , $th->getMessage() );
        }
    }
}
?>