September 28th 2017

1. All ingest servers, the VOD server, and the main RTMP server are now running Debian 9 (Stretch), previously all were running Debian 7 (Wheezy).  These were all started as clean installations, and packages reinstalled from the ground up.
2. Nginx on all servers has been updated to the latest stable release, 1.12.1.  The mainline release is up to 1.13.5 so I imagine that will hit stable soon, at which point we'll update.
3. The RTMP module is now from the dev branch of Sergey's fork (<https://github.com/sergey-dryabzhinsky/nginx-rtmp-module>), where perviously it was using master.  We're hoping this solves some finicky issues but nothing major.
4. MySQL has been changed to MariaDB -  Oracle are kinda gross and their development of MySQL isn't particularly open.  MariaDB's development is fully open and has much more cutting edge features.  Generally it performs better too, so it doesn't make sense not to switch while we can.
5. We've upgraded from PHP5 to PHP7 - another thing worth doing while we're reinstalling Nginx, PHP7 is more modern and has a ton of new features.  Thankfully none of our scripts have broken either!
 
Todo:
* Reinstall Nginx on the main server!  I'm an idiot and forgot to change a line of code in one of the modules, which means rebuilding Nginx.  Thankfully this is pretty small nowadays since we'll just backup the folder and the restore it once rebuilt.
* Set up MariaDB replication from the main server to the backup server.
* Investigate rtmp config file restructuring to make system management easier.
