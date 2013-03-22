<?php

require_once("common.php");
print_header("License Monitor Graphs");
?>
</head><body>
<h1>License monitoring</h1>
<p class="a_centre"><a href="admin.php"><img src="back.jpg" alt="up page"/></a></p>
<hr/>
<p>Following links will show the license usage for different tools. Data is being collected every <?php echo($collection_interval); ?> minutes.</p>
<p>Features (click on link to show past usage):</p>

<ul>
<?php

#############################################################
# Print out the list of tools we are showing statistics
#############################################################
for ( $i = 0 ; $i < sizeof($monitor_license); $i++ ) {
	echo ('<li><a href="feature_monitor.php?feature=' . $monitor_license[$i]["feature"] . '">' . $monitor_license[$i]["description"] . '</a></li>');
}
?>
</ul>

<?php

include_once('./version.php');

?>
</body></html>
