<?php

include($_SERVER['DOCUMENT_ROOT'].'/data/sidebar.php');
include($_SERVER['DOCUMENT_ROOT'].'/data/parser.php');
global $parser;
include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
global $conn;

//Clear the log
$logfile = $_SERVER['DOCUMENT_ROOT'].'/nms.log';
file_put_contents($logfile,'');





// Open XML capture file
$filename = $_SERVER['DOCUMENT_ROOT'].'/afull.xml';
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

$content = "";

include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');

?>
