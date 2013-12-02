JasperClient
============

About
-----
JasperClient is a php library for connecting to the Jaspersoft report server
REST API to run reports.
 
This library contains some modified code from the flowl/jasper library available at:
https://github.com/flowl/jasper

The flowl/jasper library was in beta and missing functionality that we needed
(Oct 2013). We originally planned to create a pull request against the library,
but ended up changing it too heavily and deleting a lot of code that we didn't
need.

The core goal of this library is to provide native Jasper report running within
a php application. Creating, updating, and deleting reports or any other objects
in the Jaspersoft report server is not planned at this time.


Status
------
Beta - Nov 2013


Issues to Resolve
-----------------
* Upload the report viewer code
* Find better way to handle report page count
* Address how report input controls are currently handled
* Cleanup error handling in the report viewer
* Add support for jasper professional server
