<?php

/*
1. wlan.encryption (listTable - highlight)
2. ssl/http (two colored bar)
3. %encrypted data packets - # (two colored bar)

 ____________
|      |    |
|      |____|
|      |    |
|______|____|


*/


include($_SERVER['DOCUMENT_ROOT'].'/fcaps/sidebar.php');
include($_SERVER['DOCUMENT_ROOT'].'/fcaps/sec_functions.php');


//Generate html code to fill the page's content
$content = '<h3>Security Management</h3></br>';


/******************************** FEATURE 1 ******************************/

/*
array(
	0 => [ssid,p_cnt,d_cnt,enc]
	1 => [ssid,p_cnt,d_cnt,enc]
	...
)
*/	
$wlan_enctypes = get_encryption_of_wlans();

$feature1= "<div id=enctypes >
				<p>1.Encryption methods</p>
				(the encryption scheme used for every wlan scanned)
				<table>
			 		<tr>
			 			<th class=header>SSID</th>
			 			<th class=header>Device Count</th>
			 			<th class=header>Total packets</th>
			 			<th class=header2>Encryption</th>
			 		</tr>";

foreach( $wlan_enctypes as $i => $attrs ){
	if( $attrs['enc']=='wep/psk' )
		$class = "style='color:red;'";
	else if( $attrs['enc']=='wpa/wpa2' )
		$class = "style='color:green;'";
	else{
		$attrs['enc'] = 'unknown';
		$class = "style=''";
	}
	
	$feature1 .= 	"<tr>
						<td>$attrs[ssid]</td>
						<td>$attrs[d_cnt]</td>
						<td>$attrs[p_cnt]</td>
						<td $class >$attrs[enc]</td>
					 <tr/>";
}
$feature1 .= "	</table>
			 </div> 
			 </br>";
			 		
/******************************** FEATURE 2 ******************************/
$l5 = get_ssl_http_pct(); #[ #ofHTTP, #ofSSL ] 	
if($l5[0]==0) 
	$pct = 0;	
else
	$pct = round($l5[1] / ($l5[0]+$l5[1]) * 100,2);

$width = round($pct*5); 
$tot_width = round(100*5);		

$feature2= "<div id=l5sec >
				<p>2.Application Layer Security</p>
				(count the fraction of HTTP packets that use the SSL protocol)
				<table>
			 		<tr>
			 			<th class=header >Packets using SSL</th>			
			 			<th class=header style='width:{$tot_width}px;'>SSL/HTTP Percentage $pct %</th>
			 			<th class=header >Packets using plain HTTP</th>
			 		</tr>
			 		<tr>
						<td>$l5[1]</td>
						<td class=no-pad>
							<div class=bar style='width:{$width}px;'></div>
						</td>
						<td style='text-align:right'>$l5[0]</td>
					 <tr/>
			 	</table>
			 </div> 
			 </br>";
			
/******************************** FEATURE 3 ******************************/
$prot = get_protected_pct(); #[ #total, #ofPROT ] 		
if($prot[0]==0)
	$pct = round($prot[1] / ($prot[0]+$prot[1]) * 100,2);
else
	$pct = 0;
	
$width = round($pct*5); 
$tot_width = round(100*5);		

$feature3= "<div id=protected >
				<p>3.Link Layer Security</p>
				(count the actually protected, wifi.data packets)
				<table>
			 		<tr>
			 			<th class=header >Protected Packets</th>			
			 			<th class=header style='width:{$tot_width}px;'>Protected Percentage $pct %</th>
			 			<th class=header >Unprotected data</th>
			 		</tr>
			 		<tr>
						<td>$prot[1]</td>
						<td class=no-pad>
							<div class=bar style='width:{$width}px;'></div>
						</td>
						<td style='text-align:right;'>$prot[0]</td>
					 <tr/>
			 	</table>
			 </div> 
			 </br>";
			 
			 
/****************************** PUTTING IT TOGETHER **********************/		
$content .= "<table>
				<tr>
					<td rowspan=2> $feature1 </td>
					<td class=allin> $feature2 </td>
				</tr>
				<tr>
					<td class=allin> $feature3 </td>
				</tr>
			</table>";
			
$extra_css = "<link rel='stylesheet' type='text/css' href='/fcaps/sec_styles.css'/>";

include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');
?>

