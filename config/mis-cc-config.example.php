<?php
/*
 * This little app restricts player access to Miscreated servers. Identified by their IP address, players may be
 * allowed access, kicked, or banned by the IP address' country of origin.
 */

/*
 * HomeRun check: this little snippet should prevent this file from being processed by PHP and accidentally exposing
 * your credentials and other sensitive information.
 */
if (!isset($homeRun)) {
  die("Really? No. I don't think so...");
}

/*
 * As the VPN check is a courtesy service, the owner requests your email address be used when making requests. You must
 * respond in a timely manner if the service owner attempts to contact you.
 */
$cfg['emailAddress']='your@email.address';

/*
 * Set your local timezone. Valid timezones may be found here:
 * http://php.net/manual/en/timezones.php
 */
$cfg['timezone']='America/New_York';

/*
 * Array of countries you limit to the server. If a country isn't listed, the player cannot get in.
 * Values must correspond to the appropriate ISO Alpha-2 Code.
 *
 * Uncomment and add a line for each country you wish to allow. You may have this set, or the countries you wish to
 * exclude, but not both.
 */
// $cfg['limitToCountries'][]='US';

/*
 * Array of countries you do not wish to allow on the server. If you
 * Values must correspond to the appropriate ISO Alpha-2 Code.
 */
$cfg['countries'][]='BR';
$cfg['countries'][]='CN';
$cfg['countries'][]='JP';
$cfg['countries'][]='PH';
$cfg['countries'][]='RU';

/*
 * steamID64 you wish to exclude from actions. Create an entry for each ID.
 *
 * NOT YET IMPLEMENTED!!!!
 */
 // $cfg['steamID64Exceptions'][]='steamID64valueGoesHere';
 // $cfg['steamID64Exceptions'][]='nextsteamID64valueGoesHere';

/*
 * Do you wish to kick or ban users by country? Default is to kick. Set to 1 to ban.
 */
$cfg['ban']=0;

/*
 * Do you want to check for VPN users? If so, change 'check' value to 1.
 */
$cfg['vpn']['check']=0;

/*
 * Do you want to kick or ban VPN users? Default is to kick, change to 1 to ban.
 */
$cfg['vpn']['ban']=0;

/*
 * Server settings - required
 *
 * Format: $cfg['srvr']['name']... you can specify multiple servers by adding additional entries to the array.
 */
 $cfg['srvr']['srv01']['rcon']['ip']='123.45.67.89';
 $cfg['srvr']['srv01']['rcon']['password']='secretpassword';
 $cfg['srvr']['srv01']['rcon']['port']='12345';

 // $cfg['srvr']['srv02']['rcon']['ip']='123.45.67.89';
 // $cfg['srvr']['srv02']['rcon']['password']='secretpassword';
 // $cfg['srvr']['srv02']['rcon']['port']='12345';

?>
