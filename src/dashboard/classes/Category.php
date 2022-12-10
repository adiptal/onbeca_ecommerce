<?php
class Category
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
    
    private function checkCategoryExists( $str )
    {
        $query = "SELECT category_id FROM category WHERE $str" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $category_id );
		if( $preparedStatement->fetch() )
		{
            return $category_id;
        }
        else
        {
            return false;
        }
    }
    
    public function saveCategory( $department_id , $category_name , $category_id = null )
    {
        try
        {
            if( $category_id == '' || $category_id == null )
            {
                $get_category_id = $this->checkCategoryExists( 'department_id = ' . $department_id . ' AND category_name LIKE "' . $category_name . '"' );
            }
            else
            {
                $get_category_id = $this->checkCategoryExists( 'category_id = ' . $category_id );
            }

            if( $get_category_id == false )
            {
                $query = "INSERT INTO category ( department_id , category_name , created_at , is_deleted ) VALUES ( ? , ? , ? , ? )";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "issi" , $department_id , $category_name , $this->current_time , $this->false );
                $preparedStatement->execute();
            }
            else
            {
                $query = "UPDATE category SET category_name = ? , updated_at = ? , is_deleted = ? WHERE category_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "ssii" , $category_name , $this->current_time , $this->false , $get_category_id );
                $preparedStatement->execute();
            }
            
            header('Content-Type: application/json');
            return json_encode(array( 'success' , 'Category List Updated' ));
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Category' , 'Save Category' , $th->getMessage() );
            header('Content-Type: application/json');
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function deleteCategory( $category_id )
    {
        header('Content-Type: application/json');
        try
        {
            if( $this->checkCategoryExists( 'category_id=' . $category_id ) )
            {
                $query = "UPDATE category SET deleted_at = ? , is_deleted = ? WHERE category_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sii" , $this->current_time , $this->true , $category_id );
                $preparedStatement->execute();
                return json_encode(array( 'success' , 'Category Deleted' ));
            }
            else
            {
                return json_encode(array( 'error' , 'Category ID doesnot exists' ));
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Category' , 'Delete Category' , $th->getMessage() );
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function getCategories( $department_id )
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT * FROM category WHERE department_id = $department_id AND is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['category_id'] , $row['category_name'] ) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Category' , 'Get Categories' , $th->getMessage() );
        }
    }
}
?>