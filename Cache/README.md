### Jasper Cache for ReportUnit & Folder metadata
--

#### About 
Loading of resources and information from the report server REST API can be
time consuming. Caching of report data is difficult as users have access to
different selection criteria (security) and the applications data can change
quickly. All of these make reports highly dynamic. This caching configuration
won't try to cache report data, but it will cache the report and folder listing
information.  This cache makes browsing the available forders and reports on
your report server through your application much more tolerable.


#### Configuration
Make sure that the Cache directory is owned and writeable by your web server user.
For example on a unix/linux machine, you might do somthing like:

    # sudo chown -R apache:apache JasperClient/Cache
    # sudo chmod -R 770 JasperClient/Cache
    
Next make sure caching is enabled in your config.php

    
    define ( "APP_REPORT_USE_CACHE", true );  // Should Folder & Report Metadata be cached
    define ( "APP_REPORT_CACHE_DIR", APP_MODELS . "/JasperClient/Cache" );  // Cache Location
    define ( "APP_REPORT_CACHE_TIMEOUT", 60 );  // Lifetime of cache in Minutes
