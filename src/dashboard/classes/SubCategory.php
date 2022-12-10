<?php
class SubCategory
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
    
    private function checkSubCategoryExists( $str )
    {
        $query = "SELECT sub_category_id FROM sub_category WHERE $str" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $sub_category_id );
		if( $preparedStatement->fetch() )
		{
            return $sub_category_id;
        }
        else
        {
            return false;
        }
    }
    
    public function saveSubCategory( $category_id , $sub_category_name , $filter_json = null , $sub_category_id = null )
    {
        try
        {
            $filter_json = preg_replace('/[ ]/', '-', $filter_json);
            
            if( $sub_category_id == '' || $sub_category_id == null )
            {
                $get_sub_category_id = $this->checkSubCategoryExists( 'category_id = ' . $category_id . ' AND sub_category_name LIKE "' . $sub_category_name . '"' );
            }
            else
            {
                $get_sub_category_id = $this->checkSubCategoryExists( 'sub_category_id = ' . $sub_category_id );
            }

            if( $get_sub_category_id == false )
            {
                $query = "INSERT INTO sub_category ( category_id , sub_category_name , filter_json , created_at , is_deleted ) VALUES ( ? , ? , ? , ? , ? )";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "isssi" , $category_id , $sub_category_name , $filter_json , $this->current_time , $this->false );
                $preparedStatement->execute();
            }
            else
            {
                $query = "UPDATE sub_category SET sub_category_name = ?, filter_json = ? , updated_at = ? , is_deleted = ? WHERE sub_category_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sssii" , $sub_category_name , $filter_json , $this->current_time , $this->false , $get_sub_category_id );
                $preparedStatement->execute();
            }
            
            header('Content-Type: application/json');
            return json_encode(array( 'success' , 'Sub Category List Updated' ));
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Sub Category' , 'Save Sub Category' , $th->getMessage() );
            header('Content-Type: application/json');
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function deleteSubCategory( $sub_category_id )
    {
        header('Content-Type: application/json');
        try
        {
            if( $this->checkSubCategoryExists( 'sub_category_id =' . $sub_category_id ) )
            {
                $query = "UPDATE sub_category SET deleted_at = ? , is_deleted = ? WHERE sub_category_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sii" , $this->current_time , $this->true , $sub_category_id );
                $preparedStatement->execute();
                return json_encode(array( 'success' , 'Sub Category Deleted' ));
            }
            else
            {
                return json_encode(array( 'error' , 'Sub Category ID doesnot exists' ));
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Sub Category' , 'Delete Sub Category' , $th->getMessage() );
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function getSubCategories( $category_id )
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT * FROM sub_category WHERE category_id = $category_id AND is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['sub_category_id'] , $row['sub_category_name'] , $row['filter_json'] ) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Sub Category' , 'Get Sub Categories' , $th->getMessage() );
        }
    }

    public function getSubCategory( $sub_category_id )
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT * FROM sub_category WHERE sub_category_id = $sub_category_id AND is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['sub_category_id'] , $row['sub_category_name'] , $row['filter_json'] ) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Sub Category' , 'Get Sub Category' , $th->getMessage() );
        }
    }
}
?>