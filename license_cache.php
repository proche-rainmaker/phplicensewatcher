<?php
#
# $Id: license_cache.php 49448 2011-10-15 00:51:45Z proche $ 
#

######################################################
# license_cache
# my job is to query the license server once at the start of the day
# and populate the database with the max number of licenses available
# per feature
######################################################



if ( ! is_readable('./config.php') ) {
    echo("<H2>Error: Configuration file config.php does not exist. Please
         notify your system administrator.</H2>");
    exit;
} else
    include_once('./config.php');

require_once("./common.php");
require_once("./tools.php");

###################################################
# We are using PEAR's DB abstraction library
###################################################
require_once("DB.php");    


################################################################
#  Connect to the database
#   Use persistent connections
################################################################
$db = DB::connect($dsn, true);

if (DB::isError($db)) {
    die ($db->getMessage());
}

$today = date("Y-m-d");

foreach ($server as $host) {
    $master_array = getDetails($host);
    $license_array = $master_array['licenses'];

    foreach ($license_array as $feature=>$feature_array) {
        $license_total=0;

        # add up all the licenses available to each product feature
        foreach ($feature_array as $key) {
            $license_total += $key['num_licenses'];
        }

        $sql_format = 'INSERT INTO licenses_available (flmavailable_server,flmavailable_date,flmavailable_product,flmavailable_num_licenses) VALUES ("%s","%s","%s","%s")';
        $sql = sprintf($sql_format,$host["hostname"],$today,$feature,$license_total);
                
        if ( isset($debug) && $debug == 1 ) {
            print_sql ($sql);
        }

        $recordset = $db->query($sql);

        if (DB::isError($recordset)) {
            die ($recordset->getMessage());
        }
    }

}

$db->disconnect();

?>
