
application/x-httpd-php getrocketstop.php ( PHP script, ASCII text )

<?php
  //Use same config as bot
  require_once("config.php");

  // Establish mysql connection.
  $dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
  $dbh->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);

  $rows = array();  
  try {

    $sql = "

SELECT     * 
FROM       invasions
LEFT JOIN  pokestops
ON         invasions.pokestop_id = pokestops.id    
WHERE invasions.end_time > UTC_TIMESTAMP()
      AND invasions.end_time < UTC_TIMESTAMP()  + INTERVAL 1 hour


    ";
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

