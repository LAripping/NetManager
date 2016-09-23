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
			 			<th class=header>Router (resolved address)</th>
			 			<th class=header>WLAN's SSID</th>
			 			<th class=header>Up-time pct.</th>			 			
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
# array( ssid => array( supported_rates,avg_rate,channels_used ) )

$feature2= "<div id=thresholds >
				<p>2.Data Rates and Signal Strength for wlan's devices</p>
				(processing packets from devices associated in this wlan)";

if( isset($_POST['submit2']) || isset($_POST['submit3']) ){
	$ssid = trim($_POST['ssid']);
	$attrs = $wlans[$ssid];	
	$wlan_devices = get_wlan_devices( $ssid );
	# array( hw_addr => hw_addr_res,avg_signal_strength_this_dev )
	
	$avg_signal_strength_all_devs =  $wlan_devices['sum'] / count($wlan_devices);	
	unset($wlan_devices['sum']);
	
	$ext_sup = strstr($attrs['supported_rates'],'Ext');
	$sup = strstr($attrs['supported_rates'],'Ext',True);
	$sup_num = str_replace('Supported Rates',' ',strstr($sup,'[Mbit',True));
	$ext_sup_num = str_replace('Extended Supported Rates',' ',strstr($ext_sup,'[Mbit',True));
	$avg_rate = round($attrs['avg_rate'],2);
	$avg_sig = round($avg_signal_strength_all_devs,2).' dBm';
	
	$channels = get_wlan_channels($ssid);
	# array( $no => $pct )
	
		
	$feature2.="<table>
					<tr>
			 			<th class=header>WLAN selected</th>
			 			<td>SSID:</td>
			 			<td colspan='2' class='ssid'>$ssid</td>
			 		</tr>
			 		<tr>
			 			<td rowspan='4'> </td>
			 			<td>Supported Rates:</td>
			 			<td colspan='2' class='supp_rates'>$sup_num [Mbit/sec]</td>
			 		</tr>
			 		<tr>
				 		<td>Extended Supported Rates:</td>
			 			<td colspan='2' class='data'>$ext_sup_num [Mbit/sec]</td>
			 		</tr>
			 		<tr>
			 			<td>Average Rate:</td>
			 			<td colspan='2' class='avg_rate'>$avg_rate [Mbit/sec]</td>
			 		</tr>
			 		<tr>
				 		<td>Average signal strength for wlan:</td>
				 		<td colspan='2' class='data'>$avg_sig</td>
			 		</tr>
			 		<tr>
				 		<td></td>
				 		<th>Channels used:</th>
				 		<td>Channel Number:</td>
				 		<td>Pct. of packets:</td>
			 		</tr>";
			 		
	$count = count($channels);
	
	$non_ortho = array(2,3,4,5, 7,8,9,10, 12,13);
	
	$i=1;		 		
	foreach( $channels as $no => $pct ){		 		
		$feature2.="
					<tr>"
						.($i==1 ? "<td colspan='2' rowspan=$count></td>":"")
			 			.( in_array($no,$non_ortho) ? "
			 			<td class='non_ortho' >$no</td>" : "
			 			<td class='data-center' >$no </td>")."
			 			<td class='data-center' >$pct %</td>
			 		</tr>";
		$i++;
	}
	unset($pct);	 			
	
	$feature2.=" 	<tr>
						<td></td>
				 		<th >Devices in WLAN</th>
				 		<td>HW address: (resolved)</td>
				 		<td>Device's avg. Signal Strength</td> 
				 	</tr>";

	$lowest_ss = find_devs_with_min_ss($wlan_devices,2);
	
	$count = count($wlan_devices);
	$i=1;
	foreach( $wlan_devices as $hw_address => $attrs ){
		
		$hw_addr_res = ( $attrs['hw_addr_res'] ? "($attrs[hw_addr_res])" : "" );
		$dev_sig = round($attrs['avg_signal_strength_this_dev'],2).' dBm';
		
		$rev_i = $count-$i+1;
		
		$feature2 .="
					<tr>"
						.($i==1 ? "<td colspan='2' rowspan=$count></td>":"")."
						<td class='rev$rev_i'>$hw_address $hw_addr_res</td>"
						.( in_array($hw_address,$lowest_ss) ? "
						<td class='low'>$dev_sig</td>" : "
						<td class='data-center'>$dev_sig</td>")."
					</tr>";
		$i++;			
	}
	unset($attrs);
	$feature2.="</table>";
} else{
	$feature2.="<form action='/fcaps/fault.php' method='post'>
					Type in the WLAN's SSID</br></br>
					<input type='text' name='ssid' required autocomplete='on'></br>
					<input class='button' type='submit' name='submit2' value='Analyze WLAN'>
				</form>";
}
$feature2.="</div>
			</br>";
					 			
/******************************** FEATURE 3 ******************************/
		
$feature3= "<div id=actions >
				<p>3.Correct the faults found</p>
				(in the whole ecosystem, including all wlans and devices)";

if( isset($_POST['submit3']) ){
	$action_hl ='<style>';
	$feature3 .= '<ol></br>';

/*****		Action1)	(purple strikethrough 2 last devs hw) 
							low avg.wlan-signal stregth + many devices 
								=> "Interferance" => Reduce devices */
	define("SIG_POOR", -60);
	define("MANY_DEVS", 5);
	
	if( count($wlan_devices)>=MANY_DEVS && $avg_sig<=SIG_POOR ){
		for( $i=1; $i <= count($wlan_devices)-MANY_DEVS+1 ; $i++ ){	
			$action_hl .= "#thresholds td.rev$i{ color:purple; text-decoration:line-through;}";
		}
		
		$feature3 .= "<li style='color:purple'>
						Possible interferance in in wlan links. 
						Reduce the number of devices to increase the avg. signal strenght.
					  </li></br>";
					  
	}
	
/****		Action2)	(red bg-color the ss of 2 devs w lowest ss)
							low signal stregth 
								=>  "Increase Tx Power",
					   				"Come closer",
					   				"Place router centrally"*/					   				
		$action_hl .= '#thresholds td.low{ 
							text-align:center;
							color:black;
							background-color:crimson;
						}';
		
		$feature3 .= "<li style='color:crimson'>
						Poor signal strength noticed for the devices highlighted.
						You can try:</br>
						<ul style='list-style-type:square;'>
							<li>Increasing AP's transmitting power</li>
							<li>Place the AP in a central location</li>
							<li>Bring the devices closer to the AP</li>
						</ul>
					  </li></br>";
					  
/****		Action3)	(orange bg-color the non-ortho channel nos)
							high pct in non-ortho channels
								=> "Switch channel of flows"*/
	define("HIGH_PCT", 50);
	if( pct_in_non_ortho($channels,$non_ortho)>=HIGH_PCT ){
		$action_hl .= '#thresholds td.non_ortho{
							text-align:center;
							color:black;
							background-color:gold;
						}';
						
		$feature3 .= "<li style='color:gold'>
						'High volume of traffic flowing through overlapping wifi channels.</br>
						 Improved rates can be achieved by using the orthogonal, 
						 non-overlapping channels 1,6 and 11.
					   </li></br>";
	}

/****		Action4)	(green color,underline avg_rate, green bg-color sup.rates)
							avg.rate < avg(sup.rates)
								=> "Router is capable of providing significally improved rates!" */
	preg_match_all('!\d+!', $sup_num, $matches);
	preg_match_all('!\d+!', $ext_sup_num, $ext_matches);
	foreach($ext_matches[0] as $ext_match){
		array_push($matches[0], $ext_match);
	}
	$avg_of_supp = array_sum($matches[0]) / count($matches[0]);
	
	if( $avg_rate <= $avg_of_supp ){
		$action_hl .= '#thresholds td.avg_rate{
							color:teal;
							text-decoration:underline;
						}';
						
		$action_hl .= '#thresholds td.supp_rates{
							color:black;
							background-color:teal;
						}';
						
		$feature3 .= "<li style='color:teal'>
						'The AP is capable of providing significally improved data-rates.</br>
						 Please review it's settings.
					   </li></br>";
	}


	$feature3 .= "</ol>";
	$action_hl .='</style>';
} else{
	$feature3.="<form action='/fcaps/fault.php' method='post'>
					</br><input class='button' type='submit' name='submit3' value='Suggest Actions'>
					<input type='hidden' name='ssid' value='$_POST[ssid]'>
				</form>";
}
$feature3.="</div>
			</br>";
		
		
/****************************** PUTTING IT TOGETHER **********************/		

$content .= "<table style='margin-left:0px;'>";
if( isset($_POST['submit2'])||isset($_POST['submit3']) ) $content.="
				<tr style='height:0%;'>";
else $content.="<tr>";
			
$content .="		<td rowspan=2 style='border:none;'> $feature1 </td>
					<td style='border:none; vertical-align:top;'> $feature2 </td>
				</tr>";
if( isset($_POST['submit2'])||isset($_POST['submit3']) ) $content.="
				<tr style='height:auto;'>
					<td style='border:none; vertical-align:top;'> $feature3 </td>";
$content .=    "</tr>
			</table>";
			
			
$extra_css = "<link rel='stylesheet' type='text/css' href='/fcaps/fault_styles.css'/>";
$extra_css .= $action_hl;
include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');


?>

