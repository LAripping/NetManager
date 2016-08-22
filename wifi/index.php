<?php

//include($_SERVER['DOCUMENT_ROOT'].'/wifi/sidebar.php');
$no_sidebar = TRUE;

include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
#Returns an instanciated connection object ( $conn )


$content = '<p> UNPREPARED SELECT </br>';

$stmt = "SELECT * FROM packets";
$result = $conn->query($stmt);

if ($result == TRUE && $result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $content .= "id: " . $row["id"] . "</br>";
    }
} else {
    $content .= "0 results";
}


$content .= '</p></br>';
///////////////////////////////////////////////////

$content = '<p> 5 PACKETS </br>';

$name = $_SERVER['DOCUMENT_ROOT'].'/afull.xml';
$count = 0;

$file = simplexml_load_file($name);
foreach ($file->xpath('//packet') as $packet) {
    if($count==5)   break;

    $content .= $count.$packet."</br></br>";
	$proto = $packet->proto[4];

    $content .= $proto['name'];
    $count++;
}


$content .= "</p>";

include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');

?>
