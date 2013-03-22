<?php
#
# $Id: license_alert.php 51936 2012-02-09 18:44:45Z proche $
#
############################################################################
# Purpose: This script is used to e-mail alerts on licenses that are 
# 	   due to expire some time in the future. This script should
#	   be run out of cron preferably every day. Check config.php
#	   to configure e-mail address reports should be sent to 
#	   as well as how much ahead should the user be warned about 
#	   expiration ie. 10 days before license expires.
############################################################################

#require_once("common.php");
#print_header("

if ( ! is_readable('./config.php') ) {
    echo("<H2>Error: Configuration file config.php does not exist. Please
         notify your system administrator.</H2>");
    exit;
} else
    include_once('./config.php');

if ( ! is_readable('./tools.php') ) {
    echo("<H2>Error: Tools.php is missing. Please make sure that the file is 
there. Exiting ....</H2>");
    exit;
} else
    include_once('./tools.php');


######################################################### 
# Date when the licenses will expire
######################################################### 
$expire_date = mktime (0,0,0,date("m"),date("d")+$lead_time,  date("Y"));

$today = mktime (0,0,0,date("m"),date("d"),  date("Y"));

$message = "<p align=\"center\"><a href=\"admin.php\"><img src=\"back.jpg\" alt=\"up page\" border=\"0\"></a></p>";


##############################################################
# We are using PHP Pear stuff ie. pear.php.net
##############################################################
require_once ("HTML/Table.php");


$table = new HTML_Table();

$headerStyle = "bgcolor=lightblue";
$colHeaders = array("Server", "Server description", "Feature", "Version", "Expiration", "Days to expiration", "Number of license(s) expiring");

$table->addRow($colHeaders, $headerStyle, "TH");

#######################################################
# Get names of different colors. These will be used to group visually
# licenses from the same license server
#######################################################
$color = explode(",", $colors);
$i=0;

foreach ($server as $host) { 
    $master_array = getDetails($host);
    $expiration_array = $master_array['expiration'];

    # Now after the expiration has been built loop through all the fileservers
    foreach ( $expiration_array as $key => $myarray) {
        for ( $j = 0 ; $j < sizeof($myarray) ; $j++ ) {
            if ( (strcmp($myarray[$j]["days_to_expiration"],"permanent") != 0) && ($myarray[$j]["days_to_expiration"] <= $lead_time) ) {
                if ( $myarray[$j]["days_to_expiration"] < 0 )  
                    $myarray[$j]["days_to_expiration"] = "<b>Already expired</b>";

                $table->addRow(array(
                    $host["hostname"], 
                    $host["desc"],
                    $key,
                    $myarray[$j]["version"],
                    $myarray[$j]["expiration_date"],
                    $myarray[$j]["days_to_expiration"],
                    $myarray[$j]["num_licenses"]),
                    "bgcolor='" . $color[$i] ."'"
                );
            }
        }
    } 
    //}
}    

########################################################
# Center columns 2,4,5and 6. Columns start with 0 index
########################################################
$table->updateColAttributes(1,"align=center"); 
$table->updateColAttributes(4,"align=center"); 
$table->updateColAttributes(5,"align=center"); 
$table->updateColAttributes(3,"align=center"); 

########################################################
# Dump the table HTML into a variable
########################################################
$table_html = $table->toHTML();

#echo($table_html);

$message = "<HTML>\n<BODY>
These licenses will expire within " . $lead_time . " days. Licenses 
will expire at 23:59 on the day of expiration.<p>";

$message .= $table_html;


########################################################################
# If the table has more than one row (header row will be one) there 
# are expiring licenses
########################################################################(
if ( $table->getRowCount() > 1 ) {

   if ( $notify_to && ! isset($_GET['nomail']) ) {

        $message .= "<p>URL: <a href=\"".$URL."\">".$URL."</a></p>";
        $message .= "</HTML>";
        
        echo("Emailing to $notify_to<p>\n");

       mail($notify_to, "ALERT: License expiration within " . $lead_time . " days", $message,
          "From: License Robot <" . $notify_from . ">\nContent-Type: text/html\nMime-Version: 1.0");

   }

}

$message .= "</HTML>";
echo($message);

?>
