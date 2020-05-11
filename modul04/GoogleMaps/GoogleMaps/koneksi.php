<?php
//using mysqli
$mysqli = new mysqli("sql102.epizy.com", "epiz_24482941", "z3HIqannQ2oO", "epiz_24482941_db_sigweb");
//$mysqli = new mysqli("localhost", "root", "", "db_sigweb");
if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: " . $mysqli->connect_error;
}
