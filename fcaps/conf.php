<?php

include($_SERVER['DOCUMENT_ROOT'].'/fcaps/sidebar.php');
include($_SERVER['DOCUMENT_ROOT'].'/fcaps/conf_functions.php');
include($_SERVER['DOCUMENT_ROOT'].'/fcaps/fault_functions.php');


//Generate html code to fill the page's content
$content = '<h3>Configuration Management</h3></br>';

/******************************** FEATURE 1 ******************************/
$channels = get_global_channels();
$freqs = array(
	'1' => 2412,
	'2' => 2417,
	'3' => 2422,
	'4' => 2427,
	'5' => 2432,
	'6' => 2437,
	'7' => 2442,	
	'8' => 2447,
	'9' => 2452,
	'10'=> 2457,
	'11'=> 2462,
	'12'=> 2467,
	'13'=> 2472	);	
	
$feature1= "<div id=channels >
				<p>1.Global WiFi frequency configuration</p>
				(percentage of packets captured for every channel in the EU band)
				<table>
			 		<tr>
			 			<th class=header>Channel No.</th>
			 			<th class=header>Frequency</th>			 			
			 			<th class=header style='width:279px;'> </th>
			 			<th class=header>Percentage</th>
			 		</tr>";
			 		
$ortho = array('1','6','11');
foreach( $channels as $no => $pct ){
	$width = round($pct*5);
	$feature1 .= ( in_array($no,$ortho) ? "<tr class=ortho>" : "<tr>")."
						<td>$no</td>
						<td>$freqs[$no] MHz</td>
						<td class='no_pad'>
							<div class=bar style='width:{$width}px;'>
							</div>
						</td>
						<td>$pct %</td>
					 <tr/>";
}
unset($pct);
$feature1 .= "	</table>
			 </div> 
			 </br>";		
			 
			 
/******************************** FEATURE 2 ******************************/
$protocols = get_protocols();
			
# TODO Color them like wireshark does!			
			
$feature2= "<div id=protocols >
				<p>2.Protocols discovered</p>
				(...and how often each one occurs among the total packets captured)
				<table>
			 		<tr>
			 			<th class=header>Protocol name</th>
			 			<th class=header>Occurences</th>			 			
			 			<th class=header style='width:200px;'> </th>
			 			<th class=header>Percentage</th>
			 		</tr>";
			 		
$total = array_sum( $protocols );
foreach( $protocols as $name => $count ){
	$pct = round($count / $total * 100, 2);
	$width = round($pct*6);
	
	$feature2 .= 	"<tr>
						<td>$name</td>
						<td>$count packets ($pct %)</td>
						<td class=no_pad>
							<div class=bar style='width:{$width}px;'>
							</div>
						</td>
						<td>$pct %</td>
					 <tr/>";
}
unset($pct);
$feature2 .= "	</table>
			 </div> 
			 </br>";			
			
			
/******************************** FEATURE 3 ******************************/			
/*Topology Viewer:

	Select WLAN > 
		SSID + BSSID
		List Dev's conf:
		- HW addr
		- HW addr resolved
		- IP addr
		(- style if is_router)
		Supported rates
*/	

$feature3_pre= "
			<div id=topology>
				<p>3.Topology viewer</p>
				(enlist the devices and the configuration in a wlan of your choice)";

$wlans = get_wlans();

$feature3_pre.="
				<form action='/fcaps/conf.php' method='get'>
					Select a WLAN to examine it's configuration</br>
					<select name='ssid' multiple>";
foreach( $wlans as $ssid => $attrs){
	$feature3_pre.="	<option value='$ssid'>$ssid</option>";
}

$feature3_pre.="	</select>
					</br>
					<input class='button' type='submit' name='submit' value='Analyze WLAN'>
				</form>
			</div>
		</br>";


if( isset($_POST['submit'])  ){
	$ssid = $_GET['ssid'];
	$attrs = $wlans[$ssid];	
	
	$feature3.="
			<div id=topology>
				<table>
					<tr>
			 			<th rowspan='3' colspan='3' class=header>WLAN selected</th>
			 		</tr>
			 		<tr>
			 			<td>SSID</td>
			 			<td class='ssid'> $ssid</td>
			 		</tr>
			 		<tr>
			 			<td>BSSID</td>
			 			<td class='ssid'>$attrs[bssid]</td>
			 		</tr>";
		
		$wlan_devices = get_wlan_devices( $ssid );	 	
		$count = count($wlan_devices);
		$i =1;	
		foreach( $wlan_devices as $hw_addr => $attrs ){
			$ip = get_device_ip( $hw_addr )		#TODO 
			
			$feature3.="
			 		<tr>
				 		<th rowspan=4 colspan=2> Device:</th>
						<td>IP address</td>
						<td>$ip</td>
					</tr>
					<tr>
						<td>MAC address</td>
						<td>$hw_address</td>
					</tr>
					<tr>
						<td>Resolved address</td>
						<td>$hw_addr_res</td>
					</tr>";
			$i++;	
		}
		
$feature3.="	</table>
			</div>
		</br>";	 		




			
			 
			 
			 
/****************************** PUTTING IT TOGETHER **********************/		

$content = "<table>
				<tr>
					<td style='border:none;margin-left:0px;'> $feature1 </td>
					<td style='border:none;'> $feature3_pre </td>					
				</tr>
				<tr style='height:auto;'>
					<td style='border:none;'> $feature2 </td>
					<td style='border:none;'> $feature3 </td>					
				</tr>
			</table>";
			

$extra_css = "<link rel='stylesheet' type='text/css' href='/fcaps/conf_styles.css'/>";
//$extra_css .= $action_hl;


include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');

?>

