phplicensewatcher
=================

What it does:
-------------

 - basic webui for license servers, teams can easily view what the license status is
 - reporting of license statistics to either RRD or MySQL
 - alerting if license is down, this includes if an RLM ISV fails.
 - monitoring of license expiration
 - there is no RRD frontend, so hooks into Cacti using 'Graph Mangement' ID that you create [Cacti Docs](http://docs.cacti.net/manual:087:8_rrdtool.05_external_rrds)
 - RLM provides its own webserver, can be defined and linked by adding "webui"=>"http://url:port" to the $server[] array.

Supported License Servers:
--------------------------
 - flexlm
 - rlm
 - sesi
 - tweak
 - pixar
 - spm
 - rvl

config.php:
-----------
$server[] = array("hostname"=>"port@flexlm.example.com","desc"=>"flexlm stuff","type"=>"flexlm","cacti"=>"0000");
$server[] = array("hostname"=>"port@rlm.example.com","desc"=>"rlm stuff","type"=>"rlm","webui"=>"http://rlm.example.com:port");


see README for more details
