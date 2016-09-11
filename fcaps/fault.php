<?php

include($_SERVER['DOCUMENT_ROOT'].'/fcaps/sidebar.php');
include($_SERVER['DOCUMENT_ROOT'].'/fcaps/fault_functions.php');


//Generate html code to fill the page's content
$content = '<h3>Fault Management</h3></br>';


$routers = get_routers(); 
# array( hw_address => array( hw_addr_res,wlan_assoc ) )

foreach( $routers as $r => $attrs ){
	$count_online_pkts = get_router_beacon_count( $r );
//	$count_total_pkts = get_router_count( $r );
	$count_total_pkts = get_wlan_packet_count( $r );
	$beacon_pct = $count_online_pkts / $count_total_pkts; #must be float
	
	$at_wlan = $attrs['wlan_assoc'];
	$at_res = $attrs['hw_addr_res'];
	$new_attrs = array();
	$new_attrs['hw_addr_res'] = ( $at_res ? "($at_res)" : "" );
	$new_attrs['wlan_assoc'] = $at_wlan;
	$new_attrs['beacon_pct'] = round($beacon_pct*100.0,2);
	$routers[$r] = $new_attrs;
}

# array( hw_address => ( hw_addr_res,wlan_assoc,beacon_pct ) )
unset($attrs);


$content .= "<div id=routers_uptime >
			 	<p>1.Percentage of time each router was online</p>
				(# of beacons from the wlan's router / # of packets in this wlan)
				<table>
			 		<tr>
			 			<th>Router (resolved address)</th>
			 			<th>WLAN's SSID</th>
			 			<th>Up-time pct.</th>			 			
			 		</tr>";
			 		
foreach( $routers as $hw_addr => $attrs ){
	$right = 'text-align:right;';
	if( $attrs[beacon_pct]<60 )
		$class = "style='color:red;$right'";
	else if( $attrs[beacon_pct]<90 )
		$class = "style='color:orange; $right'";
	else
		$class = "style='color:green; $right'";	
	
	
	$content .= 	"<tr>
						<td>$hw_addr $attrs[hw_addr_res]</td>
						<td>$attrs[wlan_assoc]</td>
						<td $class >$attrs[beacon_pct] %</td>
					 <tr/>";
}
unset($attrs);
$content .= "	</table>
			 </div> </br>";
			 
	
$extra_css = "<link rel='stylesheet' type='text/css' href='/fcaps/fault_styles.css'/>";
include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');


?>

