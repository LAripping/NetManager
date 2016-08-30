<?php

include($_SERVER['DOCUMENT_ROOT'].'/data/sidebar.php');
//$no_sidebar = TRUE;

$content = '<p>Using the expat XML parser, a Database is populated with
    captured traffic.</p>';

$content .= '<p>Follow the options in the sidebar for the relevant actions.</p>';

include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');

?>
