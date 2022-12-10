<?php
class Image
{
    private $IssueLogging;
	function __construct()
	{
        set_error_handler(array($this, "customIssueLogger"));
        require_once __DIR__ . '/IssueLogging.php';
        $this->IssueLogging = new IssueLogging();
    }

    public function customIssueLogger( $errno , $errstr , $errfile , $errline )
    {
        $this->IssueLogging->logIssue( 'Image' , $errno , $errline . ' : ' . $errstr . ' in File : ' . $errfile );
    }

    function addImage()
    {
        if( isset($_FILES['file']) && $_FILES["file"]["error"] == 0 )
        {
            $file_type = $_FILES['file']['type'];
            $allowed = array("image/jpeg", "image/gif", "image/png");
            if(!in_array($file_type, $allowed))
            {
                return 'File Type Not Supported';
            }

            $date = date('mdyHis', time());
            $folder = 'includes/blogimages/';
            $path = $folder.$date.'.png';
            move_uploaded_file( $_FILES['file']['tmp_name'] , $path );
            return 'success';
        }
        else
        {
            return 'No File Selected';
        }
    }

    function getImageStack()
    {
        $string = array();
        $dirname = "includes/blogimages";
        $images = scandir($dirname);
        rsort($images);
        foreach($images as $image) {
            if( $image != '.' && $image != '..' )
            array_push($string, $image);
        }
        
		header('Content-Type: application/json');
        return json_encode($string);
    }
}
?>