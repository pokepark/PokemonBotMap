<?php
	//Use same config as bot
	require_once("config.php");
	
	// Establish mysql connection.
	$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$db->set_charset('utf8mb4');

	// Error connecting to db.
	if ($db->connect_errno) {
		echo("Failed to connect to Database!\n");
		die("Connection Failed: " . $db->connect_error);
	}
                                                                                                                                                            
	$sql = "SELECT * FROM gyms";

	$result = $db->query($sql);
	
	if (!$result) {
		echo "An SQL error occured";
		exit;
	}
	
	$rows = array();
	while($gyms = $result->fetch_assoc()) {
		$rows[] = $gyms;
	}
	
	print json_encode($rows);
	
	
?>
