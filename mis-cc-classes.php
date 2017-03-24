<?php
/*
 * HomeRun check: this little snippet should prevent this file from being processed
 * by PHP and accidentally exposing your credentials, inner workings, and other
 * sensistive information.
 */
if (!isset($homeRun)) {
  die("Really? No. I don't think so...");
}

class RCON {
  function __construct($ip, $port, $pass) {
    $this->url="http://{$ip}:{$port}/rpc2";
    $this->password=$pass;
  }

  function banId($steamId=0) {
      echo "Ban ID: {$steamId}\r\n";
  }

  function kickId($steamId=0) {
      echo "Kick ID: {$steamId}\r\n";
  }

  function sendCommand($command=0) {
    /*
     * This function returns a two value array: result, msg. Result is either true or false based on success or failure,
     * respectively. Msg is the message to be returned.
     */
    if (!$command) { return array('result'=>0,'msg'=>'Command not passed'); }
    // Init curl, set common headers and options.
    $curl=curl_init();
    $header[0] = "Connection: keep-alive";
    $header[1] = "Content-type: text/xml";
    curl_setopt($curl, CURLOPT_URL, $this->url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_POST, 1);

    /*
     * Start challenge - this gets uptime from the Miscreated server.
     */
    $challenge="<methodCall><methodName>challenge</methodName><params></params></methodCall>";
    $header[2] = "Content-length: ".strlen($challenge);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $challenge);
    $challengeResult = curl_exec($curl);
    $uptime=$this->parseResponseString($challengeResult);

    /*
     * Start authentication - authenticate with the Miscreated server.
     *
     * The value we send for authentication is an md5sum of the combined value of the server's uptime, a colon, and the password.
     */
    $md5Pass=md5($uptime.':'.$this->password);
    $authenticate='<methodCall><methodName>authenticate</methodName><params><param><value><string>'.
                  $md5Pass.
                  '</string></value></param></params></methodCall>';
    $header[2] = "Content-length: ".strlen($authenticate);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $authenticate);
    $authResult = curl_exec($curl);
    $auth=$this->parseResponseString($authResult);
    // The server will return 'authorized' if everything went okay.
    if ($auth !== 'authorized') {
      return array('result'=>0,'msg'=>'Authentication failed');
    }

    /*
     * Execute command
     */
    $commandString='<methodCall><methodName>'.
             $command.
             '</methodName><params></params></methodCall>';
    $header[2] = "Content-length: ".strlen($commandString);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $commandString);
    $commandResult = curl_exec($curl);
    $result=$this->parseResponseString($commandResult);
    $invalid='[Whitelist] Invalid command:';
    $invalidCheck=strpos($result,$invalid);
    if ($invalidCheck === true) {
      $commandWorked=1;
    } else {
      $commandWorked=0;
    }
    return array('result'=>$commandWorked,'msg'=>$result);
  }

  private function parseResponseString($xml) {
    $parse = xml_parser_create();
    xml_parse_into_struct($parse, $xml, $xmlVals, $xmlIndex);
    xml_parser_free($parse);
    $string=@$xmlVals[$xmlIndex['STRING'][0]]['value'];
    return $string;
  }

  function currentStatus() {
    $rawStatus=$this->sendCommand("status");
    $rawStatus=$rawStatus['msg'];
    $rawStatus=explode("\n", str_replace("\r","\n",$rawStatus));
    $statusArray=array();
    $unknown=0;
    foreach ($rawStatus as $value) {
      if (strpos($value,':')) {
        $tmpArray=explode(':',$value,2);
        if ($tmpArray[0] == "steam") {
          $unknown++;
          $tmpInfo=str_replace('  name: ',':',$tmpArray[1]);
          $info=explode(':',$tmpInfo,2);
          $steamID64=trim($info[0]);
          if ($steamID64=='') {
            $unknown++;
            $steamID64="<unknown${unknown}>";
          }
          $name=trim(preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', explode('  entID:',$info[1],2)[0]));
          $info=explode('  entID:',$info[1],2)[1];
          $info=str_replace('  id: ',':',$info);
          $info=str_replace('  ip: ',':',$info);
          $info=str_replace('  ping: ',':',$info);
          $info=str_replace('  state: ',':',$info);
          $info=str_replace('  profile: ',':',$info);
          $info=explode(':',$info);
          $statusArray['player'][$steamID64]['name']=$name;
          $statusArray['player'][$steamID64]['ip']=$info[2];
          $statusArray['player'][$steamID64]['state']=$info[5];
        } else {
           $statusArray[$tmpArray[0]]=$tmpArray[1];
        }
      }
    }
    return $statusArray;
  }
}

class ipAddress {

  function __construct($ip=false,$steamID64=false,$email) {
    if (($ip) && ($steamID64) && ($email)) {
      $this->set_steamID64($steamID64);
      $this->set_ip($ip);
      $this->email=$email;
    } else {
      return 0;
    }
  }

  function set_steamID64($value) {
    $this->steamID64=$value;
  }

  function get_steamID64() {
    return $this->steamID64;
  }

  function set_ip($value) {
    $this->ip=$value;
  }

  function get_ip() {
    return $this->ip;
  }

  function set_country() {
    $url="http://ipinfo.io/{$this->ip}";
    $handle=fopen($url,'r');
    stream_set_timeout($handle,10);
    $contents=stream_get_contents($handle);
    $this->country=0;
    foreach(preg_split("/((\r?\n)|(\r\n?))/", $contents) as $line){
      if (strstr($line,'"country"')) {
        $this->country=explode(':',trim(preg_replace('/[^a-zA-Z0-9:]/', '', $line)))[1];
      }
    }
  }

  function get_country() {
    return $this->country;
  }

  function set_vpn($value) {
    $this->vpn=0;
    $url="http://check.getipintel.net/check.php?ip={$this->ip}&contact={$this->email}";
    $handle=@fopen($url,'r');
    if ($handle) {
      stream_set_timeout($handle,10);
      $contents=@stream_get_contents($handle);
      if ($contents) { $this->vpn=$contents; } else { $this->vpn = 0; }
    }
  }

  function get_vpn() {
    return $this->vpn;
  }

}

?>
