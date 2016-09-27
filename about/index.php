<?php

include($_SERVER['DOCUMENT_ROOT'].'/about/sidebar.php');



$content = "
    <h2>About this application</h2>
    </br>
		<p>This application was designed as part of an assignment in the course
		'Network Management', taught by <a href='http://cgi.di.uoa.gr/~nancy/'>
		Nancy Alonisioti</a> in the spring semester, for the 
		<a href='http://www.di.uoa.gr'>Department of Informatics and 
		Telecommunications (DIT)</a> of the University of Athens (UOA). </p>
    </br>
    	<p>For a walkthrough of the application, feel free to peek at the 
    	presentation created to demonstrate the course project by visiting 
    	the <a href='/about/intro.php'>Intro</a> page. Google slides was 
    	used, but an PDF version is also available in the project's github 
    	repository (see next paragraph).</p>
    </br>
    	<p>The source code is open for contributions. For more information on 
    	how you can contribute, please visit the <a href='/about/credits.php'>
    	Credits</a> page.</p>
    </br>
    	<table>
    		<tr>
    			<td>
    				<img src='/theme/images/uoa-logo.png' 
    				alt='UOA logo' style=''>
    			</td>	
    			<td>
    				<img src='/theme/images/dit-logo.png'
    				alt='DIT logo' style=''>
    			</td>
    		</tr>
    	</table>";


include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php')


?>

