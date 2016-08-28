<?php

///////////////DEMO UNPREPARED SELECT////////////////////////////////////


//include($_SERVER['DOCUMENT_ROOT'].'/wifi/sidebar.php');
$no_sidebar = TRUE;

include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
#Returns an instanciated connection object ( $conn )


$content = '<p> UNPREPARED SELECT </br>';

$stmt = "SELECT * FROM packets";
$result = $conn->query($stmt);

if ($result == TRUE && $result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $content .= "id: " . $row["id"] . "</br>";
    }
} else {
    $content .= "0 results";
}


$content .= '</p></br>';

///////////////DEMO PARSE////////////////////////////////////

$logfile = $_SERVER['DOCUMENT_ROOT'].'/nms.log';
file_put_contents($logfile, ""); #Clear log

$content = '<p> 5 PACKETS </br>';
$count = 1;

// Initialize the XML parser
if(! $parser=xml_parser_create() ){
    error_log("Parse creation failed\n",3,$logfile);
    exit;
}


// Function to use at the start of an element
function start($parser,$element_name,$element_attrs) {
    global $count,$logfile, $content;
    $attrs = var_export($element_attrs,true);

    switch($element_name) {
        case "PACKET":
            $content  .= "</br><h5>Packet $count</h5>";
            break;
        case "PROTO":
            $proto_name = $element_attrs['NAME'];
            $proto_showname = $element_attrs['SHOWNAME'];

            $content .= "Protocol: $proto_name ($proto_showname) </br>";
            break;
    }
    error_log("found $element_name - attrs: $attrs\n",3,$logfile);
}

// Function to use at the end of an element
function stop($parser,$element_name) {
    global $content,$count,$logfile;
    switch($element_name){
        case "PACKET":
            $count++;
            break;
    }
    error_log("stopped $element_name\n",3,$logfile);
}

// (NOT USED) Function to use when finding character data
function char($parser,$data) {
    global $content,$logfile;
    error_log("data: $data\n",3,$logfile);
    $content .= $data;
}

// Specify element handler
if(! xml_set_element_handler($parser,"start","stop") ){
    error_log("Element Handler setting failed\n",3,$logfile);
    exit;
}

// Specify data handler
//if(! xml_set_character_data_handler($parser,"char") ){
//    error_log("Data Handler setting failed\n",3,$logfile);
//    exit;
//}

// Open XML file
$filename = $_SERVER['DOCUMENT_ROOT'].'/afull.xml';
if(! $fp=fopen($filename,"r") ){
    error_log("Openning file failed\n");
    exit;
}

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

// Free the XML parser
xml_parser_free($parser);

$content .= "</p>";

include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');

?>
