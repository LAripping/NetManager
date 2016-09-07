<?php

include($_SERVER['DOCUMENT_ROOT'].'/data/sidebar.php');



$content = '<form action="/data/populate.php" method="post">
				<h3>Select XML file to process</h3>
				</br>
				<p>XML files found in webroot directory:</p>
				</br>';
				
foreach (new DirectoryIterator('..') as $file) {
    if($file->getExtension()=='xml')
	    $content .= "<input type='radio' name='file' value='" 
	    		 . $file->getFilename() . "'> "
	    		 . $file->getFilename() . "<br>";
}				

$content .='</br><input type="submit" name="submit" value="Parse file"></form>';

include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php');

?>
