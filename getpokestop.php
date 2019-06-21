<?php
  //Use same config as bot
  require_once("config.php");

  // Establish mysql connection.
  $dbh = new PDO("mysql:host=" . QUEST_DB_HOST . ";dbname=" . QUEST_DB_NAME . ";charset=utf8mb4", QUEST_DB_USER, QUEST_DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
  $dbh->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);

  $rows = array();  
  try {

    $sql = "SELECT * FROM pokestops";
    $result = $dbh->query($sql);
    
    while($stop = $result->fetch(PDO::FETCH_ASSOC)) {

      $rows[] = $stop;
    }
  }
  catch (PDOException $exception) {

    error_log($exception->getMessage());
    $dbh = null;
    exit;
  }

  print json_encode($rows);

  $dbh = null;
?>
