<?php

include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
global $conn; 


get_protected_pct(); #[ #total, #ofPROT ] 
get_ssl_http_pct(); #[ #ofHTTP, #ofSSL ] 		
get_encryption_of_wlans();
/*
array(
	0 => [ssid,p_cnt,d_cnt,enc]
	1 => [ssid,p_cnt,d_cnt,enc]
	...
)
*/	

function get_encryption_of_wlans(){
	global $conn;

	
	$wlans = array();
	$q = "	SELECT ssid,encryption
			FROM wlan;";
	if(! $result = $conn->query($q) ) die("$conn->error");
	while( $row = $result->fetch_assoc() ){
		$wlan_attrs = array();
		$wlan_attrs['ssid'] = $row['ssid'];
		$wlan_attrs['enc'] = $row['encryption'];
		array_push( $wlans,$wlan_attrs );
	}
	$result->free();

	foreach($wlans as $i => $wlan_attrs){
		$q = "	SELECT 	COUNT(DISTINCT p.id) AS cp, 
						COUNT(DISTINCT d.hw_address) AS cd 
				FROM packet p, device d
				WHERE p.source_hw_address = d.hw_address
				AND (
					d.wlan_assoc='$wlan_attrs[ssid]'
					OR p.ssid='$wlan_attrs[ssid]'
				);";			
		if(! $result = $conn->query($q) ) die("$conn->error");
		while( $row = $result->fetch_assoc() ){
			$wlans[$i]['p_cnt']=$row['cp'];
			$wlans[$i]['d_cnt']=$row['cd'];
		}
		$result->free();
	}
	
	return $wlans;
}


function get_protected_pct(){
	global $conn;
	$ret = array();
	
	$q = "	SELECT COUNT(*) AS total
			FROM packet
			LIMIT 1;";
	if(! $result = $conn->query($q) ) die("$conn->error");
	while( $row = $result->fetch_assoc() ){
		array_push( $ret, $row['total']);
	}
	$result->free();	
	
	$q = "	SELECT COUNT(*) AS prot
			FROM packet
			WHERE unprotected=0
			LIMIT 1;";
	if(! $result = $conn->query($q) ) die("$conn->error");
	while( $row = $result->fetch_assoc() ){
		array_push( $ret, $row['prot']);
	}
	$result->free();	

	return $ret;
}


function get_ssl_http_pct(){
	global $conn;
	$ret = array();
	
	$q = "	SELECT COUNT(*) AS http
			FROM packet
			WHERE protocols LIKE '%http%'
			LIMIT 1;";
	if(! $result = $conn->query($q) ) die("$conn->error");
	while( $row = $result->fetch_assoc() ){
		array_push( $ret, $row['http']);
	}
	$result->free();	
	
	$q = "	SELECT COUNT(*) 
				AS 'ssl'
			FROM packet
			WHERE protocols 
				LIKE '%ssl%'
			LIMIT 1;";
	if(! $result = $conn->query($q) ) die("$conn->error");
	while( $row = $result->fetch_assoc() ){
		array_push( $ret, $row['ssl']);
	}
	$result->free();	

	return $ret;
}

