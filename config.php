<?php 
#
# $Id: config.php 61155 2013-03-19 22:36:07Z proche $
#

//date_default_timezone_set('America/Los_Angeles');
/* A good commented config-file (sample-config.php) is included in the distribution (in case you want to modify it manually). */

$today = strtotime(date('Y-m-d'));
# fake date used for permanent licenses
$permdate = "2020/1/1";
# defien upper limit for license without limits
$unlimited = 10000;

# cacti info
$cactiurl="http://cacti.example.com/graph.php?action=view&rra_id=all&local_graph_id=";
$cactigraph="http://cacti.example.com/graph_image.php?action=view&local_graph_id=";

# utils used to gather info
$lmutil_loc="/usr/local/bin/lmutil";
$lmutil="/usr/local/bin/lmutil";
$lmstat_loc=$lmutil_loc . " lmstat"; 
$spmstat="/usr/local/bin/spmstat";
$rvlstatus="/usr/local/bin/rvlstatus";
$sesictrl="/usr/local/bin/sesictrl";
$tlm_server="export LD_LIBRARY_PATH=/usr/local/lib ; /usr/local/bin/tlm_server";
$rlmstat="/usr/local/bin/rlmutil rlmstat";
$pixar_query="/usr/local/bin/pixar_query.sh";

# emailed alerts
$URL="http://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'],0,strrpos($_SERVER['REQUEST_URI'],"/")) . "/"; 
# who is email from
$notify_from="licensing@example.com";
# who gets general notifications (license expiring soon)
$notify_to="licensing@example.com";
# who gets license down alerts, besides notify_to
$notify_alerts="alert@example.com";
# pause between notifications, in minutes
$notify_resend="60";

# when should we worry about licenses? in days
$lead_time=10;
$disable_autorefresh=0;
$disable_license_removal=0;
# how often do we sample? in minutes
$collection_interval=5;

# db info
$db_type="mysql";
$db_hostname="localhost";
$db_username="phplic";
$db_password="phplic";
$db_database="phplicensewatcher";
$dsn = "mysql://$db_username:$db_password@$db_hostname/$db_database";

# table/graph info
$colors = "#ffffdd,#ff9966, #ffffaa,#ccccff,#cccccc,#ffcc66,#99ff99,#eeeeee,#66ffff,#ccffff, #ffff66, #ffccff,#ff66ff, yellow,lightgreen,lightblue";
$rrdtool_bin="/usr/local/rrdtool-1.2.27/bin/rrdtool";
$rrd_dir="/var/www/html/phplicensewatcher/rrd";
$smallgraph="350,240";
$largegraph="900,400";
$legendpoints="";
$log_file[]="";

# server list
//$server[] = array("hostname"=>"","desc"=>"","type"=>"");

# monitor list
$monitor_license[] = array("feature"=>"nuke_r","description"=>"Nuke Render");
$monitor_license[] = array("feature"=>"nuke_i","description"=>"Nuke (GUI)");
?>
