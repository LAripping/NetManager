<?php

/*
Perfomance Management
	Traffic load in specified time period (MBs/GBs, # ofpackets)
		1 MB = 1024000 bytes (= 1000×1024) B
		 
	per link
		avg Speeds (=rates)
		+global_device_average ( avg of avges)
		
		TCP 
			-max tcp_bytesinflight -> Throughput Peak
			-avg tcp_bytes		   -> 
		
	(gobal) http response times=f(time) graph
*/


include($_SERVER['DOCUMENT_ROOT'].'/fcaps/sidebar.php');
include($_SERVER['DOCUMENT_ROOT'].'/fcaps/perf_functions.php');


//Generate html code to fill the page's content
$content = '<h3>Performance Management</h3></br>';


/******************************** FEATURE 1 ******************************/

# time format: str"2016-09-24T15:55"

$capt_start = get_oldest_time(); 
$capt_end	= get_recent_time(); 

$global_traffic = get_traffic_in_range( $capt_start,$capt_end );
echo "traffic: $global_traffic";

if( isset($_POST['submit']) ){
	$given_start=$_POST['start'];
	$given_end	=$_POST['end'];
	$traffic = get_traffic_in_range( $given_start,$given_end );
}

#TODO convert to MB/GB : 1 MB = 1.000.000 B 

$feature1= "<div id=load>
				<form action='/fcaps/perf.php' method='post'>
					<p>1.Traffic load</p>
					(sum the size of all packets exchanged within a given time reange)
					<table class=align-left>
				 		<tr>
				 			<th>Cumulative traffic captured:</th>
				 			<td>
				 				<input type='number' readonly 
				 					   value='$global_traffic'> Bytes
				 			</td>
				 		</tr>
				 		<tr>
				 			<th>Capture start time:</th>
				 			<td>
				 				<input type='datetime-local' readonly 
				 					   value='$capt_start'>
				 			</td>
				 		</tr>	
				 		<tr>
				 			<th>Capture end time:</th>
				 			<td>
				 				<input type='datetime-local' readonly 
				 					   value='$capt_end'>
				 			</td>
				 		</tr>
				 		<tr>
				 			<th>Specify start time:</th>
				 			<td>
				 				<input type='datetime-local' required 
				 					   min='$capt_start'
				 					   max='$capt_end'>
				 			</td>
				 		</tr>						 		
				 		<tr>
				 			<th>Specify end time:</th>
				 			<td>
				 				<input type='datetime-local' required
				 					   min='$capt_start'
				 					   max='$capt_end'>
				 			</td>
						</tr>
					</table>
				</form>
			</div>";
			 		
/******************************** FEATURE 2 ******************************/
 		
$link_stats = get_link_stats();
/*
array( 
	[0] => array(
			[1] => $glob_avg_rate,
			[2] => $glob_throughput_avg,
			[3] => $glob_throughput_peak
	)
	
	[1] => array(
			[1] => $src_hw,
			[2] => $dst_hw, 
			[3] => $avg_rate,
			[4] => $throughput_avg,      
			[5] => $throughput_peak
	 )
	[2] => array(
			[1] => $src_hw,
			[2] => $dst_hw,
			[3] => $avg_rate,
			[4] => $throughput_avg,      
			[5] => $throughput_peak
	 )
	...
)
-highlight the min and max for every column
*/

$feature2= "<div id=flows >
				<p style='text-decoration:underline;'>2.Link Speeds</p>
				(measure the performance of each link)
				</br></br>
				Legend:
				<p class=legend style='background-color:red;'> Minimum value observed </p> 
				<p class=legend style='background-color:green;'> Maximum value observed</p>
				</br></br>
				<table class=align-left>
			 		<tr>
			 			<th class=subheader>Link no.</th>
			 			<th class=header>Source MAC</th>
			 			<th class=header>Destination MAC</th>			 			
			 			<th class=header>Speed - avg</th>		
			 			<th class=header2>TCP throughput - avg </th>
			 			<th class=header2>TCP throughput - peak</th>	 			
			 		</tr>
			 		<tr>
			 			<th>0</th>
			 			<th>Global avg.</th>
			 			<th>Global avg.</th>			 			
			 			<th>{$link_stats[0][0]} Mbps</th>		
			 			<th>{$link_stats[0][1]} Bytes</th>
			 			<th>{$link_stats[0][2]} Bytes</th>	 			
			 		</tr>";

unset( $link_stats[0] );

$max_style = "style='background color:green'";
$min_style = "style='background-color:red'";


$mins_maxes = find_mins_maxes($link_stats);

foreach( $link_stats as $i => $l ){
	$feature2 .= "	<tr>
						<td>$i</td>
						<td>{$l[0]}</td>
						<td>{$l[1]}</td>
						<td"
						.( $l[2]==$mins_maxes[0][2] ? " $min_style" : "")
						.( $l[2]==$mins_maxes[1][2] ? " $max_style" : "")."					
						>{$l[3]} Mbps</td>
						<td"
						.( $l[3]==$mins_maxes[0][3] ? " $min_style" : "")
						.( $l[3]==$mins_maxes[1][3] ? " $max_style" : "")."							
						>{$l[4]} Bytes</td>
						<td"
						.( $l[4]==$mins_maxes[0][3] ? " $min_style" : "")
						.( $l[4]==$mins_maxes[1][3] ? " $max_style" : "")."	
						>{$l[4]} Bytes</td>						
					 </tr>";
}
$feature2 .= "	</table>
			 </div> 
			 </br>";
			
/******************************** FEATURE 3 ******************************/
$resp_times = get_http_response_times(); 
/* array( $timestamp,$response_dt ) sorted(keys,asc) */

$timestamps = array();
$delays = array();
foreach( $resp_times as $row){
	array_push($timestamps,$row[0]);
	array_push($delays,$row[1]);
}


$min_x = min($timestamps);
$max_x = max($timestamps);
$min_y = min($delays);	#~30 ms
$max_y = max($delays);	#~600 ms
$step = 30; #ms


$feature3= "<div id=resp_dt >
				<p>3.HTTP response times</p>
				(track the variance of http response delays)

				<table class=align-left>
					<tr>";


$i=0;
foreach( $resp_times as $row){	
	if($i++>200) break;
	$feature3.="		<td class=cols>
							<table class=cols_container>
								<tbody>";
	for( $h=$min_y; ;$h+=$step ){
		$feature3 .= "				<tr>
										<td class=around_div>
											<div class=bar></div>
										</td>
									</tr>";
		if( $h>=$row[1] )	break;
	}
	$feature3.="				</tbody>
							</table>
						</td>";
}
$feature3.="		</tr>
				</table>";	 
			 
			 
/****************************** PUTTING IT TOGETHER **********************/		
$content .= "<table>
				<tr>
					<td class=allin> $feature1 </td>
				</tr>
				<tr>
					<td class=allin> $feature2 </td>
				</tr>
				<tr>
					<td class=allin colspan=2> $feature3 </td>
				</tr>
			</table>";
			
$extra_css = "<link rel='stylesheet' type='text/css' href='/fcaps/perf_styles.css'/>";

include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');
?>

