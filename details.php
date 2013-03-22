<?php
#
# $Id: details.php 51321 2012-01-17 19:56:19Z proche $
#

require_once("common.php");
require_once("tools.php");
print_header("Licenses in Detail");

##############################################################
# We are using PHP Pear stuff ie. pear.php.net
##############################################################
require_once ("HTML/Table.php");

?>
</head>
<body>

<h1>Licenses in Detail</h1>
<p class="a_centre"><a href="index.php"><img src="back.jpg" alt="up page"/></a></p>
<hr/>

<?php

# grab server info
if (isset($_GET['server'])) {
	$host = $server[$_GET['server']];
} else {
	die('no server defined');
}	


#################################################################
# List available features and their expiration dates
#################################################################
if ( $_GET['listing'] == 1 ) {
	echo('<p>This is a list of licenses (features) available on this particular license server. If there are multiple entries under "Expiration dates" it means there are different entries for the same license. If expiration is in red it means expiration is within ' . $lead_time . ' days.</p>');

	$today = mktime(0,0,0,date("m"),date("d"),date("Y"));

	$tableStyle = "border=\"1\" cellpadding=\"1\" cellspacing=\"2\" ";

	# Create a new table object
	$table = new HTML_Table($tableStyle);

	# First row should be the name of the license server and it's description
	$headerStyle = "colspan=\"4\"";
	$colHeaders = array("Server: " . $host['hostname'] . " ( " . $host['desc'] . " )");

	$table->addRow($colHeaders, $headerStyle, "TH");
	
    $master_array = getDetails($host);
    $expiration_array = $master_array['expiration'];
    
	# Define a table header
	$headerStyle = "style=\"background: yellow;\"";
	$colHeaders = array("Feature", "Vendor Daemon", "Total licenses", "Number licenses, Days to expiration, Date of expiration");
	$table->addRow($colHeaders, $headerStyle, "TH");

	#######################################################
	# Get names of different colors. These will be used to group visually
	# licenses from the same license server
	#######################################################
	$color = explode(",", $colors);

	#######################################################
	# We should have gotten an array with features
	# their expiration dates etc.
	#######################################################

	foreach ($expiration_array as $key => $feature_array) {
		$total_licenses = 0;
		$feature_string = "";
		$feature_table = new HTML_Table("width=100%");

		for ( $p = 0 ; $p < sizeof($feature_array) ; $p++ ) {

			# Keep track of total number of licenses for a particular feature
			# this is since you can have licenses with different expiration
			$total_licenses += $feature_array[$p]["num_licenses"];
			$feature_table->addRow(array($feature_array[$p]["num_licenses"] . " license(s) expire(s) in ". $feature_array[$p]["days_to_expiration"] . " day(s) Date of expiration: " . $feature_array[$p]["expiration_date"] ), "colspan=\"3\"");
			
			#######################################################################
			# Check whether license is close to expiration date			
			#######################################################################
			if ( $feature_array[$p]["days_to_expiration"] <= $lead_time  ) {

				if ($feature_array[$p]["days_to_expiration"] >= 0 ){
					$feature_table->updateRowAttributes( ($feature_table->getRowCount() - 1) , "class=\"expires_soon\"");
				} elseif ( $feature_array[$p]["days_to_expiration"] < 0 ){
					$feature_table->updateRowAttributes( ($feature_table->getRowCount() - 1) , "class=\"already_expired\"");
				}

			}

		}

		$table->addRow(array(
			$key,
			$feature_array[0]["vendor_daemon"],
			$total_licenses,
			$feature_table->toHTML(),
		));

		unset($feature_table);
	}

	########################################################
	# Center columns 2. Columns start with 0 index
	########################################################
	$table->updateColAttributes(1,"align=\"center\"");

	$table->display();

} else {
	########################################################
	# Licenses currently being used
	########################################################
	echo ("<p>Following is the list of licenses currently being used. Licenses that are currently not in use are not shown.</p>\n");
	# stop the annoying errors in error_log saying undefined var
	# happens when no user lics been checked out
    if (isset($host['cacti'])) {
        $cactiurl = $cactiurl . $host['cacti'];
        $cactigraph = $cactigraph . $host['cacti'];
        printf("<div align=\"center\"><a href=\"%s\" border=0><img src=\"%s\"></a></div>\n",$cactiurl,$cactigraph);
    }
	$master_array = getDetails($host);
    $users = $master_array['users'];
    $license_array = $master_array['licenses'];
	

	#######################################################
	# Get names of different colors
	#######################################################
	$color = explode(",", $colors);


	################################################################################
	# Check whether anyone is using licenses from this particular license server
	################################################################################
	if ( sizeof($users) > 0 ) {
		# Create a new table
		$tableStyle = "width=\"100%\"";
		$table = new HTML_Table($tableStyle);

		# Show a banner with the name of the serve@port plus description
		$headerStyle = "colspan=\"4\"";
		$colHeaders = array("Server: " . $host['hostname'] . " ( " . $host['desc']. " )");
		$table->addRow($colHeaders, $headerStyle, "TH");
		$x=0;

		$headerStyle = "style=\"background: lightblue;\"";
		$colHeaders = array("Feature", "# cur. avail", "Details","Time checked out");
		$table->addRow($colHeaders, $headerStyle, "TH");

		# Get current UNIX time stamp
		$now = time ();

		###########################################################################
		# Loop through the used features
		###########################################################################
		foreach ($license_array as $key => $feature_array) {
			# add up all the licenses reported
			$license_total = 0;
			$license_used = 0;
			$license_available = 0;
				
			for ($j=0;$j<sizeof($feature_array);$j++) {	
				$license_total += $feature_array[$j]["num_licenses"];
				$license_used += $feature_array[$j]["licenses_used"];
			}
				
			$license_available = ($license_total - $license_used);
			$license_info = "Total of " . $license_total . " licenses, " .  $license_used . " currently in use, <b>" . $license_available . " available</b>";
			
			if ( $license_used > 0 ) {
				$table->addRow(array($key, $license_available, $license_info));
			}	
			
			#sometimes it looks like a license is checked out but nothing is reported
			
			if ( isset($users[$key])) {
				for ( $k=0 ; $k < sizeof($users[$key]) ; $k++ ) {
					$time_difference = "";
				
					$t = new timespan( $now, $users[$key][$k]["time_checkedout"] ) ;
					#format the date string
					if ( $t->years > 0) $time_difference .= $t->years . " years(s), ";
					if ( $t->months > 0) $time_difference .= $t->months . " month(s), ";
					if ( $t->weeks > 0) $time_difference .= $t->weeks . " week(s), ";
					if ( $t->days > 0) $time_difference .= " " . $t->days . " day(s), ";
					if ( $t->hours > 0) $time_difference .= " " . $t->hours . " hour(s), ";
					$time_difference .= $t->minutes . " minute(s)";
				
					$table->addRow(array("&nbsp;","",$users[$key][$k]["line"],$time_difference), "style=\"background:$color[$x];\"");
				}	
				# bump color code
				$x++;
			}
		}
		//$table->updateColAttributes(2,"align=\"center\"");
		//$table->updateColAttributes(4,"align=\"center\"");
		//$table->updateColAttributes(0,"align=\"center\"");

        # Display the table
        if ( $table->getRowCount() > 2 ){
            $table->display();
        }
	} else {
		echo("<p style=\"color: red;\">No licenses are currently being used on " . $host['hostname']. " ( " . $host['desc'] . " )</p>");
	}
}

include_once('./version.php');

?>

</body></html>
