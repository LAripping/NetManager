<?php



/*
Accounting/Administration Management:
	1.Network Utilization / traffic volume: #ofpackets per device (non routers - show ssid)
	2.Flows: IP source-dest pairs (#of packets)
	3.Service Usage: port source-dest pairs +click-resolve via IANA)
	(Tsaou only=no other has IPs ports captured)

4. (Action:) Billing: Give price (€ - slider) per MB and 
		-calculate cost (packets x size x price)
		-calculate (& highlight rows) by suggesting 'plans' 
		=> regular,silver,gold,platinum
*/



include($_SERVER['DOCUMENT_ROOT'].'/fcaps/sidebar.php');
include($_SERVER['DOCUMENT_ROOT'].'/fcaps/acc_functions.php');


//Generate html code to fill the page's content
$content = '<h3>Accounting Management</h3></br>';


/******************************** FEATURE 1 ******************************/
$feature1= "<div id=utilization style='width:450px;'>
				<p>1.Network Utilization per device (non-routers)</p>
				(measure each device's activity by means of total packets exchanged)
				<table>
			 		<tr>
			 			<th class=header>Device with MAC addr.</th>
			 			<th class=header>in WLAN</th>			 			
			 			<th class=header>Packet count</th>
			 			<th class=cost>Cost in €</th>
			 			<th class=cost>Plan</th>			 			
			 		</tr>";
			 		
# TODO Show legend

$plans=array(
   		#level => array( name, color,base_cost)
		1 => array( 'regular','brown',200 ),
		2 => array( 'silver','silver',500 ),
		3 => array( 'gold','gold',1000 ),
		4 => array( 'diamond','turquoise',10000 )
	);

$show = isset( $_POST['submit'] );
if( isset($_POST['submit']) )
	if( $_POST['cost'] )
		$euro_per_MB = $_POST['cost'];
	else
		$euro_per_MB = 0.5;
else
	$euro_per_MB = 0.5;
		

$device_packet = get_packets_each_device(); 
/*
$hw_addr => array(	
				'ssid' => $ssid
				'count'=> $count
			)	
*/
	
foreach( $device_packet as $hw_addr => $dev_attrs ){
	
	#calculate cost and plan
	$dev_cost = calculate_cost($hw_addr,$euro_per_MB);
	$dev_plan = 0;
	foreach($plans as $level => $plan_attrs){
		if($dev_cost<$plan_attrs[2]){
			$dev_plan = $level;
			break;
		}
	}
	if(! $dev_plan ) $dev_plan=4;
	
	$feature1 .= "	<tr>
						<td>$hw_addr</td>
						<td>$dev_attrs[ssid]</td>
						<td style='width:40px'; >$dev_attrs[count]</td>"
				.( $show ? "
						<td>$dev_cost</td>
						<td style= 'color:white;
									font-weight:bold;
									background-color:{$plans[$dev_plan][1]};
									text-align:center;'>
							{$plans[$dev_plan][0]}
						</td>
				" : "
						<td></td>
						<td></td>")."
					 </tr>";
}
$feature1 .= "	</table>
			 </div> 
			 </br>";		
			 
			 
/******************************** FEATURE 2 ******************************/
$feature2= "<div id=flows >
				<p>2.Flows statistics</p>
				(count packets exchanged for every possible source-dest. IP pair)
				<table>
			 		<tr>
			 			<th class=subheader>Source MAC</th>
			 			<th class=header>Source IP</th>
			 			<th class=header>Destination IP</th>			 			
			 			<th class=header>Packet count (desc.) </th>			 			
			 		</tr>";
			 		
$ip_pairs = get_ip_pairs();
/* if(src, dst ip's not null)
array( 
	[1] => array( $src_hw,$src_ip,$dst_ip,$count DESC )
	[2] => array( $src_hw,$src_ip,$dst_ip,$count DESC )
	...
)
*/		

foreach( $ip_pairs as $i ){

	$feature2 .= "	<tr>
						<td style='padding-right:10px;'>{$i[0]}</td>
						<td>{$i[1]}</td>
						<td>{$i[2]}</td>
						<td style='width:50px';>{$i[3]}</td>
					 </tr>";
}
$feature2 .= "	</table>
			 </div> 
			 </br>";
	
			
/******************************** FEATURE 3 ******************************/
$feature3 ="
			 <div id=calculate>
				<p>3.Calculate billing policy</p>
				(sum each device's cost and suggest plans)
				</br></br>
				<form action='/fcaps/acc.php' method='post'>
					Define the cost per MB: 
					<input name='cost' type='number' step='0.01' 
						min='0.01' placeholder='0.50'".
						( isset($_POST['submit']) ? "
						value=$euro_per_MB" : "" )."  
						style='width:50px;'> € </br>
					<input class='button' type='submit' 
						name='submit' value='Calculate'>
					<input class='button' type='submit' 
						onclick='location.href=$_SERVER[PHP_SELF]'
						value='Reset'>	
				</form>";
								
if( isset( $_POST['submit'] ) ){
	$feature3.="</br></br>Plans have the following cost threshold:
				<table id=plans>
					<tr>
						<th>Plan name</th>
						<th>Cost</th>
					</tr>";
					
	foreach( $plans as $level => $attrs){
		$feature3.="<tr style= 'color:white;
								font-weight:bold;
								background-color:{$attrs[1]};
								text-align:center;'>
						<td>{$attrs[0]}</td>
						<td>{$attrs[2]} €</td>
					</tr>";
	}
	$feature3.="</table>";
	
}		
		
$feature3 .="</div>";			 
			 
			 
/****************************** PUTTING IT TOGETHER **********************/		
$content .= "<table>
				<tr style='vertical-align:top;'>
					<td id=upleft rowspan=2 style='border:none;margin-left:0px;padding-right:15px;'> $feature1 </td>
					<td style='border:none;'> $feature2 </td>
				</tr>
				<tr style='vertical-align:top;'>
					
					<td style='border:none;'> $feature3 </td>
				
				</tr>
			</table>";
			

$extra_css = "<link rel='stylesheet' type='text/css' href='/fcaps/acc_styles.css'/>";
//$extra_css .= $action_hl;


include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');

?>

