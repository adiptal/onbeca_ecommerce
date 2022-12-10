<?php
class Banner
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
    
    private function checkBannerExists( $str )
    {
        $query = "SELECT banner_id FROM banner WHERE $str" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $banner_id );
		if( $preparedStatement->fetch() )
		{
            return $banner_id;
        }
        else
        {
            return false;
        }
    }
    
    public function saveBanner( $locale_id , $banner_page_url , $banner_image_url , $banner_id = null )
    {
        try
        {
            if( $banner_id == '' || $banner_id == null )
            {
                $get_banner_id = $this->checkBannerExists( 'locale_id = ' . $locale_id . ' AND banner_page_url LIKE "' . $banner_page_url . '"' );
            }
            else
            {
                $get_banner_id = $this->checkBannerExists( 'banner_id = ' . $banner_id );
            }

            if( $get_banner_id == false )
            {
                $query = "INSERT INTO banner ( locale_id , banner_page_url , banner_image_url , created_at , is_deleted ) VALUES ( ? , ? , ? , ? , ? )";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "isssi" , $locale_id , $banner_page_url , $banner_image_url , $this->current_time , $this->false );
                $preparedStatement->execute();
            }
            else
            {
                $query = "UPDATE banner SET banner_page_url = ? , banner_image_url = ? , updated_at = ? , is_deleted = ? WHERE banner_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sssii" , $banner_page_url , $banner_image_url , $this->current_time , $this->false , $get_banner_id );
                $preparedStatement->execute();
            }
            
            header('Content-Type: application/json');
            return json_encode(array( 'success' , 'Banner List Updated' ));
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Banner' , 'Save Banner' , $th->getMessage() );
            header('Content-Type: application/json');
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function deleteBanner( $banner_id )
    {
        header('Content-Type: application/json');
        try
        {
            if( $this->checkBannerExists( 'banner_id=' . $banner_id ) )
            {
                $query = "UPDATE banner SET deleted_at = ? , is_deleted = ? WHERE banner_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sii" , $this->current_time , $this->true , $banner_id );
                $preparedStatement->execute();
                return json_encode(array( 'success' , 'Banner Deleted' ));
            }
            else
            {
                return json_encode(array( 'error' , 'Banner ID doesnot exists' ));
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Banner' , 'Delete Banner' , $th->getMessage() );
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function getBanners( $locale_id )
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT * FROM banner WHERE locale_id = $locale_id AND is_deleted = 0 ORDER BY created_at DESC";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['banner_id'] , $row['banner_page_url'] , $row['banner_image_url'] ) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Banner' , 'Get Banners' , $th->getMessage() );
        }
    }

    public function getBanner( $banner_id )
    {
        try
        {
            $data_list_json = array();
            $query = "SELECT * FROM banner WHERE banner_id = $banner_id AND is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );
            if ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , $row['banner_page_url'] , $row['banner_image_url'] );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Banner' , 'Get Banner' , $th->getMessage() );
        }
    }
}
?>