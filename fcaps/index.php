<?php

/*
    Initialize sidebar for "fcaps" section
*/
$sidebar = '        <li><a href="fcaps/fault.php">Fault</a></li>
                    <li><a href="fcaps/configuration.php">Configuration</a></li>
                    <li><a href="fcaps/accounting.php">Accounting</a></li>
                    <li><a href="fcaps/performance.php">Performance</a></li>
                    <li><a href="fcaps/Security.php">Security</a></li>
                    ';

/*
    Content for the FCAPS section page is generated through
    the default "faults" page
*/
include('fcaps/fault.php')

include('base.php');    #Add this include to pages which render html code


?>

