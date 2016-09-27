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
	$q = "	SELECT ssid
			FROM wlan;";
	if(! $result = $conn->query($q) ) die("$conn->error");
	while( $row = $result->fetch_assoc() ){
		$wlan_attrs = array();
		array_push( $wlan_attrs, $row['ssid']);
		array_push( $wlans,$wlan_attrs );
	}
	$result->free();

	foreach($wlans as $i => $wlan_attrs){
		echo "wlan $i";
		$q = "	SELECT DISTINCT hw_address AS hw
				FROM device
				WHERE wlan_assoc='$wlan_attrs[0]'
					UNION
				SELECT DISTINCT source_hw_address AS hw
				FROM packet
				WHERE ssid='$wlan_attrs[0]';";			
		if(! $result = $conn->query($q) ) die("$conn->error");
		$cnt = 0;
		while( $row = $result->fetch_assoc() ){
			$cnt++;
		}
		$result->free();	
		$wlans[$i][2]=$cnt;
				echo "between";
		$q = "	SELECT count(*) as c
				FROM packet
				WHERE source_hw_address IN(
					SELECT DISTINCT hw_address AS hw
					FROM device
					WHERE wlan_assoc='$wlan_attrs[0]'
						UNION
					SELECT DISTINCT source_hw_address AS hw
					FROM packet
					WHERE ssid='$wlan_attrs[0]'
				);";			
		if(! $result = $conn->query($q) ) die("$conn->error");
		while( $row = $result->fetch_assoc() ){
			$wlans[$i][1]=$row['c'];
		}
		$result->free();
		echo "after";
	}
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

