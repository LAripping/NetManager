<?php

include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
global $conn;

 



/*
 * Returns 
 *	-null if something goes wrong
 *	-True if packet is found
 *	-False if packet is not found in DB
 */ 
function check_if_packet_exists( $time,$count ){
	global $conn,$logfile;

	$q_select = $conn->prepare("SELECT id
                                FROM packet
                                WHERE time_captured = ?");
    
    if( !$q_select ){
    	error_log("ERROR: Couldn't prepare select statement 
            for packet #$count.
            The error reported is '$conn->error'.
            Skipping Packet\n\n",3,$logfile);
        return null;
    }
    
    $q_select->bind_param('s',$time);
    if(! $q_select->execute() ){
        error_log("ERROR: Couldn't execute select statement 
            for packet #$count.
            The error reported is '$q_select->error'.
            Skipping Packet\n\n",3,$logfile);
        $q_select->close();    
        return null;
    }
    
    $q_select->bind_result($found_id);
    $q_select->fetch();
    
	if($found_id){
        error_log("Packet already in DB (id=$found_id)
            Skipping\n\n",3,$logfile);   
        $ret = True;
    } else {
		$ret = False;
	}
		
	$q_select->close(); 
	return $ret;
}



function insert_geninfo( $fields,$count ){
	global $conn,$logfile;
			
	$q_insert = $conn->prepare("INSERT INTO packet(time_captured,
												   num,
												   packet_size,
												   protocols)
                                VALUES(?, ?, ?, ?)");
                                
	if( !$q_insert ){
	    error_log("ERROR: Couldn't prepare insert statement 
        		   for packet #$count,
            	   The error reported is '$conn->error'.
            	   Skipping Packet\n\n",3,$logfile);
        return null;
    }                          	
                                
    $q_insert->bind_param('siis',$fields['time_captured'],
                                 $fields['num'],
                                 $fields['packet_size'],
                                 $fields['protocols']);
    error_log("*About to insert packet with geninfo:\nINSERT INTO packet..\n",
    														3,$logfile);
    if(! $q_insert->execute() ){
        error_log("ERROR: Couldn't execute insert statement 
        	for packet #$count,
            The error reported is '$q_insert->error'.
            Skipping Packet\n\n",3,$logfile);
        $q_insert->close();    
        return null;
    }
    $in_id= $q_insert->insert_id;
    $q_insert->close();
	return $in_id;
}



function insert_packet( $fields,$count,$in_id ){
	global $conn,$logfile;

	//Some fields need Post processing 
	if( array_key_exists('_unprotected',$fields)
       && $fields['type']=='2'){
	    $fields['uprotected']='1';
    } 

    $q_multy = '';
    $q_update = "UPDATE packet SET %s='%s' WHERE id=$in_id;";

    $non_packet = array('bssid','supported_rates', 'encryption');
    foreach($fields as $key => $value){
        if( in_array($key,$non_packet) || strstr($key,'DEVICE')){
            error_log("Skipping update for field $key...\n",3,$logfile);
            continue;
        }
        $q_multy .= sprintf($q_update, $key,$value);
    }

    error_log("*About to perform multiquery:\n$q_multy\n",3,$logfile);
    if(! $conn->multi_query($q_multy) ){
        error_log("ERROR: Couldn't execute multi-query for packet #$count.
        		   The error reported is $conn->error.
        		   Skipping Packet\n\n",3,$logfile);
        return null;
    }
    
    do{
    	$res = $conn->store_result();
    	if($res) $res->free();
    }while( $conn->more_results()&&$conn->next_result() );
    
	return True;
}




/*
 * Returns 
 *	-null if something goes wrong
 *	-True if wlan is found
 *	-False if wlan is not found in DB
 */
function check_if_wlan_exists( $ssid ){
	global $conn,$logfile;

	$q_select = $conn->prepare("SELECT ssid
                                FROM wlan
                                WHERE ssid = ?");
	if( !$q_select ){
        error_log("ERROR: Couldn't prepare select 
        		   statement for wlan #$ssid.
            	   The error reported is '$conn->error'.
                   Skipping Packet\n\n",3,$logfile);
        return null;
    }                            
                                
    $q_select->bind_param('s',$ssid);
    if(! $q_select->execute() ){
        error_log("ERROR: Couldn't execute select 
        		   statement for wlan #$ssid.
            	   The error reported is '$q_select->error'.
                   Skipping Packet\n\n",3,$logfile);
        $q_select->close();           
        return null;
    }
    $q_select->bind_result($found_ssid);
    $q_select->fetch();
    
	if($found_ssid){
        error_log("Wlan already in DB. Skipping\n",3,$logfile);
		$ret = True;
    } else {
		$ret = False;
	}
	
	do{
		$res=$q_select->store_result();
		$q_select->free_result();
	}while($conn->more_results()&&$conn->next_result());
	
	$q_select->close();
    return $ret;
}





/*
 * Returns 
 * 	-null if something goes wrong
 *	-False if packet is not found in DB
 *	-the 'hw_address' attribute of the rown found
 */
function check_if_device_exists( $hw_address ){
	global $conn,$logfile;

	$q_select = $conn->prepare("SELECT hw_address
                                FROM device
                                WHERE hw_address = ?");
	if( !$q_select ){
        error_log("ERROR: Couldn't prepare select 
        		   statement for device #$hw_address.
            	   The error reported is '$conn->error'.
                   Skipping Packet\n\n",3,$logfile);
        return null;
    }                                
                                
    $q_select->bind_param('s',$hw_address);
    if(! $q_select->execute() ){
        error_log("ERROR: Couldn't execute select 
        	statement for device #$hw_address.
            The error reported is '$q_select->error'.
            Skipping Packet\n\n",3,$logfile);
        $q_select->close();
        return null;
    }
    $q_select->bind_result($found_hw_address);
    $q_select->fetch();
    
    
	if($found_hw_address){
        error_log("Device already in DB. Skipping\n",3,$logfile);
        $ret = $found_hw_address;
    } else {
		$ret = False;
	}
	
	do{
		$res=$q_select->store_result();
		$q_select->free_result();
	}while($conn->more_results()&&$conn->next_result());
	
	$q_select->close();
	
	return $ret;
}


function insert_rest( $fields,$count ){
	global $conn, $logfile;	
	
	//Insert device table rows
											# Not a broadcast packet
	if( !is_null($fields['dest_hw_address']) 
	&& 	$fields['dest_hw_address']!='ff:ff:ff:ff:ff:ff' ){		
											# Insert the device it was sent to
		$d_device_id = check_if_device_exists( $fields['dest_hw_address'] );
		if( is_null($d_device_id) ){
			return null;
		} else if( $d_device_id==False ){
			$q_insert = $conn->prepare("INSERT INTO device(hw_address)
						                VALUES(?)");
			if( !$q_insert ){
				error_log("ERROR: Couldn't prepare insert statement
						   for device #$fields[dest_hw_address].
						   The error reported is '$conn->error'.
						   Skipping Packet\n\n",3,$logfile);
				return null;
			}						                
						                
			$q_insert->bind_param('s',$fields['dest_hw_address']);
    		error_log("*About to insert dest device :\nINSERT INTO device..\n",
    														3,$logfile);								   
			if(! $q_insert->execute() ){
				error_log("ERROR: Couldn't execute insert statement
						   for device #$fields[dest_hw_address].
						   The error reported is '$q_insert->error'.
						   Skipping Packet\n\n",3,$logfile);
   			    $q_insert->close();
				return null;
			}
			$d_device_id= $q_insert->insert_id;
			$q_insert->close();
		} 
	}
	
	if( !is_null($fields['source_hw_address']){  
											# Insert the device it was sent from
		$s_device_id = check_if_device_exists( $fields['source_hw_address'] );
		if( is_null($s_device_id) ){
			return null;
		} else if( $s_device_id==False ){
			$q_insert = $conn->prepare("INSERT INTO device(hw_address)
							            VALUES(?)");
							            
			if( !$q_insert ){
				error_log("ERROR: Couldn't prepare insert statement 
					for device #$fields[source_hw_address],
					The error reported is '$conn->error'.
					Skipping Packet\n\n",3,$logfile);
				return null;
			}					                
							            
			$q_insert->bind_param('s',$fields['source_hw_address']);
			error_log("*About to insert src device :\nINSERT INTO device..\n",
																3,$logfile);
			if(! $q_insert->execute() ){
				error_log("ERROR: Couldn't execute insert statement 
					for device #$fields[source_hw_address],
					The error reported is '$q_insert->error'.
					Skipping Packet\n\n",3,$logfile);
				$q_insert->close();
				return null;
			}
			$s_device_id= $q_insert->insert_id;
			$q_insert->close();
		}
	}

	//Insert wlan table rows
	
	if( array_key_exists('ssid',$fields) && !is_null($fields['ssid']) ){
		if( is_null(check_if_wlan_exists( $fields['ssid']))){
			return null;
		} else if( check_if_wlan_exists( $fields['ssid'])==False ){
			$q_insert = $conn->prepare("INSERT INTO wlan(ssid)
						                VALUES(?)");
						                
			if( !$q_insert ){			
				error_log("ERROR: Couldn't prepare insert statement 
					for wlan #$fields[ssid],
					The error reported is '$conn->error'.
					Skipping Packet\n\n",3,$logfile);
				return null;
			}	                    
						                
			$q_insert->bind_param('s',$fields['ssid']);
    		error_log("*About to insert wlan :\nINSERT INTO wlan..\n",
    														3,$logfile);
			if(! $q_insert->execute() ){
				error_log("ERROR: Couldn't execute insert statement 
					for wlan #$fields[ssid],
					The error reported is '$q_insert->error'.
					Skipping Packet\n\n",3,$logfile);
				$q_insert->close();    
				return null;
			}
			$wlan_id= $q_insert->insert_id;
			$q_insert->close();
		}
	}
			
	//Update rows with the unprocessed fields 
				
	$q_multy = '';	# Prepare query templates
	$q_update_sd = "UPDATE device SET %s='%s' WHERE hw_address='$s_device_id';";
	$q_update_dd = "UPDATE device SET %s='%s' WHERE hw_address='$d_device_id';";
	
	if( array_key_exists('ssid',$fields) && !is_null($fields['ssid']) ){
		$q_update_w  = "UPDATE wlan SET %s='%s' WHERE ssid='$wlan_id';";
			$q_multy .= sprintf($q_update_sd, 'wlan_assoc', $fields['ssid']);
		if( $fields['dest_hw_address']!='ff:ff:ff:ff:ff:ff' ){
			$q_multy .= sprintf($q_update_dd, 'wlan_assoc', $fields['ssid']);
		}
	}
	
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
    					    
    error_log("*About to perform multiquery:\n$q_multy\n",3,$logfile);
    if(! $conn->multi_query($q_multy) ){
        error_log("ERROR: Couldn't execute multi-query for remaining fields
        		   of packet #$count. 
        		   The error reported is $conn->error.
        		   Skipping Packet\n\n",3,$logfile);
        return null;
    }
    
    do{
    	$res = $conn->store_result();
    	if($res) $res->free();
    }while( $conn->more_results()&&$conn->next_result() );
    
	return True;					    
}






?>
