<?php
class ContentListing
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
        $this->IssueLogging->logIssue( 'ContentListing' , $errno , $errline . ' : ' . $errstr . ' in File : ' . $errfile );
    }
    
    private function isJson( $string )
    {
        if( strlen( urldecode( $string ) ) > 2 )
        {
            json_decode( urldecode( $string ) );
            return (json_last_error() == JSON_ERROR_NONE);
        }
        else
        {
            return false;
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

    public function createFilterListing( $param_table , $section )
    {
        $data_list_json = array();

        switch( $param_table )
        {
            case 'department' :
                    $query = "SELECT department_id FROM department WHERE department_name LIKE '$section' AND is_deleted = 0";
                    $preparedStatement = $this->connection->prepare( $query );
                    $preparedStatement->execute();
                    $preparedStatement->store_result();
                    
                    $preparedStatement->bind_result( $department_id );
                    if( $preparedStatement->fetch() )
                    {
                        $query = "SELECT category_name FROM category WHERE department_id = $department_id AND is_deleted = 0";
                        $data = mysqli_query( $this->connection , $query );
                        $data_list_json['url_links'] = [];
                        $data_list_json['url_links']['current'] = [ $section ];
                        $data_list_json['url_links']['child'] = [];
                        while ( $row = mysqli_fetch_assoc($data) )
                        {
                            array_push( $data_list_json['url_links']['child'] , $row['category_name'] );
                        }
                    }
            break;
            
            case 'category' :
                    $query = "SELECT category_id FROM category WHERE category_name LIKE '$section' AND is_deleted = 0";
                    $preparedStatement = $this->connection->prepare( $query );
                    $preparedStatement->execute();
                    $preparedStatement->store_result();
                    
                    $preparedStatement->bind_result( $category_id );
                    if( $preparedStatement->fetch() )
                    {
                        $query = "SELECT sub_category_name FROM sub_category WHERE category_id = $category_id AND is_deleted = 0";
                        $data = mysqli_query( $this->connection , $query );
                        $data_list_json['url_links'] = [];
                        $data_list_json['url_links']['current'] = [ $section ];
                        $data_list_json['url_links']['child'] = [];
                        while ( $row = mysqli_fetch_assoc($data) )
                        {
                            if ( strpos( $row['sub_category_name'] , 'Filter' ) !== false )
                            {
                                $query = "SELECT * FROM sub_category WHERE sub_category_name LIKE '". $row['sub_category_name'] ."' AND is_deleted = 0";
                                $data = mysqli_query( $this->connection , $query );
                                while ( $row = mysqli_fetch_assoc($data) )
                                {
                                    $data_list_json = [ 'sub_category' , json_decode($row['filter_json'] , true)[1] ];
                                }
                            }
                            else
                            {
                                array_push( $data_list_json['url_links']['child'] , $row['sub_category_name'] );
                            }
                        }
                    }
            break;
            
            case 'sub_category' :
                    $query = "SELECT * FROM $param_table WHERE ". $param_table ."_name LIKE '$section' AND is_deleted = 0";
                    $data = mysqli_query( $this->connection , $query );
                    while ( $row = mysqli_fetch_assoc($data) )
                    {
                        $data_list_json = json_decode($row['filter_json'] , true)[1];
                    }
            break;
        }
        
		header('Content-Type: application/json');
        return json_encode($data_list_json);
    }

    public function createSearchFilter( $section , $search , $locale_name )
    {
        $locale_id = $this->getID( 'locale' , [$locale_name , 'locale_id' , 'locale_name'] );
        $data_list_json = [];

        $data_list_json = array();
        if( $section === 'All' )
        {
            $section = '%';
        }
            
        $department_ids = [];
        $query = "SELECT department_id , department_name FROM department WHERE department_name LIKE '$section' AND is_deleted = 0";
        $data = mysqli_query( $this->connection , $query );
        while ( $row = mysqli_fetch_assoc($data) )
        {
            $department_ids[$row['department_id']] = $row['department_name'];
            $data_list_json[$row['department_name']] = [];
        }
        $department_id = implode( ',' , array_keys($department_ids) );
            
        $category_ids = [];
        $query = "SELECT department_id , category_id , category_name FROM category WHERE department_id in ( $department_id ) AND is_deleted = 0";
        $data = mysqli_query( $this->connection , $query );
        while ( $row = mysqli_fetch_assoc($data) )
        {
            array_push( $category_ids , $row['category_id'] );
            $category_ids[$row['category_id']] = $row['category_name'];
            $data_list_json[$department_ids[$row['department_id']]][$row['category_name']] = [];
        }
        $category_id = implode( ',' , array_keys($category_ids) );
        
        $search = "( product.product_name LIKE '%" . implode( "%' OR product.product_name LIKE '%" , explode( " " , $search ) ) . "%' OR product.product_article LIKE '%" . implode( "%' OR product.product_article LIKE '%" , explode( " " , $search ) ) . "%' )";
        
        $query = "SELECT DISTINCT category.department_id , sub_category.category_id , sub_category.sub_category_name FROM category , sub_category , product , product_locale WHERE product.product_id = product_locale.product_id AND product_locale.locale_id = $locale_id AND category.category_id = sub_category.category_id AND sub_category.sub_category_id = product.sub_category_id AND product.product_spec_status = 1 AND product.product_publish_status = 1 AND sub_category.category_id in ( $category_id ) AND $search AND product.is_deleted = 0 AND sub_category.is_deleted = 0";

        $data = mysqli_query( $this->connection , $query );
        while ( $row = mysqli_fetch_assoc($data) )
        {
            array_push( $data_list_json[$department_ids[$row['department_id']]][$category_ids[$row['category_id']]] , $row['sub_category_name'] );
        }

        header('Content-Type: application/json');
        return json_encode($data_list_json);
    }

    public function createContentListing( $param_table , $param_sql , $locale_name , $filter = null , $search = null , $offset = 0 , $limit = 12 )
    {
        if( $param_sql[0] === 'All' )
        {
            $param_sql[0] = '%';
        }

        $param_id = $this->getID( $param_table , $param_sql );
        $locale_id = $this->getID( 'locale' , [$locale_name , 'locale_id' , 'locale_name'] );
        switch( $param_table )
        {
            case 'department' : return $this->createDepartmentProductListing( $param_id  , $locale_id , $filter , $search , $offset , $limit );
            break;
            
            case 'category' : return $this->createCategoryProductListing( $param_id  , $locale_id , $filter , $search , $offset , $limit );
            break;
            
            case 'sub_category' : return $this->createSubCategoryProductListing( $param_id  , $locale_id , $filter , $search , $offset , $limit );
            break;
        }
    }

    public function createDepartmentProductListing( $department_id , $locale_id , $filter = null , $search = null , $offset = 0 , $limit = 12 )
    {
        if( $department_id == false || $locale_id == false )
        {
            header('Content-Type: application/json');
            return json_encode(false);
        }
        else
        {
            $category_id = array();
            $is_array = is_array( $department_id );

            if( $is_array )
            {
                $department_id = implode( ',' , $department_id );
            }
            
            $query = "SELECT * FROM category WHERE department_id in ( $department_id ) AND is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $category_id , $row['category_id'] );
            }
            
            header('Content-Type: application/json');
            return $this->createCategoryProductListing( $category_id  , $locale_id , $filter , $search , $offset , $limit );
        }
    }

    public function createCategoryProductListing( $category_id , $locale_id , $filter = null , $search = null , $offset = 0 , $limit = 12 )
    {
        if( $category_id == false || $locale_id == false )
        {
            header('Content-Type: application/json');
            return json_encode(false);
        }
        else
        {
            $sub_category_id = array();
            $is_array = is_array( $category_id );

            if( $is_array )
            {
                $category_id = implode( ',' , $category_id );
            }
            
            $query = "SELECT * FROM sub_category WHERE category_id in ( $category_id ) AND is_deleted = 0";
            $data = mysqli_query( $this->connection , $query );

            while ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $sub_category_id , $row['sub_category_id'] );
            }
            
            header('Content-Type: application/json');
            return $this->createSubCategoryProductListing( $sub_category_id  , $locale_id , $filter , $search , $offset , $limit );
        }
    }

    public function createSubCategoryProductListing( $sub_category_id , $locale_id , $filter = null , $search_data = null , $offset = 0 , $limit = 12 )
    {
        if( $sub_category_id == false || $locale_id == false )
        {
            header('Content-Type: application/json');
            return json_encode(false);
        }
        else
        {
            $data_list_json = array();
            $is_array = is_array( $sub_category_id );

            if( $is_array )
            {
                $sub_category_id = implode( ',' , $sub_category_id );
            }

            // PRODUCT SEARCH
            $search = "( product.product_name LIKE '%" . implode( "%' OR product.product_name LIKE '%" , explode( " " , $search_data ) ) . "%' ) OR ( product.product_article LIKE '%" . implode( "%' OR product.product_article LIKE '%" , explode( " " , $search_data ) ) . "%' )";

            // PRODUCT FILTER
            $filter_string = '';
            if( $filter != null && $this->isJson( $filter ) )
            {
                $filter = json_decode( urldecode( $filter ) , true );
                $filter_string .= " ( ";
                $filter_length = count( $filter );
                $i = 0;
                foreach ( $filter as $key => $values )
                {
                    $filter_string .= " ( ";
                    $values_length = count( $values );
                    $j = 0;
                    foreach ( $values as $value )
                    {
                        $regex = '"' . $key . '":\\\\["((' . $value . ')?|\\\\S[^\\\\]]+[^>]?(' . $value . ')|(' . $value . ')\\\\S[^\\\\]]+)"\\\\]';
                        $filter_string .= "product.filter_json REGEXP '$regex'";
                        
                        if ( $j < $values_length - 1 )
                        {
                            $filter_string .= " OR ";
                        }
                        $j++;
                    }
                    $filter_string .= " ) ";
                    if ( $i < $filter_length - 1 )
                    {
                        $filter_string .= " AND ";
                    }
                    $i++;
                }
                $filter_string .= " ) AND";
            }

            if( $offset == 0 )
            {
                $offset = ($offset * $limit);
            }
            else
            {
                $offset = ($offset * $limit) - $limit;
            }

            // TOTAL COUNT
            $query = "SELECT count(*) FROM product , product_locale WHERE product.product_id = product_locale.product_id AND product_locale.locale_id = $locale_id AND product.sub_category_id in ( $sub_category_id ) AND ( $search ) AND $filter_string product.product_spec_status = 1 AND product.product_publish_status = 1 AND product.is_deleted = 0 AND product_locale.is_deleted = 0";
            $preparedStatement = $this->connection->prepare( $query );
            $preparedStatement->execute();
            $preparedStatement->store_result();
            
            $preparedStatement->bind_result( $count );
            if( $preparedStatement->fetch() )
            {
                array_push( $data_list_json , ceil($count/$limit) , floor($offset/$limit) );
            }
            
            $orderBy = '';
            if( $search_data != '' )
            {
                $orderBy .= 'case';
                $last_key = 0;
                foreach (explode( " " , $search_data ) as $key => $value) {
                    $key += 1;
                    $last_key = $key;
                    $orderBy .= " when product.product_name LIKE '%$value%' then " . $key;
                }
                $orderBy .= " else ". ( $last_key + 1 ) ." end ,";
            }
            
            // PRODUCT LIST JSON
            $query = "SELECT * FROM product , product_locale WHERE product.product_id = product_locale.product_id AND product_locale.locale_id = $locale_id AND product.sub_category_id in ( $sub_category_id ) AND ( $search ) AND $filter_string product.product_spec_status = 1 AND product.product_publish_status = 1 AND product.is_deleted = 0 AND product_locale.is_deleted = 0 ORDER BY $orderBy product.created_at DESC , product_locale.user_click_count DESC";
            
            if( $limit != 0 )
            {
                $query .= " LIMIT $limit OFFSET $offset";
            }
            
            //-------------------------------------------------
            require_once __DIR__ . '/Affiliate.php';
            $Affiliate = new Affiliate();
            //-------------------------------------------------
            
            $data = mysqli_query( $this->connection , $query );
            $data_list_json['products'] = [];
            while ( $row = mysqli_fetch_assoc($data) )
            {
                $affiliateDetails = json_decode( $Affiliate->getProductBestAffiliate( $row['product_id'] , $locale_id ) , true );

                array_push( $data_list_json['products'] , array( $row['product_id'] , $row['product_name'] , $row['product_url'] , $row['product_image_url'] ) );
                
                $last_index = sizeof( $data_list_json['products'] ) - 1;
                $data_list_json['products'][$last_index] = array_merge( $data_list_json['products'][$last_index] , $affiliateDetails );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
    }
}
?>