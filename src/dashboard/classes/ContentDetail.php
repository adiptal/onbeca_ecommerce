<?php
class ContentDetail
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
        $this->IssueLogging->logIssue( 'ContentDetail' , $errno , $errline . ' : ' . $errstr . ' in File : ' . $errfile );
    }

    public function addClickCount( $product_url , $locale_name )
    {
        $product_id = $this->getID( 'product' , [$product_url , 'product_id' , 'product_url'] );
        $locale_id = $this->getID( 'locale' , [$locale_name , 'locale_id' , 'locale_name'] );

        if( !isset( $_SESSION['addedClick-' . $product_id . $locale_id] ) )
        {
            $_SESSION['addedClick-' . $product_id . $locale_id] = false;
        }

        if( !$_SESSION['addedClick-' . $product_id . $locale_id] )
        {
            $query = "UPDATE product_locale SET user_click_count = user_click_count + 1 WHERE product_id = ? AND locale_id = ?";
            $preparedStatement = $this->connection->prepare( $query );
            $preparedStatement->bind_param( "ii" , $product_id , $locale_id );
            $preparedStatement->execute();

            $_SESSION['addedClick-' . $product_id . $locale_id] = true;
        }
    }

    public function getID( $param_table , $param_sql )
    {
        $query = "SELECT $param_sql[1] FROM $param_table WHERE $param_sql[2] LIKE '$param_sql[0]' AND is_deleted = 0" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $param_sql[1] );
		if( $preparedStatement->fetch() )
		{
            return $param_sql[1];
        }
        else
        {
            return false;
        }
    }

    public function createProductDetails( $product_url , $locale_name )
    {
        $this->addClickCount( $product_url , $locale_name );
        
        $product_id = $this->getID( 'product' , [$product_url , 'product_id' , 'product_url'] );
        $locale_id = $this->getID( 'locale' , [$locale_name , 'locale_id' , 'locale_name'] );
		$data_list_json = array();

		$query = "SELECT product.product_name , product.product_image_url , product.filter_json , product.product_article FROM product , product_locale WHERE product.product_id = product_locale.product_id AND product_locale.locale_id = $locale_id AND product.product_id = $product_id AND product.is_deleted = 0 AND product_locale.is_deleted = 0 ORDER BY product.created_at DESC";
		$data = mysqli_query( $this->connection , $query );
		while ( $row = mysqli_fetch_assoc($data) )
		{
			array_push( $data_list_json , $row['product_name'] , $row['product_image_url'] , $row['filter_json'] , $row['product_article'] );
        }

        require_once __DIR__ . '/Affiliate.php';
        $Affiliate = new Affiliate();
        $affiliateDetails = json_decode( $Affiliate->getAffiliates( $product_id , $locale_id ) , true );
        
        $data_list_json['affiliate'] = [];
        $data_list_json['affiliate'] = array_merge( $data_list_json['affiliate'] , $affiliateDetails );
        
        $query = "SELECT sub_category.sub_category_name , category.category_name , department.department_name FROM product , sub_category , category , department WHERE product.sub_category_id = sub_category.sub_category_id AND sub_category.category_id = category.category_id AND category.department_id = department.department_id AND product.product_id = $product_id AND product.is_deleted = 0 AND sub_category.is_deleted = 0 AND category.is_deleted = 0 AND department.is_deleted = 0";
        $data = mysqli_query( $this->connection , $query );
        while ( $row = mysqli_fetch_assoc($data) )
        {
            $data_list_json['breadcrump'] = [ $row['department_name'] , $row['category_name'] , $row['sub_category_name'] ];
        }
        
		header('Content-Type: application/json');
        return json_encode($data_list_json);
    }

    public function createdRelatedProductListing( $product_url , $locale_name , $filter_json )
    {
        $filter_json = urldecode($filter_json);
        $product_id = $this->getID( 'product' , [$product_url , 'product_id' , 'product_url'] );
        $sub_category_id = $this->getID( 'product' , [$product_url , 'sub_category_id' , 'product_url'] );
        $locale_id = $this->getID( 'locale' , [$locale_name , 'locale_id' , 'locale_name'] );
		$data_list_json = array();

        $query = "SELECT product.product_id , product.product_name , product.product_image_url , product.product_url FROM product , product_locale WHERE product.product_id = product_locale.product_id AND product_locale.locale_id = $locale_id AND product.product_spec_status = 1 AND product.sub_category_id = $sub_category_id AND product.product_id != $product_id AND product.filter_json LIKE $filter_json AND product.is_deleted = 0 AND product_locale.is_deleted = 0 ORDER BY product.created_at DESC LIMIT 5 OFFSET 0";
        $data = mysqli_query( $this->connection , $query );

		while ( $row = mysqli_fetch_assoc($data) )
		{
			array_push( $data_list_json , array($row['product_id'] , $row['product_name'] , $row['product_url'] , $row['product_image_url']) );
        }

		header('Content-Type: application/json');
        return json_encode($data_list_json);
    }

    public function getProductImageUrl( $locale_name , $product_url )
    {
        $locale_id = $this->getID( 'locale' , [$locale_name , 'locale_id' , 'locale_name'] );

        $data_list_json = array();
        $query = "SELECT product.product_name , product.product_image_url FROM product , product_locale WHERE product.product_id = product_locale.product_id AND product_locale.locale_id = $locale_id AND product.product_spec_status = 1 AND product.product_publish_status = 1 AND product.product_url LIKE '$product_url' AND product.is_deleted = 0" ;
        $data = mysqli_query( $this->connection , $query );
        if( $row = mysqli_fetch_assoc($data) )
        {
            array_push( $data_list_json , $row['product_name'] , $row['product_image_url'] );
        }
        else{
            $data_list_json = false;
        }
        
		header('Content-Type: application/json');
        return json_encode($data_list_json);
    }
}
?>