<?php
/*
 * This PHP script is NOT intended to be run on a web server. This is a php-cli script. Running this script through a
 * web server will likely not work at all, and may have other unintended consequences.
 */

/*
 * Requirements: php7.0-cli, php7.0-curl, php7.0-xml
 */

/*
 * Setting the $homeRun variable to allow included php files to execute if they
 * have set the "Home Run" check which normally would prevent the script from
 * completing execution.
 */
$homeRun=1;

// Pull in our includes.
require_once('config/mis-cc-config.php'); // configuration file for this app.
require_once('mis-cc-classes.php'); // classes for this app.
require_once('mis-cc-functions.php'); // classes for this app.

if (@isset($cfg['timezone'])) {
  date_default_timezone_set($cfg['timezone']);
} else {
  date_default_timezone_set('America/New_York');
}

if (@isset($cfg['srvr'])) {
  foreach ($cfg['srvr'] as $key => $value) {
    echo $key."\n";
    $$key = new RCON($value['rcon']['ip'],
                     $value['rcon']['port'],
                     $value['rcon']['password']);
    if (!$$key) {
      echo "Configuration error for server {$$key}.".
           "Please make sure ip, port, and password are defined.";
    } else {
      $status=$$key->currentStatus();
      if (@count($status['player'])) {
        $action=processPlayers($key,$status['player']);
        if (@isset($action['kick'])) {
          foreach ($action['kick'] as $value) {
            $$key->kickId($value);
          }
        }
        if (@isset($action['ban'])) {
          foreach ($action['ban'] as $value) {
            $$key->banId($value);
          }
        }
      }
    }
  }
} else {
  echo "Error: Please add server configurations.\n";
}

die();
?>
