<?php
/*

CONFIGURE FILE OF SCRIPT

*/

// Set username and password for control panel login
$USERNAME='admin';
$PASSWORD='admin';

// Set your timezone. List of options: https://www.php.net/manual/en/timezones.php
date_default_timezone_set('Europe/Madrid');

// Set Maximum time expiration of counter's cookie in seconds (86400 = 1 day).
$COOKIE_EXPIRATION_TIME=60;

// Set redirect URL if user click over a link with campaign already finished.
$URL_REDIRECT_CAMPAIGN_FINISHED='https://www.disney.com';

// Set redirect URL if user already clicked ant tries to click again (cookie not expired).
$URL_REDIRECT_ALREADY_CLICKED='https://yourdomain.com/sorry.html';

// Set $URL_SCRIPT_FOLDER variable with script url. Example: https://yourdomain.com/counter/
$URL_SCRIPT_FOLDER='';

// Set $PATH_LOGS_COUNTERS variable with full path for storing counters logs. Example: /server/full/path/yourdomai.com/htpdocs/counters/logs_counters/
$PATH_LOGS_COUNTERS='';

// Set $PATH_LOGS_HISTORY variable with full path for sotring counters history logs. Example: /server/full/path/yourdomai.com/htpdocs/counters/logs_history/
$PATH_LOGS_HISTORY='';
?>