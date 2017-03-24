# miscreated-country-control
Limit access to your Miscreated game servers by player country of origin.

## Notes ##
* Copy `mis-cc-config.example.php` to `mis-cc-config.php` and configure as desired. Please pay attention to the config notes in that file.
* The user running *miscreated-country-control* must have write permissions to the `miscreated-country-control` directory. A log file, `mis-cc-ip.log`, will be written to this location. This log file location will be added at a future date.
* The app does not yet kick or ban players, but only prints that information to the console. This will be changed after additional testing.

## How to run ##
Command line, only.

```/usr/bin/php /opt/miscreated-country-control/mis-cc.php```

Be sure to correct the binary and directory paths according to your system configuration.

It's a good idea to schedule this as a cron job... you can figure that out.
