<?php
require 'framwork/MooPHP.php';
MooPlugins('ipdata');
$address = convertIp(GetIP());
echo "var curent_area='".$address."'";
MooGetFromwhere();
?>
