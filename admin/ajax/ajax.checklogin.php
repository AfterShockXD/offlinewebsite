<?php
session_start();
ini_set('display_errors', true);
error_reporting(-1);
if ( !isset($_SESSION) ) session_start();
include('../../classes/config.php');
include('../../classes/mysql.php');
include('../../classes/functions.php');
# Load the MySQL database class and initiate a connection
load_database();

$response = Array(
	'type' => 'fail',
	'msg'  => 'Login error'
);
foreach( $_POST as $key => $value )
 if (!is_array($value)) 
    $_POST[ $key ] = $db->escape($value);

$uname = $_POST['uname'];
$pword = $_POST['pword'];
		
$query = "SELECT * FROM dbgcon.tbladmin where email = '{$uname}' and password = '{$pword}' and status=1";

$rs = $db->query($query);
$row = $db->fetchObject($rs);

if ($db->numRows($rs) > 0) {

	$response = Array(
		'type' => 'ok',
		'msg'  => 'Logged in'
	);	
	$_SESSION['uid'] = $row->id;
	$_SESSION['name'] = $row->fname;
} 
		
echo json_encode($response);

		
?>