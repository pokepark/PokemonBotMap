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
                                                                                                                                                            
	$sql = "
    SELECT     quests.*,
                   questlist.quest_type, 
                   questlist.quest_quantity, 
                   questlist.quest_action,
                   rewardlist.reward_type, 
                   rewardlist.reward_quantity, 
                   encounterlist.pokedex_ids,
	            pokemon.pokemon_name,                   
                   pokestops.pokestop_name, 
                   pokestops.lat, 
                   pokestops.lon, 
                   pokestops.address
        FROM       quests
        LEFT JOIN  pokestops
        ON         quests.pokestop_id = pokestops.id
        LEFT JOIN  questlist
        ON         quests.quest_id = questlist.id
        LEFT JOIN  rewardlist
        ON         quests.reward_id = rewardlist.id
        LEFT JOIN  encounterlist
        ON         quests.quest_id = encounterlist.quest_id
        LEFT JOIN   pokemon 
        ON          pokemon.pokedex_id=encounterlist.pokedex_ids
        WHERE      quest_date = CURDATE()
        ORDER BY   pokestops.pokestop_name
	";

	$json           = file_get_contents( LOCATION_QUEST_REWARD_JSON);
	$translations   = json_decode($json,true);

	$result = $db->query($sql);
	
	if (!$result) {
		echo "An SQL error occured";
		exit;
	}
	
	$rows = array();
	while($stops = $result->fetch(PDO::FETCH_ASSOC)) {
		$rows[] = $stops;
	}
	
	$data['translations']   = $translations;
	$data['stops']          = $rows;	
	
	print json_encode($data);
	
	
?>
