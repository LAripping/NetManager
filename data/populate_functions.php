<?php

include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
global $conn;

 




function check_if_packet_exists( $time,$count ){
	global $conn,$logfile;

	$q_select = $conn->prepare("SELECT id
                                FROM packet
                                WHERE time_captured = binary ?");
    $q_select->bind_param('s',$time);
    if(! $q_select->execute() ){
        error_log("ERROR: Couldn't execute select statement 
            for packet #$count.
            The error reported is '$q_select->error'.
            Skipping Packet\n\n",3,$logfile);
        return null;
    }
    $q_select->bind_result($found_id);
    $q_select->fetch();
	$q_select->close();
    
	if($found_id){
        error_log("Packet already in DB (id=$found_id)
            Skipping\n\n",3,$logfile);
        return True;
    } else {
		return False;
	}
}



function insert_geninfo( $fields,$count ){
	global $conn,$logfile;
			
	$q_insert = $conn->prepare("INSERT INTO packet(time_captured,
												   num,
												   packet_size)
                                VALUES(?, ?, ?)");
    $q_insert->bind_param('sii',$fields['time_captured'],
                                $fields['num'],
                                $fields['packet_size']);

    if(! $q_insert->execute() ){
        error_log("ERROR: Couldn't execute insert statement 
        	for packet #$count,
            The error reported is '$q_insert->error'.
            Skipping Packet\n\n",3,$logfile);
        return null;
    }
    $in_id= $q_insert->insert_id;
    $q_insert->close();
	return True;
}



function insert_packet( $fields,$count ){
	global $conn,$logfile;

	if(array_key_exists('_unprotected',$fields)
       && $fields['type']=='2'){
	    $fields['uprotected']='1';
    } else{
        $fields['unprotected']='0';
    }

    $q_multy = '';
    $q_update = "UPDATE packet SET %s=%s WHERE id=$in_id;";

    $non_packet = array('bssid','supported_rates', 'encryption');
    foreach($fields as $key => $value){
        if( in_array($key,$non_packet) || strstr($key,'DEVICE')){
            error_log("Skipping update for field $key...\n",3,$logfile);
            continue;
        }
        $q_multy .= sprintf($q_update, $key,$value);
    }

    error_log("About to perform multiquery:\n$q_multy\n",3,$logfile);
    if(! $conn->multi_query($q_multy) ){
        error_log("ERROR: Couldn't execute multi-query for packet
            #$count. Skipping Packet\n\n",3,$logfile);
        return null;
    }
	return True;
}



function insert_protocol( $fields,$packet ){
	global $conn, $logfile;
	
	$q_insert_p = $conn->prepare("INSERT INTO packet_has_protocol
                                  (packet_id,protocol_name)
                                  VALUES($in_id,?)");
    foreach($protocols as $i => $name){
        $q_insert_p->bind_param('s',$name);
        if(! $q_insert->execute() ){
            error_log("ERROR: Couldn't execute insert statement for
		               packet_has_protocol, packet #$count,
        		       The error reported is '$q_insert->error'.
                	   Skipping packet\n\n",3,$logfile);
                return null;
        }
        error_log("Inserted protocol $name\n",3,$logfile);
    }
    $q_insert_p->close();

	return True;
}


function check_if_wlan_exists( $ssid ){
	global $conn,$logfile;

	$q_select = $conn->prepare("SELECT ssid
                                FROM wlan
                                WHERE ssid = binary ?");
    $q_select->bind_param('s',$ssid);
    if(! $q_select->execute() ){
        error_log("ERROR: Couldn't execute select 
        	statement for wlan #$ssid.
            The error reported is '$q_select->error'.
            Skipping Packet\n\n",3,$logfile);
        return null;
    }
    $q_select->bind_result($found_ssid);
    $q_select->fetch();
	$q_select->close();
    
	if($found_ssid){
        error_log("Wlan already in DB. Skipping\n\n",3,$logfile);
        return True;
    } else {
		return False;
	}
}


function check_if_device_exists( $hw_address ){
	global $conn,$logfile;

	$q_select = $conn->prepare("SELECT hw_address
                                FROM device
                                WHERE hw_address = binary ?");
    $q_select->bind_param('s',$hw_address);
    if(! $q_select->execute() ){
        error_log("ERROR: Couldn't execute select 
        	statement for device #$hw_address.
            The error reported is '$q_select->error'.
            Skipping Packet\n\n",3,$logfile);
        return null;
    }
    $q_select->bind_result($found_hw_address);
    $q_select->fetch();
	$q_select->close();
    
	if($found_hw_address){
        error_log("Device already in DB. Skipping\n\n",3,$logfile);
        return True;
    } else {
		return False;
	}
}


function insert_rest( $fields,$count ){
	global $conn, $logfile;	
	
	//Insert device table rows
											# Not a broadcast packet
	if( $fields['dest_hw_address']!='ff:ff:ff:ff:ff:ff' ){		
											# Insert the device it was sent to
		if( check_if_device_exists( $fields['dest_hw_address'] )==null ){
			return null;
		} else if( check_if_device_exists( $fields['dest_hw_address'])==False ){
			$q_insert = $conn->prepare("INSERT INTO device(hw_address,wlan_assoc)
						                VALUES(?,?)");
			$q_insert->bind_param('ss',$fields['dest_hw_address'],
								       $fields['ssid']);
								   
			if(! $q_insert->execute() ){
				error_log("ERROR: Couldn't execute insert statement 
					for device #$fields['dest_hw_address'],
					The error reported is '$q_insert->error'.
					Skipping Packet\n\n",3,$logfile);
				return null;
			}
			$d_device_id= $q_insert->insert_id;
			$q_insert->close();
		}
		
	} 
											# Insert the device it was sent from
	if( check_if_device_exists( $fields['source_hw_address'] )==null ){
		return null;
	} else if( check_if_device_exists( $fields['source_hw_address'])==False ){
		$q_insert = $conn->prepare("INSERT INTO device(hw_address,wlan_assoc)
					                VALUES(?,?)");
		$q_insert->bind_param('ss',$fields['source_hw_address'],
								   $fields['ssid']);

		if(! $q_insert->execute() ){
			error_log("ERROR: Couldn't execute insert statement 
				for device #$fields['source_hw_address'],
				The error reported is '$q_insert->error'.
				Skipping Packet\n\n",3,$logfile);
			return null;
		}
		$s_device_id= $q_insert->insert_id;
		$q_insert->close();
	}

	//Insert wlan table rows
	
	if( check_if_wlan_exists( $fields['ssid'])==null ){
		return null;
	} else if( check_if_wlan_exists( $fields['ssid'])==False ){
		$q_insert = $conn->prepare("INSERT INTO wlan(ssid)
				                    VALUES(?)");
		$q_insert->bind_param('s',$fields['ssid']);

		if(! $q_insert->execute() ){
			error_log("ERROR: Couldn't execute insert statement 
				for wlan #$fields['ssid'],
			    The error reported is '$q_insert->error'.
			    Skipping Packet\n\n",3,$logfile);
			return null;
		}
		$wlan_id= $q_insert->insert_id;
		$q_insert->close();
	}
			
	//Update rows with the unprocessed fields 
				
	$q_multy = '';	# Prepare query templates
	$q_update_w  = "UPDATE wlan SET %s=%s WHERE ssid=$wlan_id;";
	$q_update_sd = "UPDATE device SET %s=%s WHERE hw_address=$s_device_id;";
	$q_update_dd = "UPDATE device SET %s=%s WHERE hw_address=$d_device_id;";
	
    if( array_key_exists('DEVICE_SRC__hw_addr_res',$fields) )
    	$q_multy .= sprintf($q_update_sd, 'hw_addr_res', 
    						$fields['DEVICE_SRC__hw_addr_res']);
    
    if( array_key_exists('DEVICE_DST__hw_addr_res',$fields) )
    	$q_multy .= sprintf($q_update_dd, 'hw_addr_res',
    					    $fields['DEVICE_DST__hw_addr_res']);
    					    
    if( array_key_exists('DEVICE_SRC__ip_address',$fields) )
    	$q_multy .= sprintf($q_update_sd, 'ip_address',
    					    $fields['DEVICE_SRC__ip_address']);
    					    
    if( array_key_exists('DEVICE_DST__ip_address',$fields) )
    	$q_multy .= sprintf($q_update_dd, 'ip_address',
    					    $fields['DEVICE_DST__ip_address']);	
    					    
  	if( array_key_exists('is_router',$fields) ){
  		$q_multy .= sprintf($q_update_sd, 'is_router',
  							$fields['is_router']);
  		$q_multy .= sprintf($q_update_w, 'ap_address',
  							$fields['source_hw_address']);
  		if( array_key_exists('encryption',$fields) )
  			$q_multy .= sprintf($q_update_w, 'encryption',
  								$fields['encryption']);					
  									
  	  	if( array_key_exists('supported_rates',$fields) )
  			$q_multy .= sprintf($q_update_w, 'supported_rates',
  								$fields['supported_rates']);
  											    
    	if( array_key_exists('bssid',$fields) )
  			$q_multy .= sprintf($q_update_w, 'bssid',
  								$fields['bssid']);
  	}											    
    					    
    error_log("About to perform multiquery:\n$q_multy\n",3,$logfile);
    if(! $conn->multi_query($q_multy) ){
        error_log("ERROR: Couldn't execute multi-query for remaining fields
        		   of packet #$count. Skipping Packet\n\n",3,$logfile);
        return null;
    }
	return True;					    
}






?>
