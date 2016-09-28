<?php

include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
global $conn; 

/******************************** FEATURE 1 ******************************/

/*
 * Params: $a = array(
 *					1 => array(
 *							1 => ...
 *							2 => ...
 *							...
 *							cols => ...
 *						)	
 *					2 => array(
 *							1 => ...
 *							2 => ...
 *							...
 *							cols => ...
 *						)
 *					...
 *			)
 *
 * Returns: $mins_maxes = array(
 *			#min row		1 => array(
 *									1 => $min of col 1
 *									2 => $min of col 2
 *									...
 *									cols => $min of col cols
 *								)
 *			#max row		2 => array(
 *									1 => $max of row 1
 *									2 => $max of row 2
 *									...
 *									cols => $max of col cols
 *
 *								)
 *							)
 *
 */
function find_mins_maxes( $a ){
	$cols = count($a[1]);
	$c = count($a);

	$mins = array(); 
	for($i=0; $i<$cols; $i++) $mins[$i] = 1000000;
	$maxes = array(); 
	for($i=0; $i<$cols; $i++) $maxes[$i] = -1;

	foreach( $a as $i => $row ){
		foreach( $row as $j => $col_val ){
			if( ($col_val < $mins[$j]) && $col_val  )	$mins[$j]  = $col_val;
			if( ($col_val > $maxes[$j]) && $col_val )	$maxes[$j] = $col_val;
		}
	}			
		
	$ret = array();
	array_push($ret,$mins);
	array_push($ret,$maxes);
	return $ret;
}


function get_http_response_times(){
	global $conn;
	
	$ret = array();
	
	$q = "	SELECT 
				DISTINCT FROM_UNIXTIME(time_captured, '%d/%m %h:%i%:%s')
					AS timestamp,
				TRUNCATE(http_response_dt*1000,2)
					AS delay_ms
			FROM packet
			WHERE http_response_dt IS NOT NULL
			ORDER BY time_captured ASC;";
			
	if(! $result = $conn->query($q) ) die("$conn->error");
	
	while( $row = $result->fetch_assoc() ){
		array_push( $ret, array($row['timestamp'],$row['delay_ms']) );
	}
	
	$result->free();	
	
	return $ret;
}



/*
array( 
	[0] => array(
			[0] => $glob_avg_rate,
			[1] => $glob_throughput_avg,
			[2] => $glob_throughput_peak_avg
	)
	
	[1] => array(
			[0] => $src_hw,
			[1] => $dst_hw, 
			[2] => $avg_rate,
			[3] => $throughput_avg,      
			[4] => $throughput_peak
	 )
	[2] => array(
			[0] => $src_hw,
			[1] => $dst_hw,
			[2] => $avg_rate,
			[3] => $throughput_avg,      
			[4] => $throughput_peak
	 )
	...
)
*/
function get_link_stats(){
	global $conn;
	
	$ret = array();
	$globals = array();
	array_push($ret,$globals);
	
	$q = "	SELECT 	source_hw_address AS src_hw,
					dest_hw_address AS dst_hw,
					AVG(rate) AS avg_rate,
					AVG(tcp_bytes_in_flight) AS throughput_avg,
					MAX(tcp_bytes_in_flight) AS throughput_peak
			FROM packet
			WHERE dest_hw_address <> 'ff:ff:ff:ff:ff:ff'
			GROUP BY source_hw_address,dest_hw_address
			HAVING throughput_avg IS NOT NULL;";
				
	if(! $result = $conn->query($q) ) die("$conn->error");

	$sum_rate = 0;
	$sum_through_avg = 0;
	$sum_through_peak = 0;	
	while( $row = $result->fetch_assoc() ){
		$sum_rate += $row['avg_rate'];
		$sum_through_avg += $row['throughput_avg'];
		$sum_through_peak += $row['throughput_peak'];
		array_push( $ret, array($row['src_hw'],
								$row['dst_hw'],
								$row['avg_rate'],
								$row['throughput_avg'],
								$row['throughput_peak']) );
	}
	
	$result->free();
	
	$ret[0][0] = $sum_rate / (count($ret)-1);
	$ret[0][1] = $sum_through_avg  / (count($ret)-1);
	$ret[0][2] = $sum_through_peak / (count($ret)-1);		
		
	return $ret;
}






function get_oldest_time(){
	global $conn;
	
	
	$q = "	SELECT FROM_UNIXTIME(
						MIN(time_captured),
						'%Y-%m-%dT%h:%i'
					) AS oldest
			FROM packet;";
					
	if(! $result = $conn->query($q) ) die("$conn->error");
	
	while( $row = $result->fetch_assoc() ){
		$ret = $row['oldest'];
	}
	
	$result->free();	
	return $ret;
}


function get_recent_time(){
	global $conn;
	
	
	$q = "	SELECT FROM_UNIXTIME(
						MAX(time_captured),
						'%Y-%m-%dT%h:%i'
					) AS recent
			FROM packet;";
					
	if(! $result = $conn->query($q) ) die("$conn->error");
	
	while( $row = $result->fetch_assoc() ){
		$ret = $row['recent'];
	}
	
	$result->free();	
	return $ret;
}


function get_traffic_in_range( $capt_start,$capt_end ){
	global $conn;
	
	$q = "	SELECT SUM(packet_size) AS s
			FROM packet
			WHERE	FROM_UNIXTIME(time_captured) >=
				 	STR_TO_DATE('$capt_start','%Y-%m-%dT%H:%i')
			AND		FROM_UNIXTIME(time_captured) <=
				 	STR_TO_DATE('$capt_end','%Y-%m-%dT%H:%i');";
				 	
	if(! $result = $conn->query($q) ) die("$conn->error");
	
	while( $row = $result->fetch_assoc() ){
		$ret = $row['s'];
	}
	
	$result->free();	
	return $ret;
}

















