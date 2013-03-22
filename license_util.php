<?php
#
# $Id: license_util.php 49584 2011-10-20 17:10:32Z proche $
#

if ( ! is_readable('./config.php') ) {
    echo("<H2>Error: Configuration file config.php does not exist. Please
    notify your system administrator.</H2>");
    exit;
} else
    include_once('./config.php');

require_once("./common.php");
require_once("./tools.php");

################################################################
# Get current date and time
################################################################
$date = date('Y-m-d');
$time = date('H:i') . ":00";
$statusMsg="";

foreach ($server as $host) {
    $master_array = getDetails($host);
    $license_array = $master_array['licenses'];
    $users = $master_array['users'];
    $status_array = $master_array['status'];
    if (strlen($status_array['msg'])>1) {
        emailAlerts($host,$status_array['msg']);
    }

    foreach ($license_array as $feature=>$feature_array) {
        $license_used=0;
        $num_licenses=0;

        # add up all the licenses available to each product feature
        foreach ($feature_array as $key) {
            $license_used += $key['licenses_used'];
            $num_licenses += $key['num_licenses'];
            $server = $host['hostname'];
            $feature_name = $feature;
            $push = false;
            # ugly hack!!!
            # mentalray (spm) and sesi may return multiple sets for
            # each license type, we want to record them individually
            switch ($host['type']) {
                case "spm":
                    $server = $key['extra'];
                    $push = true;
                    break;
                case "sesi";
                    $feature_name = $feature."-".$key['extra'];
                    $push = true;
                    break;
                default:
                    $server = $host['hostname'];
                    $feature_name = $feature;
            }
            # do we need to push per feature?
            if ($push) {
                $usage = array($server,$feature_name,$key['licenses_used'],$key['num_licenses']);
                writeLicense_Usage($usage);
            }

        }
        # if we didnt push before or 
        # array was larger than 1 and we want to send combined data
        if ((! $push) || (sizeof($feature_array) > 1)) {
            $usage = array($host["hostname"],$feature,$license_used,$num_licenses);
            writeLicense_Usage($usage);
        }
	}
}
?>
