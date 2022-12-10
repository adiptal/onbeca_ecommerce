<?php
class LinkTracker
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
    
    private function checkLinkTrackerExists( $str )
    {
        $query = "SELECT link_tracker_id FROM link_tracker WHERE $str" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $link_tracker_id );
		if( $preparedStatement->fetch() )
		{
            return $link_tracker_id;
        }
        else
        {
            return false;
        }
    }

    private function trimImportantURL( $link_url )
    {
        switch( $link_url )
        {
            case ( preg_match( '/(http|https):\/\/www.amazon\.(\S[^\/]+)\/([\\w-]+\/)?(dp|gp\/product)\/(\\w+\/)?(\\w{10})/' , $link_url , $matches ) ? true : false );
                return $matches[1] . '://www.amazon.' . $matches[2] . '/' . $matches[4] . '/' . $matches[6];
            break;

            case (preg_match('/(http|https):\/\/www.bestbuy\..*?/',  $link_url) ? true : false) :
                return substr( $link_url , 0 , strpos( $link_url , '.p?' ) );
            break;

            default : return $link_url;
        }
    }
    
    public function saveLinkTracker( $link_url , $link_tracker_id = null )
    {
        try
        {
            $link_url = $this->trimImportantURL( $link_url );
            
            if( $link_tracker_id == '' || $link_tracker_id == null )
            {
                $get_link_tracker_id = $this->checkLinkTrackerExists( 'link_url LIKE "' . $link_url . '"' );
            }
            else
            {
                $get_link_tracker_id = $this->checkLinkTrackerExists( 'link_tracker_id = ' . $link_tracker_id );
            }

            if( $get_link_tracker_id == false )
            {
                $query = "INSERT INTO link_tracker ( link_url ) VALUES ( ? )";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "s" , $link_url );
                $preparedStatement->execute();
                $get_link_tracker_id = $preparedStatement->insert_id;
            }

            return $get_link_tracker_id;
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Link Tracker' , 'Save Link Tracker' , $th->getMessage() );
            return false;
        }
    }
}
?>