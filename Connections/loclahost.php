<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_loclahost = "127.0.0.1";
$database_loclahost = "dbgcon";
$username_loclahost = "root";
$password_loclahost = "";
$loclahost = mysql_pconnect($hostname_loclahost, $username_loclahost, $password_loclahost) or trigger_error(mysql_error(),E_USER_ERROR); 
?>