<?php

//include($_SERVER['DOCUMENT_ROOT'].'/wifi/sidebar.php');
$no_sidebar = TRUE;




//Get a connection object ( $conn )
include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');

//Clear the log
$logfile = $_SERVER['DOCUMENT_ROOT'].'/nms.log';
file_put_contents($logfile,'');

// Initialize the XML parser
if(! $parser=xml_parser_create() ){
    error_log("Parse creation failed\n",3,$logfile);
    exit;
}

//Initialize population variables
$count = 0;     #packets parsed

// Function to use at the start of an element
function start($parser,$element_name,$element_attrs) {
    global $count,$logfile;
    static $protocols, $i, $fields;

    #TODO avoid repopulations (effectively = reduce parser callbacks)


    switch($element_name) {
        case "PACKET":
            //Increase population counter
            $count++;
            error_log("Parsing packet #$count...\n",3,$logfile);

            //Clear packet variables
            $i = 0;
            $protocols = array();
            $fields = array(); #keys are the column names
            break;

        case "PROTO":
            //Add protocol to packet's array
            $proto_name = $element_attrs['NAME'];
            $protocols[$i] = $proto_name;
            $i++;

            error_log("-Includes protocol: $proto_name (#$i)\n",3,$logfile);
            break;

        case "FIELD":
            $field_name = $element_attrs['NAME'];

            switch($field_name){
                //GENINFO fileds
                case "timestamp":
                    $fields['time_captured'] = $element_attrs['VALUE'];
                    error_log("--captured at $fields['time_captured']\n",3,$logfile);
                    break;

                case "num":
                    $fields['num'] = $element_attrs['VALUE'];
                    error_log("--packet num: $fields['num']\n",3,$logfile);
                    break;

                //WLAN fields
                case "wlan_radio.signal_dbm":
                    $fields['signal_strength'] = $element_attrs['SHOW'];
                    error_log("--signal strength: $fields['signal_strength']\n",3,$logfile);
                    break;

                case "wlan_radio.data_rate":
                    $fields['rate'] = $element_attrs['SHOW'];
                    error_log("--data rate: $fields['rate']\n",3,$logfile);
                    break;

                case "wlan_radio.channel":
                    $fields['channel'] = $element_attrs['SHOW'];
                    error_log("--channel: $fields['channel']\n",3,$logfile);
                    break;

                case "wlan.fc.type_subtype":
                    $fields['type'] = $element_attrs['SHOW'];
                    error_log("--is type: $fields['type']\n",3,$logfile);
                    break;

                case "wlan.sa":
                    $fields['source_hw_address'] = $element_attrs['SHOW'];
                    error_log("--source hw addr: $fields['source_hw_address']\n",3,$logfile);
                    break;

                case "wlan.da":
                    $fields['dest_hw_address'] = $element_attrs['SHOW'];
                    error_log("--destination hw addr: $fields['dest_hw_address']\n",3,$logfile);
                    break;

                case "wlan_mgt.ssid":
                    $fields['ssid'] = $element_attrs['SHOW'];
                    error_log("--belongs in wlan with ssid: $fields['ssid']\n",3,$logfile);
                    break;

                case "wlan.bssid":
                    $fields['bssid'] = $element_attrs['SHOW'];
                    error_log("--belongs in wlan with bssid: $fields['bssid']\n",3,$logfile);
                    break;

                case "wlan_mgt.supported_rates":
                    if( array_key_exists('_supported_rates', $fields) ){
                        $fields['_supported_rates'] .= $element_attrs['SHOW'];
                    } else {
                        $fields['_supported_rates'] = $element_attrs['SHOW'];
                    }
                    error_log("--rates supported (up to now): $fields[_supported_rates]\n",3,$logfile);
                    break;

                case "wlan.fc.protected": #also check the wlan.fc.type above
                                          # to fill the unprotected column
                                          # according to the comment
                    $fields['_unprotected'] = $element_attrs['SHOW'];
                    error_log("--has protected flag: $fields['_unprotected']\n",3,$logfile);
                    break;

                //TCP fields
                case "tcp.srcport":
                    $fields['src_port'] = $element_attrs['SHOW'];
                    error_log("--source tcp port: $fields['src_port']\n",3,$logfile);
                    break;

                case "tcp.dstport":
                    $fields['dst_port'] = $element_attrs['SHOW'];
                    error_log("--destination tcp port: $fields['dst_port']\n",3,$logfile);
                    break;

                case "tcp.window_size":
                    $fields['tcp_window_size'] = $element_attrs['SHOW'];
                    error_log("--tcp window size: $fields['tcp_window_size']\n",3,$logfile);
                    break;

                case "tcp.analysis.lost_segment":
                    $fields['tcp_lost_prev_segment'] = True;
                    error_log("--detected lost previous segment",3,$logfile);
                    break;

                //HTTP fields
                case "http.time":
                    $fields['http_response_dt'] = $element_attrs['SHOW'];
                    error_log("--http response time: $fields['http_response_dt']\n",3,$logfile);
                    break;

                //ETHERNET fields
                case "eth.src_resolved":
                    $fields['DEVICE_SRC__hw_addr_res'] = $element_attrs['SHOW'];
                    error_log("--source ethernet address resolved: $fields['DEVICE_SRC__hw_addr_res']\n",3,$logfile);
                    break;

                case "eth.dst_resolved":
                    $fields['DEVICE_DST__hw_addr_res'] = $element_attrs['SHOW'];
                    error_log("--destination ethernet address resolved: $fields['DEVICE_DST__hw_addr_res']\n",3,$logfile);
                    break;

                //IP fields
                case "ip.src":
                    $fields['DEVICE_SRC__ip_address'] = $element_attrs['SHOW'];
                    error_log("--source ip address: $fields['DEVICE_SRC__ip_address']\n",3,$logfile);
                    break;

                case "ip.dst":
                    $fields['DEVICE_DST__ip_address'] = $element_attrs['SHOW'];
                    error_log("--destination ip address: $fields['DEVICE_DST__ip_address']\n",$logfile);
                    break;

            }       #switch field_name
            break;

    } #switch element_name
}

// Function to use at the end of an element
function stop($parser,$element_name) {
    global $content,$count,$logfile;
    switch($element_name){
        case "PACKET":
            //Make insertions (and casts needed)

            //Create FK tuples
            #$connn...

            //Mark the end of packet processing in log
            error_log("\n",3,$logfile);
            break;
    }
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
