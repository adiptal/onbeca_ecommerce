<?php
class Product
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
    
    private function checkProductExists( $str )
    {
        $query = "SELECT product_id FROM product WHERE $str" ;
        $preparedStatement = $this->connection->prepare( $query );
        $preparedStatement->execute();
		$preparedStatement->store_result();
		
		$preparedStatement->bind_result( $product_id );
		if( $preparedStatement->fetch() )
		{
            return $product_id;
        }
        else
        {
            return false;
        }
    }
    
    public function saveProduct( $sub_category_id , $product_name , $product_image_url = null , $filter_json = null , $product_id = null , $locale_id = null )
    {
        try
        {
            $product_url = strtolower(preg_replace('/[ ]/', '-', preg_replace('/[^A-Za-z0-9\- ]/', '', $product_name)));
            
            if( $product_id == '' || $product_id == null )
            {
                $get_product_id = $this->checkProductExists( 'sub_category_id = ' . $sub_category_id . ' AND product_url LIKE "' . $product_url . '"' );
            }
            else
            {
                $get_product_id = $this->checkProductExists( 'product_id = ' . $product_id );
            }

            if( $get_product_id == false )
            {
                $query = "INSERT INTO product ( sub_category_id , product_name , product_url , product_image_url , filter_json , created_at , is_deleted , user_id ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? )";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "isssssii" , $sub_category_id , $product_name , $product_url , $product_image_url , $filter_json , $this->current_time , $this->false , $_SESSION['user_id'] );
                $preparedStatement->execute();
                $get_product_id = $preparedStatement->insert_id;
            }
            else
            {
                $query = "UPDATE product SET product_name = ? , product_image_url = ? , updated_at = ? , filter_json = ? , is_deleted = ? WHERE product_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "ssssii" , $product_name , $product_image_url , $this->current_time , $filter_json , $this->false , $get_product_id );
                $preparedStatement->execute();
            }

            require_once __DIR__ . '/ProductLocation.php';
            $ProductLocation = new ProductLocation();
            $ProductLocation->manageProductLocation( $get_product_id , $locale_id );

            header('Content-Type: application/json');
            return json_encode(array( 'success' , 'Product List Updated' ));
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Product' , 'Save Product' , $th->getMessage() );
            header('Content-Type: application/json');
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function manageProductArticle( $product_id , $article_data )
    {
        try
        {
            $article_data = stripslashes($article_data);
            $query = "SELECT product_url FROM product WHERE product_id = $product_id AND is_deleted = 0" ;
            $preparedStatement = $this->connection->prepare( $query );
            $preparedStatement->execute();
            $preparedStatement->store_result();
            
            $preparedStatement->bind_result( $product_url );
            if( $preparedStatement->fetch() )
            {
                $query = "UPDATE product SET updated_at = ? , product_spec_status = ? , product_article = ? WHERE product_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sisi" , $this->current_time , $this->true , $article_data , $product_id );
                $preparedStatement->execute();
            }
            else
            {
                return false;
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Product' , 'Manage Product Article' , $th->getMessage() );
        }
    }

    public function editProductArticle( $product_id )
    {
        try
        {
            $query = "SELECT product_article FROM product WHERE product_id = $product_id AND product_spec_status = 1 AND is_deleted = 0" ;
            $preparedStatement = $this->connection->prepare( $query );
            $preparedStatement->execute();
            $preparedStatement->store_result();
            
            $preparedStatement->bind_result( $product_article );
            if( $preparedStatement->fetch() )
            {
                return $product_article;
            }
            else
            {
                return false;
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Product' , 'Edit Product Article' , $th->getMessage() );
        }
    }

    public function deleteProduct( $product_id )
    {
        header('Content-Type: application/json');
        try
        {
            if( $this->checkProductExists( 'product_id =' . $product_id ) )
            {
                $query = "UPDATE product SET deleted_at = ? , is_deleted = ? WHERE product_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "sii" , $this->current_time , $this->true , $product_id );
                $preparedStatement->execute();
                return json_encode(array( 'success' , 'Product Deleted' ));
            }
            else
            {
                return json_encode(array( 'error' , 'Product ID doesnot exists' ));
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Product' , 'Delete Product' , $th->getMessage() );
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function publishProduct( $product_id , $product_publish_status )
    {
        header('Content-Type: application/json');
        try
        {
            if( $this->checkProductExists( 'product_id =' . $product_id ) )
            {
                $query = "UPDATE product SET product_publish_status = ? WHERE product_id = ?";
                $preparedStatement = $this->connection->prepare( $query );
                $preparedStatement->bind_param( "ii" , $product_publish_status , $product_id );
                $preparedStatement->execute();
                return json_encode(array( 'success' , 'Product Approved' ));
            }
            else
            {
                return json_encode(array( 'error' , 'Product ID doesnot exists' ));
            }
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Product' , 'Publish Product' , $th->getMessage() );
            return json_encode(array( 'error' , 'Check Error Log' ));
        }
    }

    public function getProducts( $sub_category_id , $limit = null , $offset = null , $search = null )
    {
        try
        {
            $data_list_json = array();
            
            $search = "product.product_name LIKE '%" . implode( "%' AND product.product_name LIKE '%" , explode( " " , $search ) ) . "%'";

            // TOTAL COUNT
            $query = "SELECT count(*) FROM product WHERE sub_category_id = $sub_category_id AND $search AND is_deleted = 0";
            $preparedStatement = $this->connection->prepare( $query );
            $preparedStatement->execute();
            $preparedStatement->store_result();
            
            $preparedStatement->bind_result( $count );
            if( $preparedStatement->fetch() )
            {
                array_push( $data_list_json , ceil($count/$limit) , floor($offset/$limit) );
            }
            
            // PRODUCT LIST JSON

            $orderBy = '';
            if( $_SESSION['user_role_id'] != 1 )
            {
                $orderBy .= 'CASE WHEN user_id = '. $_SESSION['user_id'] .' THEN 1 END DESC ,';
            }
            $query = "SELECT * FROM product WHERE sub_category_id = $sub_category_id AND $search AND is_deleted = 0 ORDER BY $orderBy created_at DESC";
            
            if( $limit != null && $offset != null )
            {
                $query .= " LIMIT $limit OFFSET $offset";
            }
            
            $data = mysqli_query( $this->connection , $query );
            while ( $row = mysqli_fetch_assoc($data) )
            {
                if( $_SESSION['user_role_id'] == 2 )
                {
                    
                    if( $row['user_id'] == $_SESSION['user_id'] )
                    {
                        $activeToManage = 1;
                    }
                    else
                    {
                        $activeToManage = 0;
                    }
                }
                else
                {
                    $activeToManage = 1;
                }
                array_push( $data_list_json , array( $row['product_id'] , $row['product_name'] , $row['product_publish_status'] , $activeToManage ) );
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Product' , 'Get Products' , $th->getMessage() );
        }
    }

    public function getProductInfo( $product_id )
    {
        try
        {
            $data_list_json = array();
            
            $role_query = '';
            if( $_SESSION['user_role_id'] != 1 )
            {
                $role_query .= 'user_id = ' . $_SESSION['user_id'] . ' AND';
            }

            $query = "SELECT * FROM product WHERE product_id = $product_id AND $role_query is_deleted = 0 ORDER BY created_at DESC";
            $data = mysqli_query( $this->connection , $query );
            if ( $row = mysqli_fetch_assoc($data) )
            {
                array_push( $data_list_json , array( $row['product_id'] , $row['product_name'] , $row['product_image_url'] , $row['filter_json'] ) );

                require_once __DIR__ . '/ProductLocation.php';
                $ProductLocation = new ProductLocation();
                $productLocationInfo = json_decode( $ProductLocation->getProductLocations( $product_id ) , true );
                array_push( $data_list_json , $productLocationInfo );
            }
            else
            {
                $data_list_json = false;
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Product' , 'Get Product Info' , $th->getMessage() );
        }
    }

    public function getAllProducts()
    {
        try
        {
            $data_list_json = array();

            $query = "SELECT product_id , product_url , created_at FROM product WHERE is_deleted = 0 ORDER BY created_at DESC";
            $data = mysqli_query( $this->connection , $query );
            if( mysqli_num_rows($data) > 0 )
            {
                while ( $row = mysqli_fetch_assoc($data) )
                {
                    array_push( $data_list_json , array( $row['product_url'] , @date( 'c' , strtotime( $row['created_at'] ) ) ) );
                    $i = sizeof($data_list_json) - 1;
                    $data_list_json[$i][2] = array();
                    
                    $query1 = "SELECT locale.locale_id , locale.locale_name FROM product_locale , locale WHERE locale.locale_id = product_locale.locale_id AND product_locale.product_id = ". $row['product_id'] ." AND product_locale.is_deleted = 0 ORDER BY product_locale.created_at DESC";
                    $data1 = mysqli_query( $this->connection , $query1 );
                    while ( $row1 = mysqli_fetch_assoc($data1) )
                    {
                        array_push( $data_list_json[$i][2] , $row1['locale_name'] );
                    }
                }
            }
            else
            {
                $data_list_json = false;
            }
            
            header('Content-Type: application/json');
            return json_encode($data_list_json);
        }
        catch (\Throwable $th)
        {
            $this->IssueLogging->logIssue( 'Product' , 'Get All Products' , $th->getMessage() );
        }
    }
}
?>