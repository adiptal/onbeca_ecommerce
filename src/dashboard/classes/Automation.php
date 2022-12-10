<?php
ignore_user_abort(true);
ini_set( 'max_execution_time' , 0 );
class Automation
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

    public function getFinalRedirectURL( $url )
    {
        $ch = curl_init( $url );
        curl_setopt( $ch , CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $ch , CURLOPT_TIMEOUT, 10 );
        curl_exec( $ch  );
        $target = curl_getinfo( $ch , CURLINFO_EFFECTIVE_URL );
        curl_close( $ch );
        if ( $target )
        {
            return $target;
        }
        return false;
    }

    public function manageAffiliateTracking( $affiliate_id , $affiliate_url , $affiliate_pricing )
    {
        try
        {
            $link_url = $this->getFinalRedirectURL( $affiliate_url );
            
            require_once __DIR__ . '/LinkTracker.php';
            $LinkTracker = new LinkTracker();
            $link_tracker_id = $LinkTracker->saveLinkTracker( $link_url );
            
            require_once __DIR__ . '/TrackerFetcher.php';
            $TrackerFetcher = new TrackerFetcher();
            $TrackerFetcher->saveTrackerFetcher( $link_tracker_id , $affiliate_pricing );

            $query = "UPDATE affiliate SET link_tracker_id = ? WHERE affiliate_id = ?";
            $preparedStatement = $this->connection->prepare( $query );
            $preparedStatement->bind_param( "si" , $link_tracker_id , $affiliate_id );
            $preparedStatement->execute();
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Automation' , 'Manage Affiliate Tracking' , $th->getMessage() );
        }
    }

    public function refreshLinkTrackerPricing()
    {
        try
        {
            echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script><script>';
            require_once __DIR__ . '/TrackerFetcher.php';
            $TrackerFetcher = new TrackerFetcher();

            $query = "SELECT * FROM link_tracker" ;
            $data = mysqli_query( $this->connection , $query );
            $i = 0;
            while ( $row = mysqli_fetch_assoc($data) )
            {
                $i++;
                echo '
                    setTimeout( function(){
                        try {
                            $.ajax({
                                    method: "GET",
                                    url: "http://localhost/onbeca/dashboard/?pricingFetcher&link_tracker_id='. $row['link_tracker_id'] .'&link_url=" + encodeURIComponent('. '"' . $row['link_url'] .'"' .')
                            }).fail(function(response , error){
                                console.log(response);
                                console.log(error);
                            }).done(function(response){
                                if( $(response).find(".centerColAlign .a-size-medium.a-color-price").length > 0 )
                                {
                                    $("body").append("'. "<div class='page-holder'>". '"+ response +"' ."</div>" .'");
                                }
                            });
                        } catch (error) {}
                    } , '. ( 10000 * $i ) .' );';
            }
            echo '</script>';
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Automation' , 'Link Tracker Pricing' , $th->getMessage() );
        }
    }
}
?>