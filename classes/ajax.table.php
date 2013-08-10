<?php 
require_once('../../../config.php');
require_once('../../../classes/Mysql.php');
require_once('../../../library/ajax.table/class.table.php');

$db = new MySQL($config['db']['hostname'], $config['db']['username'], $config['db']['password'], $config['db']['database']);	
;	
	
echo new tableManager(
	$db, 
	(string)$_REQUEST['name'], 
	"SELECT
  devices.id AS ID,
  devices.deviceuid AS DeviceID,
  devices.imei,
  devices.number AS `Cell Number`,
  devices.model_ AS Model,
  IF(devices.account = '' OR ISNULL(devices.account), 'unassigned', devices.account) AS Member_no,
  IF(devices.account = '' OR ISNULL(devices.account), 'unassigned', CONCAT(account_members.firstname, ' ', account_members.lastname)) AS Member
FROM devices
  INNER JOIN devices_models
    ON devices.model = devices_models.id
  LEFT OUTER JOIN account_members
    ON account_members.id = devices.account
",
	'ID',
	(int)@$_REQUEST['page'], 
	(string)@$_REQUEST['order'], 
	(string)@$_REQUEST['dir'], 
	(string)@$_REQUEST['limit']
);
?>