<?php
class IssueLogging
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
    
    private function checkIssueExists( $str )
    {
        $query = "SELECT issue_logging_id FROM issue_logging WHERE $str" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $issue_logging_id );
		if( $preparedStatement->fetch() )
		{
            return $issue_logging_id;
        }
        else
        {
            return false;
        }
    }
    
    public function logIssue( $source_table , $source_function , $issue )
    {
        $issue = str_replace('\\', '_', $issue);
        $get_issue_logging_id = $this->checkIssueExists( 'source_table LIKE "' . $source_table . '" AND source_function LIKE "' . $source_function . '" AND issue LIKE "' . $issue . '"' );

        if( $get_issue_logging_id == false )
        {
            $query = "INSERT INTO issue_logging ( source_table , source_function , issue , created_at , count ) VALUES ( ? , ? , ? , ? , ? )";
            $preparedStatement = $this->connection->prepare( $query );
            $preparedStatement->bind_param( "ssssi" , $source_table , $source_function , $issue , $this->current_time , $this->true );
            $preparedStatement->execute();
        }
        else
        {
            $query = "UPDATE issue_logging SET count = count + 1 , is_solved = ? WHERE issue_logging_id = ?";
            $preparedStatement = $this->connection->prepare( $query );
            $preparedStatement->bind_param( "ii" , $this->false , $get_issue_logging_id );
            $preparedStatement->execute();
            header('Content-Type: application/json');
            return json_encode(array( 'success' , 'Issue Solved' ));
        }
    }

    public function solvedIssue( $issue_logging_id )
    {
        if( $this->checkIssueExists( 'issue_logging_id = ' . $issue_logging_id ) )
        {
            $query = "UPDATE issue_logging SET count = 0 , is_solved = ? WHERE issue_logging_id = ?";
            $preparedStatement = $this->connection->prepare( $query );
            $preparedStatement->bind_param( "ii" , $this->true , $issue_logging_id );
            $preparedStatement->execute();
            header('Content-Type: application/json');
            return json_encode(array( 'success' , 'Issue Solved' ));
        }
        else
        {
            return false;
        }
    }

    public function getIssues()
    {
        $data_list_json = array();
        $query = "SELECT * FROM issue_logging WHERE is_solved = 0";
        $data = mysqli_query( $this->connection , $query );
        while ( $row = mysqli_fetch_assoc($data) )
        {
            array_push( $data_list_json , array( $row['issue_logging_id'] , $row['source_table'] , $row['source_function']  , $row['issue'] , $row['created_at']) );
        }
        
        header('Content-Type: application/json');
        return json_encode($data_list_json);
    }

    public function getIssuesCount()
    {
        $data_list_json = array();
        $query = "SELECT count(*) as count FROM issue_logging WHERE is_solved = 0";
        $data = mysqli_query( $this->connection , $query );
        while ( $row = mysqli_fetch_assoc($data) )
        {
            $data_list_json = $row['count'];
        }
        
        header('Content-Type: application/json');
        return json_encode($data_list_json);
    }
}
?>