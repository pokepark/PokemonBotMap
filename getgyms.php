<?php
	//Use same config as bot
	require_once("config.php");
	
	// Establish mysql connection.
	$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASSWORD);

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
	while($gym = $result->fetch(PDO::FETCH_ASSOC)) {
		$rows[] = $gym;
	}
	
	print json_encode($rows);
	
	
?>
