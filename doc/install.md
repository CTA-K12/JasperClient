####Requirments
* php >= 5.3
* php-curl (i.e. php-pear-Net-Curl)

--    
 
####Installation

1. Clone git repo on your web server

        # cd /var/www
        # sudo git clone https://github.com/MESD/JasperClient.git
        

2. Make sure your web server user can access files.

        # sudo chown -R apache:apache jasperClient
        

3. If you want to use caching for metadata (on by default), make sure the cache directory is writeable by your web serevr user.

        # sudo chmod -R 770 jasperClient/report-demo/cache
        
        
4. Setup a web server alias for the jasperClient report-demo app. Below is an example alias configuration for the Apache httpd server. Make sure to restart your web service after making the configuration change.

        Alias /jasperDemo /var/www/JasperClient/report-demo/web/
        
5. Create config file

        # cd jasperClient/config
        # cp config.php.dist config.php
        
        
6. Update config file. The bare minumium changes are the report server host:port, user, and password.

        // Report Server host:port (leave off protocol i.e. http/https)
        define ("APP_REPORT_SERVER", "jasper-host:port");
        
        // Report Server User
        define ("APP_REPORT_USER", "jasperadmin");
        
        // Report Server Password
        define ("APP_REPORT_PASS", "");
