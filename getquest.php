<?php
  //Use same config as bot
  require_once("config.php");

  // Establish mysql connection.
  $dbh = new PDO("mysql:host=" . QUEST_DB_HOST . ";dbname=" . QUEST_DB_NAME . ";charset=utf8mb4", QUEST_DB_USER, QUEST_DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
  $dbh->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
                                                                                                                                                            
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

  $rows = array();
  try {

    $result = $dbh->query($sql);
    while($stops = $result->fetch(PDO::FETCH_ASSOC)) {

      $rows[] = $stops;
    }
  }
  catch (PDOException $exception) {

    error_log($exception->getMessage());
    $dbh = null;
    exit;
  }
  
  $jsonaction           = file_get_contents(LOCATION_QUEST_ACTION_JSON);
  $translationsaction   = json_decode($jsonaction,true);
  
  $jsontype           = file_get_contents(LOCATION_QUEST_TYPE_JSON);
  $translationstype   = json_decode($jsontype,true);
  
  $jsonreward           = file_get_contents(LOCATION_QUEST_REWARD_JSON);
  $translationsreward   = json_decode($jsonreward,true);
  
  $translations = array_merge($translationsaction, $translationstype, $translationsreward);

  $data['translations']   = $translations;
  $data['stops']          = $rows;

  print json_encode($data);

  $dbh = null;
?>
