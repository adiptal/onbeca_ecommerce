<?php
ini_set( 'max_execution_time' , 0 );
class TrackerFetcher
{
    private $IssueLogging;
    private $PriceAlert;

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
        
        require_once __DIR__ . '/PriceAlert.php';
        $this->PriceAlert = new PriceAlert();
        
        set_error_handler(array($this, "customIssueLogger"));
        require_once __DIR__ . '/IssueLogging.php';
        $this->IssueLogging = new IssueLogging();
    }

    public function customIssueLogger( $errno , $errstr , $errfile , $errline )
    {
        throw new Exception($errline . ' : ' . $errstr . ' in File : ' . $errfile);
    }
    
    private function checkTrackerFetcherExists( $str )
    {
        $query = "SELECT tracker_fetcher_id FROM tracker_fetcher WHERE $str" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $tracker_fetcher_id );
		if( $preparedStatement->fetch() )
		{
            return $tracker_fetcher_id;
        }
        else
        {
            return false;
        }
    }
    
    public function saveTrackerFetcher( $link_tracker_id , $price )
    {
        try
        {
            $tracker_fetcher_id = $this->checkTrackerFetcherExists( 'link_tracker_id = ' . $link_tracker_id . ' ORDER BY created_at DESC LIMIT 1 OFFSET 0' );

            if( $tracker_fetcher_id != false )
            {
                $get_tracker_fetcher_id = $this->checkTrackerFetcherExists( 'tracker_fetcher_id = ' . $tracker_fetcher_id . ' AND price BETWEEN '. ( floor( $price ) - 2 ) . ' AND ' . ( ceil( $price ) + 2 ) );
            }
            else
            {
                $get_tracker_fetcher_id = $tracker_fetcher_id;
            }

            if( $get_tracker_fetcher_id == false )
            {
                $query = "INSERT INTO tracker_fetcher ( link_tracker_id , price , created_at ) VALUES ( ? , ? , ? )";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "iss" , $link_tracker_id , $price , $this->current_time );
                $preparedStatement->execute();
                return true;
            }
            else
            {
                return false;
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Tracker Fetcher' , 'Save Tracker Fetcher' , $th->getMessage() );
            return false;
        }
    }

    private function getCURLResponse( $url )
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:62.0) Gecko/20100101 Firefox/62.0",
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_SSL_VERIFYPEER => false
        );

        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        return $header;
    }

    public function pricingFetcher( $link_tracker_id , $link_url )
    {
        switch( $link_url )
        {
            case (preg_match('/^(http|https):\/\/www.amazon\..*?$/',  $link_url) ? true : false) :
                echo $this->getCURLResponse( $link_url )['content'];
                echo '
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
                <script>
                    $(".centerColAlign .a-size-medium.a-color-price span").remove();
                    try {
                        $.ajax({
                            method: "GET",
                            url: "http://localhost/onbeca/dashboard/?saveTrackerFetcher&link_tracker_id='. $link_tracker_id .'&price=" + encodeURIComponent($(".centerColAlign .a-size-medium.a-color-price").html().replace( /[^0-9\.]/ , "" ))
                        });
                    } catch (error) {}
                    finally{
                        $(".centerColAlign .a-size-medium.a-color-price").closest(".page-holder").remove();
                    }
                </script>';
            break;

            case (preg_match('/^(http|https):\/\/www.bestbuy\..*?$/',  $link_url) ? true : false) :
                $url_unique_id = explode( '/' , $link_url );
                
                $url = 'https://api.bestbuy.com/v1/products(sku='. end( $url_unique_id ) .')?apiKey=immhjeoR5vBA4Y0WwsBEKzGa&format=json';
                $response = $this->getCURLResponse( $url );
                
                $response = json_decode( $response['content'] , true );
                if( !empty( $response['products'] ) )
                {
                    $price = $response['products'][0]['salePrice'];
                    if( $this->saveTrackerFetcher( $link_tracker_id , $price ) )
                    {
                        $this->affiliatePricingUpdate( $link_tracker_id , $price );
                    }
                }
                else
                {
                    $this->disableAffiliate( $link_tracker_id );
                }
            break;

            case (preg_match('/^(http|https):\/\/www.walmart\..*?$/',  $link_url) ? true : false) :
                echo 'Wallmart<br/>';
            break;

            default : echo $link_url . '<br/>';
        }
    }
    
    public function affiliatePricingUpdate( $link_tracker_id , $price )
    {
        try
        {
            $query = "SELECT * FROM affiliate , product , locale WHERE locale.locale_id = affiliate.locale_id AND product.product_id = affiliate.product_id AND affiliate.link_tracker_id = $link_tracker_id" ;
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                $this->PriceAlert->AlertPriceUpdateToUsers( $row['affiliate_id'] , 'http://localhost/onbeca/' . $row['locale_name'] . '/' . $row['product_url'] . '/' , $row['affiliate_company'] , $row['product_name'] , $row['affiliate_pricing'] , $price , $row['locale_currency'] );
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Tracker Fetcher' , 'Alert Price Update To Users' , $th->getMessage() );
        }
        finally
        {
            try
            {
                $query = "UPDATE affiliate SET affiliate_pricing = ? WHERE link_tracker_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "si" , $price , $link_tracker_id );
                $preparedStatement->execute();
            }
            catch (\Throwable $th)
            {
                $this->IssueLogging->logIssue( 'Tracker Fetcher' , 'Affiliate Pricing Update' , $th->getMessage() );
            }
        }
    }

    private function disableAffiliate( $link_tracker_id )
    {
        try
        {
            $query = "UPDATE affiliate SET deleted_at = ? , is_deleted = ? , link_tracker_id = 0 , affiliate_pricing = 0 , affiliate_url = '' WHERE link_tracker_id = ?";
            $preparedStatement = $this->connection->prepare( $query );
            $preparedStatement->bind_param( "sii" , $this->current_time , $this->true , $link_tracker_id );
            $preparedStatement->execute();
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Tracker Fetcher' , 'Disable Affiliate' , $th->getMessage() );
        }
    }

    public function getProductPricingHistory( $link_tracker_ids )
    {
        try
        {
            $common_dates = array();
            $data_list_json = array();
            
            // FETCH COMMON DATES
            $query = "SELECT DISTINCT (created_at) FROM tracker_fetcher WHERE link_tracker_id in ( $link_tracker_ids ) ORDER BY created_at ASC" ;
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $common_dates , strtotime( $row['created_at'] ) );
            }

            // CREATE PRODUCT HISTORY
            $query = "SELECT * FROM tracker_fetcher WHERE link_tracker_id in ( $link_tracker_ids ) ORDER BY created_at ASC" ;
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                if( !isset( $data_list_json[ $row['link_tracker_id'] ] ) )
                {
                    $data_list_json[ $row['link_tracker_id'] ] = [];
                }

                // SETTING PREVIOUS PRICE SO IF CURRENT NOT AVAILABLE THEN BACKUP
                foreach ( $common_dates as $key => $value )
                {
                    if( $value >= strtotime( $row['created_at'] ) )
                    {
                        $data_list_json[ $row['link_tracker_id'] ][ $value ] = round( $row['price'] );
                    }
                }
            }

            // REINDEXING JSON DATA
            foreach ($data_list_json as $key => $value)
            {
                $data_list_json[ $key ] = array_values( $data_list_json[ $key ] );
            }

            // MERGING COMMON DATES INTO PROCESSED DATA
            $processed_data = array();
            foreach ($common_dates as $key => $value)
            {
                array_push( $processed_data , [ date( 'dS F', $value ) ] );
            }

            // MERGING CHART DATA INTO PROCESSED DATA
            foreach ($data_list_json as $key => $value)
            {

                foreach ($data_list_json[ $key ] as $key => $value)
                {
                    array_push( $processed_data[ $key ] , $value );
                }
            }
            
            // FETCHING TRACKER ID KEYS
            $link_tracker_id_keys = [];
            foreach ($data_list_json as $key => $value)
            {
                array_push( $link_tracker_id_keys , $key );
            }

            // CREATING RESPONSE DATA
            $data_list_json = [];
            $data_list_json[0] = [];
            $data_list_json[0] = array_merge( $data_list_json[0] , $link_tracker_id_keys );
            $data_list_json['chart'] = [];
            $data_list_json['chart'] = array_merge( $data_list_json['chart'] , $processed_data );
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Tracker Fetcher' , 'Get Product Pricing History' , $th->getMessage() );
        }
    }
}
?>