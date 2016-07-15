<!DOCTYPE HTML>
<html>
<head>
  <meta charset="UTF-8">
<?php
include_once('config.php');

setOtrCode();
printmsg($_REQUEST);

printmsg(getOtrCode());
?>
</head>

<body>
</body>
</html>