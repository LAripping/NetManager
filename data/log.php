<?php

include($_SERVER['DOCUMENT_ROOT'].'/data/sidebar.php');

$content = '
    <h3>Parser log</h3>
    </br>
    <p> from last action...</p>
    </br>
    </br>
   	<iframe id="logframe" style="width:inherit; height:490px; border:none;" src="/nms.log"></iframe>
        
    </br>
    ';


include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php')


?>


