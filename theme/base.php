<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="/theme/style.css" />
	<?php echo $extra_css; ?>
	<title>Management Console</title>
</head>

<body>
<div id="container">
		<div id="header">
        	<h1><a href='/index.php'>Management  <span class="off">Console</span></a></h1>
        </div>

        <div id="menu">
        	<ul>
                <li class="menuitem"><a href="/fcaps/index.php">FCAPS model</a></li>
                <li class="menuitem"><a href="/wifi/index.php">WiFi Analysis</a></li>
                <li class="menuitem"><a href="/data/index.php">Captured Data</a></li>
            </ul>
        </div>

        <?php if( !isset($no_sidebar) ): ?>
        <div id="leftmenu">
            <div id="leftmenu_top"></div>
		    <div id="leftmenu_main">
            <p></p>
                <ul>
                    <?php echo $sidebar; ?>
                </ul>
            </div>
            <div id="leftmenu_bottom"></div>
        </div>
        <?php endif ?>

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
