<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
<title>Management Console</title>
</head>

<body>
<div id="container">
		<div id="header">
        	<h1>Management  <span class="off">Console</span></h1>
        </div>

        <div id="menu">
        	<ul>
                <li class="menuitem"><a href="fcaps.php">FCAPS model</a></li>
<!-- TODO complete fcaps.php business logic-->
                <li class="menuitem"><a href="devices.php">Devices</a></li>
<!-- TODO add protocols.php business logic-->
                <li class="menuitem"><a href="protocols.php">Protocols</a></li>
<!-- TODO add devices.php business logic-->
                <li class="menuitem"><a href="wifi.php">WiFi Analysis</a></li>
<!-- TODO add wifi.php business logic-->
            </ul>
        </div>

        <div id="leftmenu">
            <div id="leftmenu_top"></div>
		    <div id="leftmenu_main">
                <h3> </h3>
                <ul>
                    <?php echo $sidebar; ?>
                </ul>
            </div>
            <div id="leftmenu_bottom"></div>
        </div>

		<div id="content">
            <div id="content_top"></div>
            <div id="content_main">
                <?php echo $content; ?>
            </div>
            <div id="content_bottom"></div>
        </div>

    </div>
</body>
</html>
