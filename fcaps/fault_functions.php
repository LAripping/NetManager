<?php

include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
global $conn; 

/******************************** FEATURE 1 ******************************/

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
	
	
	
/******************************** FEATURE 2 ******************************/	
	
	
/*
 * Returns 
 *	array( 
 *		$ssid => array( 
 *					'supported_rates'	=> $supported_rates
 * 					'avg_rate'			=> $avg_rate
 *  			 )
 *	)
 *
 */ 
function get_wlans(){
	global $conn; 
	
	$q = "	SELECT wlan.ssid, supported_rates, AVG(rate) as avg_rate
		 	FROM wlan,packet 
		 	WHERE wlan.ssid=packet.ssid
		 	GROUP BY wlan.ssid;";
	
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	$ret = array();
	while( $row = $result->fetch_assoc() ){
		$ret[ $row['ssid'] ] = 
			array(	'supported_rates'=> $row['supported_rates'],
					'avg_rate' 		 => $row['avg_rate'] );
	}
	
	$result->free();
	return $ret;
}	




/*
 * Params
 *	ssid: of the wlan to retrieve info for 
 *
 * Returns 
 *	string: (e.g) '7 (70%), 9 (20%), 11(10%) '
 *
 */ 
function get_wlan_channels($ssid){
	global $conn; 
	
	# Get total # of packets for the wlan
	$q = "	SELECT COUNT(*) AS total
			FROM packet
			WHERE ssid='$ssid';";
			
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	while( $row = $result->fetch_assoc() ){
		$total = $row['total'];
	}
	$result->free();
	
	
	# Get channels used and the respective packet # 
	$q = "	SELECT DISTINCT channel, COUNT(*) AS count
		 	FROM packet 
		 	WHERE ssid='$ssid'
		 	GROUP BY channel
		 	ORDER BY count;";
	
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	$ret = "";
	while( $row = $result->fetch_assoc() ){
		$pct = round( ($row['count']/$total * 100.0),2 );
		$ret .= "$row[channel] ($pct%), ";
	}
	
	$result->free();
	return $ret;
}


	
/*
 * Params
 *	$ssid whose network the devices will be retrieved
 
 
 * Returns 
 *	array( 
 *		$hw_addr => array( 
 *						'hw_addr_res'					=> $hw_addr_res
 * 						'avg_signal_strength_this_dev'  => $...
 *  			 	)
 *	)
 *
 *	TRICK: The last key is sum, and the value after used must be unset
 *
 */ 
function get_wlan_devices( $ssid ){
	global $conn; 
	
	# get all packets whose *destination* is a device in the wlan specified
	$q = "	SELECT dest_hw_address AS hw_address, hw_addr_res, 
				AVG(signal_strength) AS avg_signal_strength_this_dev 
			FROM packet,device
			WHERE dest_hw_address=hw_address
			AND id IN(
				SELECT id
				FROM packet
				WHERE source_hw_address IN (
					SELECT hw_address
					FROM device
					WHERE wlan_assoc='$ssid'
				) OR dest_hw_address IN (
					SELECT hw_address
					FROM device
					WHERE wlan_assoc='$ssid'
				)
			) 
			AND dest_hw_address<>'ff:ff:ff:ff:ff:ff'
			GROUP BY dest_hw_address ;";
	
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	$ret1 = array();
	while( $row = $result->fetch_assoc() ){
		$ret1[ $row['hw_address'] ] = 
			array(	'hw_addr_res'
						=> $row['hw_addr_res'],
					'avg_signal_strength_this_dev' 
						=> $row['avg_signal_strength_this_dev'] );
	}
	$result->free();
	
	# get all packets whose *source* is a device in the wlan specified
	$q = "	SELECT source_hw_address AS hw_address, hw_addr_res, 
				AVG(signal_strength) AS avg_signal_strength_this_dev 
			FROM packet,device
			WHERE source_hw_address=hw_address
			AND id IN(
				SELECT id
				FROM packet
				WHERE source_hw_address IN (
					SELECT hw_address
					FROM device
					WHERE wlan_assoc='$ssid'
				) OR dest_hw_address IN (
					SELECT hw_address
					FROM device
					WHERE wlan_assoc='$ssid'
				)
			) 
			GROUP BY dest_hw_address ;";
	
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	$ret2 = array();
	while( $row = $result->fetch_assoc() ){
		$ret2[ $row['hw_address'] ] = 
			array(	'hw_addr_res'
						=> $row['hw_addr_res'],
					'avg_signal_strength_this_dev' 
						=> $row['avg_signal_strength_this_dev'] );
	}	
	$result->free();
	
	
	#manually merge the tables, to avoid duplicate hw_addres 'es
	#when that occurs, set avg_... to the average of the two avg'es
	$ret = array();
	foreach($ret1 as $hw_address1 => $attrs1){
		foreach($ret2 as $hw_address2 => $attrs2){
		
			if( $hw_address1==$hw_address2 ){
				$avg_of_avges =  $attrs1['avg_signal_strength_this_dev']
								+$attrs2['avg_signal_strength_this_dev']
								/2.0;
			
				$ret1[$hw_address1]['avg_signal_strength_this_dev']=$avg_of_avges;
				
				unset( $ret2[$hw_address2] );
			}
		}
	}
	$ret = $ret1 + $ret2;
	
	$sum = 0;
	foreach($ret as $hw_address => $attrs)
		$sum += $attrs['avg_signal_strength_this_dev'];
		
	$ret['sum']=$sum;	
	
	return $ret;
}	
	
	
	
	
