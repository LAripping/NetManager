<?php

include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
global $conn; 


/*
 * Returns 
 *	array( 
 *		$hw_address => array( 
 *						'hw_addr_res'	=> $hw_addr_res
 * 						'wlan_assoc'	=> $wlan_assoc
 *  				   )
 *	)
 *
 */ 
function get_routers(){
	global $conn; 
	
	$q = "	SELECT DISTINCT hw_address,hw_addr_res,wlan_assoc
		 	FROM device,wlan 
		 	WHERE device.hw_address = wlan.ap_address;";
	
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	$ret = array();
	while( $row = $result->fetch_assoc() ){
		$ret[ $row['hw_address'] ] = 
			array( 'hw_addr_res' => $row['hw_addr_res'],
					'wlan_assoc' =>	$row['wlan_assoc'] );
	}
	
	$result->free();
	return $ret;
}



/*
 *
 * Params
 *	'hw_addrress' of router
 *
 * Returns 
 * 	 beacon count of router
 *
 */ 
function get_router_beacon_count( $hw_address ){
	global $conn; 
	
	$q = "	SELECT count(*) AS c
			FROM packet,device
			WHERE type='8' AND
			hw_address=source_hw_address 
			AND	hw_address='$hw_address';";
	
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	while( $row = $result->fetch_assoc() ){
		$ret = $row['c'];
	}
	
	$result->free();
	return $ret;
}
	
	
	
/*
 *
 * Params
 *	'ap_address' of the wlan
 *
 * Returns 
 * 	 packet count of the wlan
 *
 */ 
function get_wlan_packet_count( $ap_address ){
	global $conn; 
	
	$q = "	SELECT count(*) AS c
			FROM packet,wlan
			WHERE packet.ssid = wlan.ssid
			AND wlan.ap_address='$ap_address';";
	
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	while( $row = $result->fetch_assoc() ){
		$ret = $row['c'];
	}
	
	$result->free();
	return $ret;
}	


	
/*
 *
 * Returns 
 * 	 packet count
 *
 */ 
function get_packet_count(){
	global $conn; 
	
	$q = "	SELECT count(*) AS c
			FROM packet;";
	
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	while( $row = $result->fetch_assoc() ){
		$ret = $row['c'];
	}
	
	$result->free();
	return $ret;
}
	
	
	
	
	
	
	
	
	
