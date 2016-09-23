<?php

include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
global $conn; 

/******************************** FEATURE 1 ******************************/



function get_packets_each_device(){
	global $conn;
	
	$devs = array();
	
	$q = "	SELECT hw_address,wlan_assoc
			FROM device
			WHERE is_router=0
			AND wlan_assoc IS NOT NULL;";
			
	if(! $result = $conn->query($q) ) die("$conn->error");
	
	while( $row = $result->fetch_assoc() ){
		$devs[ $row['hw_address'] ] = array('ssid' => $row['wlan_assoc']);
	}
	
	$result->free();	
	
	foreach( $devs as $hw_addr => $attrs ){
		$q = "	SELECT COUNT(id) AS count
				FROM packet WHERE
				source_hw_address = '$hw_addr' or dest_hw_address = '$hw_addr';"; 
				
		if(! $result = $conn->query($q) ) die("$conn->error");
		
		while( $row = $result->fetch_assoc() ){
			$devs[ $hw_addr ][ 'count' ] = $row['count'];
		}
		
		$result -> free();
	}
	
	return $devs;
}



function calculate_cost( $hw_addr,$euro_per_MB ){
	global $conn;
	
	$ret = '';
	
	$q = "	SELECT SUM(packet_size)*$euro_per_MB AS cost
			FROM packet WHERE
			source_hw_address = '$hw_addr' OR dest_hw_address = '$hw_addr';"; 
			
	if(! $result = $conn->query($q) ) die("$conn->error");
		
	while( $row = $result->fetch_assoc() ){		
		$ret = $row['cost'];
	}
	
	$result->free();
	return $ret;
}





$ip_pairs = get_ip_pairs();
/*TODO if(src, dst ip's not null)
array( 
	[1] => array( $src_hw, $src_ip,$dst_ip,$count DESC )
	[2] => array( $src_hw, $src_ip,$dst_ip,$count DESC )
	...
)
*/	
	
function get_ip_pairs(){
	global $conn; 
	
	$ret = array();
	
	$q = "	SELECT 	s.hw_address AS source_hw,
					s.ip_address AS source_ip, 
					d.ip_address AS dest_ip, 
					COUNT(id) AS count
		FROM packet p, device s, device d
		WHERE p.source_hw_address = s.hw_address
		AND p.dest_hw_address = d.hw_address
		AND s.ip_address IS NOT NULL
		AND d.ip_address IS NOT NULL
		AND s.ip_address != d.ip_address
		GROUP BY p.source_hw_address, p.dest_hw_address
		ORDER BY count DESC;";
			
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	while( $row = $result->fetch_assoc() ){
		$ret_row = array();
		array_push($ret_row, $row['source_hw']);
		array_push($ret_row, $row['source_ip']);
		array_push($ret_row, $row['dest_ip']);		
		array_push($ret_row, $row['count']);
				
		array_push($ret, $ret_row);
	}
	$result->free();
	
	return $ret;
}	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	



