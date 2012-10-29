JAAT
====

JAAT (Just Another Apache Top) is a tool for monitoring processes in your Apache server(s) in a top-like way. It has been tested for Apache 2.2.x and should work for Apache 2.0.x.

Features of JAAT
* Web-based GUI based on PHP
* Monitor multiple remote Apache servers in one place
* Defaults to list active processes only and can be configured to list all the processes.
* The processes could be sorted by any fields.
* The processes list could be exported in CSV format.

INSTALLATION
* Unzip the downloaded package and move the application (i.e., the directory *jaat*) to the root directory of your web server for management.
* Copy the file *config.default.php* to *config.php*.
* Enable mod_status module and allow access from your management web server for each Apache server you want to monitored. The details could be found in http://httpd.apache.org/docs/2.2/mod/mod_status.html .
* List the Apache server(s) you want to monitor in *config.php*.
* Launch your browser (Chrome preferred) and load the page from your management web server.

QUESTIONS AND SUGGESTIONS
* You could reach me at cyril*dot*hcwang*at*gmail*dot*com

VERSION HISTORY
* 2012/10/29     0.1 First Release