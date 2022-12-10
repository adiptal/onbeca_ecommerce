<?php
class Backup
{
    private $IssueLogging;
    private $user           = "onbeca";
    private $password       = "admin@onbeca.com";
    private $host           = "localhost";
    private $database       = "v0d6g6w4_onbeca";
	function __construct()
	{
        set_error_handler(array($this, "customIssueLogger"));
        require_once __DIR__ . '/IssueLogging.php';
        $this->IssueLogging = new IssueLogging();

        if ( !file_exists( $_SERVER['DOCUMENT_ROOT'] . '/../backup_onbeca' ) )
        {
            mkdir( $_SERVER['DOCUMENT_ROOT'] . '/../backup_onbeca' , 0777 , true );
        }
        $this->manageOldBackup();
    }

    public function customIssueLogger( $errno , $errstr , $errfile , $errline )
    {
        $this->IssueLogging->logIssue( 'Backup' , $errno , $errline . ' : ' . $errstr . ' in File : ' . $errfile );
    }

    private function manageOldBackup()
    {
        $dirname = $_SERVER['DOCUMENT_ROOT'] . '/../backup_onbeca';
        $files = scandir($dirname);
        $now   = time();
        
        foreach ($files as $file)
        {
            if ( $file != '..' && $file != '.' )
            {
                if ( $now - filemtime( $dirname . '/' . $file) >= 60 * 60 * 24 * 30 )
                {
                    unlink( $dirname . '/' . $file);
                }
            }
        }
    }

    public function backupFiles()
    {
        $zip = new ZipArchive();
        $filename = date("F-jS-Y-His", time()) . '.zip';
        if ( 
            $zip->open( $_SERVER['DOCUMENT_ROOT'] . "/../backup_onbeca/" . $filename , ZIPARCHIVE::CREATE ) != TRUE
        )
        {
            die ("Could not open archive");
        }

        $rootPath = realpath('includes/blogimages');

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            if (!$file->isDir())
            {
                $filePath = $file->getRealPath();
                $relativePath = substr( $filePath, strpos( $filePath , 'blogimages' ) );
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();

        // MANAGE DB BACKUP
        $this->exportDatabase( $this->host , $this->user , $this->password , $this->database , false , date("F-jS-Y-his", time()) . '.sql' );
    }

    public function getFileList()
    {
        $extensions = array('zip', 'rar');
        $string = array();
        $dirname = $_SERVER['DOCUMENT_ROOT'] . '/../backup_onbeca';
        $files = scandir($dirname);
        rsort($files);
        foreach($files as $file) {
            $file_type = strtolower( pathinfo( $file , PATHINFO_EXTENSION ) );
            if( $file != '.' && $file != '..' && in_array( $file_type , $extensions ) )
            {
                array_push($string, $file);
            }
        }
        
		header('Content-Type: application/json');
        return json_encode($string);
    }

    public function restoreFiles( $fileName )
    {
        $zip = new ZipArchive;
        if ( file_exists( $_SERVER['DOCUMENT_ROOT'] . '/../backup_onbeca/' . $fileName ) )
        {
            $restore = $zip->open( $_SERVER['DOCUMENT_ROOT'] . '/../backup_onbeca/' . $fileName );
            if ($restore === TRUE)
            {
                $zip->extractTo( 'includes' );
                $zip->close();
            }
        }
    }

    public function exportDatabase( $host , $user , $pass , $name , $tables = false , $backup_name = false )
    {
        $mysqli = new mysqli( $host , $user , $pass , $name );
        $mysqli->select_db( $name );
        $mysqli->query("SET NAMES 'utf8'");

        $queryTables = $mysqli->query('SHOW TABLES');
        while( $row = $queryTables->fetch_row() )
        {
            $target_tables[] = $row[0];
        }
        if($tables !== false)
        {
            $target_tables = array_intersect( $target_tables , $tables) ;
        }
        foreach( $target_tables as $table )
        {
            $result         =   $mysqli->query('SELECT * FROM '.$table);
            $fields_amount  =   $result->field_count;
            $rows_num=$mysqli->affected_rows;
            $res            =   $mysqli->query('SHOW CREATE TABLE '.$table);
            $TableMLine     =   $res->fetch_row();
            $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";

            for ( $i = 0, $st_counter = 0 ; $i < $fields_amount ; $i++, $st_counter = 0 )
            {
                while($row = $result->fetch_row())
                {
                    if ( $st_counter%100 == 0 || $st_counter == 0 )
                    {
                            $content .= "\nINSERT INTO ".$table." VALUES";
                    }
                    $content .= "\n(";
                    for($j=0; $j<$fields_amount; $j++)
                    {
                        $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) );
                        if (isset($row[$j]))
                        {
                            $content .= '"'.$row[$j].'"' ;
                        }
                        else
                        {
                            $content .= '""';
                        }
                        if ($j<($fields_amount-1))
                        {
                                $content.= ',';
                        }
                    }
                    $content .=")";
                    
                    if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num)
                    {
                        $content .= ";";
                    }
                    else
                    {
                        $content .= ",";
                    }
                    $st_counter=$st_counter+1;
                }
            } $content .="\n\n\n";
        }
        
        $backup_name = $backup_name ? $backup_name : $name.".sql";
        $fp = fopen( $_SERVER['DOCUMENT_ROOT'] . '/../backup_onbeca/' . $backup_name , "wb" );
        fwrite( $fp , $content );
        fclose( $fp );
    }
}
?>