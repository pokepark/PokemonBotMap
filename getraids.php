<?php
  //Use same config as bot
  require_once("config.php");

  // Establish mysql connection.
  $dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
  $dbh->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
                                                                                                                                                            
  $sql = "
    SELECT    raids.*,
      gyms.gym_name,
      gyms.lat,
      gyms.lon,
      UNIX_TIMESTAMP(raids.end_time) AS ts_end,
      UNIX_TIMESTAMP(raids.start_time) AS ts_start,
      UNIX_TIMESTAMP(raids.end_time)-UNIX_TIMESTAMP(NOW()) AS t_left,
      pokemon.raid_level, 
      pokemon.pokedex_id,
      pokemon.pokemon_name,
      attendance.id AS interest, 
      SUM(CASE WHEN attendance.cancel=FALSE AND attendance.raid_done=FALSE THEN 1 ELSE 0 END) AS count,
      SUM(CASE WHEN attendance.cancel=FALSE and attendance.raid_done=FALSE THEN attendance.extra_mystic ELSE 0 END) AS extra_mystic,
      SUM(CASE WHEN attendance.cancel=FALSE and attendance.raid_done=FALSE THEN attendance.extra_valor ELSE 0 END) AS extra_valor,
      SUM(CASE WHEN attendance.cancel=FALSE and attendance.raid_done=FALSE THEN attendance.extra_instinct ELSE 0 END) AS extra_instinct,
      SUM(CASE WHEN attendance.cancel=FALSE and attendance.raid_done=FALSE THEN (attendance.extra_mystic+attendance.extra_valor+attendance.extra_instinct) ELSE 0 END) AS total_extras
    FROM raids
      LEFT JOIN pokemon ON pokemon.pokedex_id=raids.pokemon
      LEFT JOIN attendance ON attendance.raid_id=raids.id
      LEFT JOIN gyms ON gyms.id = raids.gym_id
    WHERE raids.end_time > NOW()
      AND raids.end_time < NOW() + INTERVAL " . MAP_RAID_END_TIME_OFFSET_HOURS . " hour
    GROUP BY  gyms.gym_name
    ORDER BY  raids.end_time ASC";

  $rows = array();
  try {

    $result = $dbh->query($sql);
    while($raid = $result->fetch(PDO::FETCH_ASSOC)) {

      $rows[] = $raid;
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
