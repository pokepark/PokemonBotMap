<?php
  //Use same config as bot
  require_once("config.php");

  // Establish mysql connection.
  $dbh = new PDO("mysql:host=" . QUEST_DB_HOST . ";dbname=" . QUEST_DB_NAME . ";charset=utf8mb4", QUEST_DB_USER, QUEST_DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
  $dbh->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
  
  $sql = "
    SELECT     *
        FROM       quests
        LEFT JOIN  pokestops
        ON         quests.pokestop_id = pokestops.id
        LEFT JOIN  questlist
        ON         quests.quest_id = questlist.id
        LEFT JOIN  rewardlist
        ON         quests.reward_id = rewardlist.id
        LEFT JOIN  encounterlist
        ON         quests.quest_id = encounterlist.quest_id
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
 
  $jsonpokemon           = file_get_contents(LOCATION_POKEMON_JSON);
  $translationspokemon   = json_decode($jsonpokemon,true);
  
  $jsonpoketype           = file_get_contents(LOCATION_POKETYPE_JSON);
  $translationspoketype   = json_decode($jsonpoketype,true);
  
  $translations = array_merge($translationsaction, $translationstype, $translationsreward, $translationspokemon, $translationspoketype);
  

  

  $data['translations']   = $translations;
  $data['stops']          = $rows;
  
  print json_encode($data);

  $dbh = null;
?>
