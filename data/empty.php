<?php

include($_SERVER['DOCUMENT_ROOT'].'/data/sidebar.php');

include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
global $conn;

if( isset($_POST['submit']) ){
    $q_multy = 'DELETE FROM wlan ; DELETE FROM packet ; DELETE FROM device';

    if(! $conn->multi_query($q_multy) ){
        die("The error reported is $conn->error.");
    }
    
    do{
    	$res = $conn->store_result();
    	if($res) $res->free();
    }while( $conn->more_results()&&$conn->next_result() );

	$content = '</br><p>Database emptied!</p></br>';
} else{
	$content = '<form method="post">
					<h3>Please Confirm</h3>
					</br>
					<p>Are you sure you want to empty the database?</p>
					</br>
					<input type="submit" name="submit" value="Yes">
				</form>';
}    
    


include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');

?>
