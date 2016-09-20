<?php

include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
global $conn; 

/******************************** FEATURE 1 ******************************/

/*
 *
 * Returns 
 *	array( $channel => $pct )
 *
 */ 
function get_global_channels(){
	global $conn; 
	
	# Get total # of packets 
	$q = "	SELECT COUNT(*) AS total
			FROM packet
			WHERE channel IS NOT NULL;";
			
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	while( $row = $result->fetch_assoc() ){
		$total = $row['total'];
	}
	$result->free();
	
	
	# Get channels used and the respective packet # 
	$q = "	SELECT DISTINCT channel, COUNT(*) AS count
		 	FROM packet 
		 	WHERE channel IS NOT NULL
		 	GROUP BY channel
		 	ORDER BY channel ASC;";
	
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	$ret = array();
	while( $row = $result->fetch_assoc() ){
		$pct = round( ($row['count']/$total * 100.0),2 );
		$ret[ $row['channel'] ] = $pct;
	}
	
	$result->free();
	return $ret;
}



/*
 *
 * Returns 
 *	array( $protocol => $count )
 *
 */ 
function get_protocols(){
	global $conn; 
	
	$protocols = array();
	
	# Get all different protocols in an array 
	$q = "	SELECT DISTINCT protocols
			FROM packet;";
			
	if(! $result = $conn->query($q) )	die("$conn->error");
	
	while( $row = $result->fetch_assoc() ){
		$protocols_joined = $row['protocols'];
		$proto_array = explode(':',$protocols_joined);
		foreach( $proto_array as $proto ){
			if(! array_key_exists($proto,$protocols) )
				array_push($protocols, $proto );
		}
	}
	$result->free();
	
	$ret = array();
	
	# Count appearances of each protocol found
	foreach( $protocols as $proto ){
		$q = "	SELECT COUNT(*) AS count
				FROM packet
				WHERE protocols LIKE '%$proto%';";

		if(! $result = $conn->query($q) )	die("$conn->error");	

		while( $row = $result->fetch_assoc() ){
			$count = $row['count'];
		}
		
		$ret[ $proto ] = $count;
	
		$result->free();
	}
	return $ret;
}




