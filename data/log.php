<?php

include($_SERVER['DOCUMENT_ROOT'].'/data/sidebar.php');

$content = '
    <p>Parser log from last action...</p>
    </br>
    </br>
   	<iframe id="logframe" style="width:inherit; height:490px; border:none;" src="/nms.log"></iframe>
    
    <script>document.getElementById("logframe").contentWindow.location.reload();</script>
    
    </br>
    ';


include($_SERVER['DOCUMENT_ROOT'].'/theme/base.php')


?>


