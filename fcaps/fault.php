<?php

include($_SERVER['DOCUMENT_ROOT'].'/fcaps/sidebar.php');
include($_SERVER['DOCUMENT_ROOT'].'/fcaps/fault_functions.php');


//Generate html code to fill the page's content
$content = '<h3>Fault Management</h3></br>';


/******************************** FEATURE 1 ******************************/

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


$feature1= "<div id=routers_uptime >
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
	if( $attrs['beacon_pct']<60 )
		$class = "style='color:red;$right'";
	else if( $attrs['beacon_pct']<90 )
		$class = "style='color:orange; $right'";
	else
		$class = "style='color:green; $right'";	
	
	
	$feature1 .= 	"<tr>
						<td>$hw_addr $attrs[hw_addr_res]</td>
						<td>$attrs[wlan_assoc]</td>
						<td $class >$attrs[beacon_pct] %</td>
					 <tr/>";
}
unset($attrs);
$feature1 .= "	</table>
			 </div> 
			 </br>";

/******************************** FEATURE 2 ******************************/

$wlans = get_wlans(); 
# array( ssid => array( supported_rates,avg_rate ) )

$feature2= "<div id=thresholds >
				<p>2.Data Rates and Signal Strength for wlan's devices</p>";

if( isset($_POST['submit']) ){
	$ssid = $_POST['ssid'];
	$attrs = $wlans[$ssid];	
	$wlan_devices = get_wlan_devices( $ssid );
	# array( hw_addr => hw_addr_res,avg_signal_strength_this_dev )
	
	$avg_signal_strength_all_devs =  $wlan_devices['sum'] / count($wlan_devices);	
	unset($wlan_devices['sum']);
	
	$feature2.="<table>
					<tr>
			 			<th>WLAN's SSID</th>
			 			<th colspan='2'>$ssid</th>
			 		</tr>
			 		<tr>
			 			<td rowspan='2'> </td>
			 			<td colspan='2'>$attrs[supported_rates]</td>
			 		</tr>
			 		<tr>
			 			<td>Average Rate:</td>
			 			<td>$attrs[avg_rate]</td>
			 		</tr>
			 		<tr>
			 			<th colspan='2'>Devices in WLAN (resolved)</th>
			 			<th>Average signal strength for wlan:
			 			$avg_signal_strength_all_devs</th>
			 		<tr>";
			 			
	foreach( $wlan_devices as $hw_address => $attrs ){
		$hw_addr_res = ( $attrs['hw_addr_res'] ? "($attrs[hw_addr_res])" : "" );
		
		$feature2 .="
					<tr>
						<td> </td>
						<td>$hw_address $hw_addr_res</td>
						<td>$attrs[avg_signal_strength_this_dev]</td>
					</tr>";
	}
	unset($attrs);
	$feature2.="</table>";
} else{
	$feature2.="<form action='/fcaps/fault.php' method='post'>
					Type in the WLAN's SSID</br>
					<input type='text' name='ssid' required autocomplete='on'><br>
					<input type='submit' name='submit' value='Analyze WLAN'>
				</form>";
}
$feature2.="</div>
			</br>";
					 			
							

$content = "<table>
				<tr>
					<td class=divlike> $feature1 </td>
					<td class=divlike> $feature2 </td>
				</tr>
			</table>";
			
			
$extra_css = "<link rel='stylesheet' type='text/css' href='/fcaps/fault_styles.css'/>";
include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');

# TODO the functions' implementations --left off

?>

