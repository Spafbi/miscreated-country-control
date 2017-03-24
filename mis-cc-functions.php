<?php
/*
 * HomeRun check: this little snippet should prevent this file from being processed
 * by PHP and accidentally exposing your credentials, inner workings, and other
 * sensistive information.
 */
if (!isset($homeRun)) {
  die("Really? No. I don't think so...");
}

function processPlayers($server,$players=array()) {
  global $cfg;
  if (!@isset($cfg['emailAddress'])) {
    $cfg['vpn']['check']=0;
    $email=0;
  } else {
    $email=$cfg['emailAddress'];
  }
  if (count($players)) {
    echo "Processing players...\n";
    // Create an empty array. We'll add state 3 players to the array.
    $action=array();
    if (@is_array($players)) {
      $path=dirname(__FILE__);
      $log_file = $path.'/mis-cc-ip.log';
      $ip_log = fopen($log_file, "a");
      foreach ($players as $key => $value) {
        if (@isset($cfg['steamID64Exceptions'])) {
          if ( in_array($key, $cfg['steamID64Exceptions']) ) {
            continue;
          }
        }
        $kick=0;
        $ban=0;
        if (!strpos(file_get_contents($log_file),":{$value['ip']}:")) {
          $$key=new ipAddress($value['ip'],$key,$email);

          if ($value['state']=='3') {
            $entry =$$key->get_steamID64();
            $entry.=':'.$$key->get_ip();
            $$key->set_country($$key->get_ip());
            $entry.=':'.$$key->get_country();

            if (@isset($cfg['limitToCountries'])) {
              if (!in_array($$key->get_country(),$cfg['limitToCountries'])) {
                if ($cfg['ban']) {
                  $ban=1;
                } else {
                  $kick=1;
                }
              }
            } elseif (@in_array($$key->get_country(),$cfg['countries'])) {
              if ($cfg['ban']) {
                $ban=1;
              } else {
                $kick=1;
              }
            }

            if ($cfg['vpn']['check']) {
              $$key->set_vpn($$key->get_ip());
              if (!($$key->get_vpn() < 1)) {

                if ($cfg['vpn']['ban']) {
                  $ban=1;
                } else {
                  $kick=1;
                }

              }

              $entry.=':'.$$key->get_vpn();

            } else {
              $entry.=':null';
            }

            if ($ban) {
              $entry.=':ban';
              $action['ban'][]=$$key->get_steamID64();
            }
            if ($kick) {
              $entry.=':kick';
              $action['kick'][]=$$key->get_steamID64();
            }

            $entry.="\r\n";

            fwrite($ip_log,$entry);
          }
        }
      }
    fclose($ip_log);
    }
  } else {
    echo "No players processed.";
  }
  return $action;
}

?>
