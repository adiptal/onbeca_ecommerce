<?php
class Department
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
    
    private function checkDepartmentExists( $str )
    {
        $query = "SELECT department_id FROM department WHERE $str" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $department_id );
		if( $preparedStatement->fetch() )
		{
            return $department_id;
        }
        else
        {
            return false;
        }
    }
    
    public function saveDepartment( $department_name , $department_id = null )
    {
        try
        {
            if( $department_id == '' || $department_id == null )
            {
                $get_department_id = $this->checkDepartmentExists( 'department_name LIKE "' . $department_name . '"' );
            }
            else
            {
                $get_department_id = $this->checkDepartmentExists( 'department_id = ' . $department_id );
            }

            if( $get_department_id == false )
            {
                $query = "INSERT INTO department ( department_name , created_at , is_deleted ) VALUES ( ? , ? , ? )";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "ssi" , $department_name , $this->current_time , $this->false );
                $preparedStatement->execute();
            }
            else
            {
                $query = "UPDATE department SET department_name = ? , updated_at = ? , is_deleted = ? WHERE department_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "ssii" , $department_name , $this->current_time , $this->false , $get_department_id );
                $preparedStatement->execute();
            }
            
            header('Content-Type: application/json');
            return json_encode(array( 'success' , 'Department List Updated' ));
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Department' , 'Save Department' , $th->getMessage() );
            header('Content-Type: application/json');
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function deleteDepartment( $department_id )
    {
        header('Content-Type: application/json');
        try
        {
            if( $this->checkDepartmentExists( 'department_id = ' . $department_id ) )
            {
                $query = "UPDATE department SET deleted_at = ? , is_deleted = ? WHERE department_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sii" , $this->current_time , $this->true , $department_id );
                $preparedStatement->execute();
                return json_encode(array( 'success' , 'Department Deleted' ));
            }
            else
            {
                return json_encode(array( 'error' , 'Department ID doesnot exists' ));
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Department' , 'Delete Department' , $th->getMessage() );
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function getDepartments()
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT * FROM department WHERE is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['department_id'] , $row['department_name'] ) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Department' , 'Get Departments' , $th->getMessage() );
        }
    }
}
?>