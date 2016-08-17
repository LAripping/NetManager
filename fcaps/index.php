<?php

/*
    Initialize sidebar for "fcaps" section
*/
$sidebar = '        <li><a href="/fcaps/fault.php">Fault</a></li>
                    <li><a href="/fcaps/configuration.php">Configuration</a></li>
                    <li><a href="/fcaps/accounting.php">Accounting</a></li>
                    <li><a href="/fcaps/performance.php">Performance</a></li>
                    <li><a href="/fcaps/security.php">Security</a></li>
                    ';

/*
    Content for the FCAPS section page is generated through
    the default "faults" page
*/

$content = '
    <h2>FCAPS reference model</h2>
    </br>
    </br>
    <p>...to the Network Management Console, where you can see, analyze and examine
    all the information gathered from the networks monitored!
    </p>
    <p>Feel free to take a look around and make yourself familiar with the options
    available by clicking throughout the tabs in the menu (above) and the specialised
    submenus for every section (on the left)
    </p>
    </br>
    <h4>Happy Management!</h4>
    ';


include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php')


?>

