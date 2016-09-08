<?php

if( isset($_POST['submit']) ){
	$filename = $_SERVER['DOCUMENT_ROOT'].'/'.$_POST['file'];
} else {
	include($_SERVER['DOCUMENT_ROOT'].'/data/sidebar.php');
	$content = "<h3>Oops!</h3>
				</br>
				<p>You shouldn't be here! Click in one of the options from
				 the sidebar to browse the corresponding page</p>";
	include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');
	exit;
}


include($_SERVER['DOCUMENT_ROOT'].'/data/parser.php');
global $parser;
include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
global $conn;


//Clear the logs
$logfile = $_SERVER['DOCUMENT_ROOT'].'/nms.log';
$progressfile = $_SERVER['DOCUMENT_ROOT'].'/progress.log';
file_put_contents($logfile,'');
file_put_contents($progressfile,'');

// Open XML capture file
if(! $fp=fopen($filename,"r") ){
    error_log("Openning file failed\n",3,$logfile);
    exit;
} else {
    error_log("Opened file $filename for parsing\n\n",3,$logfile);
}

//Initialize population variables
$count = 0;     #packets parsed


// Read data
while ($data=fread($fp,4096)) {
    if(! xml_parse( $parser,$data,feof($fp) ) ){
        $msg = "XML Error: %s at line %d\n";
        sprintf($msg,
            xml_error_string(xml_get_error_code($parser)),
            xml_get_current_line_number($parser));
        error_log("$msg,3,$logfile");
    }
}


// Free the XML parser and close DB connection
xml_parser_free($parser);
$conn->close();

include($_SERVER['DOCUMENT_ROOT'].'/data/sidebar.php');
$content ='<h3>Done!</h3>
			</br>
			<p>Take a look at <a href="/data/log.php">the log</a>
			 to see how it went...</p>';
include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');


?>
