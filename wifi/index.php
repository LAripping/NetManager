<?php

//include($_SERVER['DOCUMENT_ROOT'].'/wifi/sidebar.php');
$no_sidebar = TRUE;

include($_SERVER['DOCUMENT_ROOT'].'/db_connect.php');
#Returns an instanciated connection object ( $conn )


$content = '<p> Test </br>';

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


$content .= "</p>";

include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');

?>
