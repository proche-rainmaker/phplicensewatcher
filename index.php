<?php

##
##      $Id: index.php 61155 2013-03-19 22:36:07Z proche $
##


require_once("common.php");
require_once("tools.php");
print_header("License Server Status");

?>

<link rel="top" href="index.php"/>
</head>
<body>
<h1>License Server Status</h1>
<div align="center"><p>To get current usage for an individual server please click on the "Details" link next to the server.</p></div>
<div id="msg" style="visibility:hidden;"></div>
<hr/>

<?php

##########################################################################
# We are using PHP Pear library to create tables :-)
##########################################################################
require_once ("HTML/Table.php");

# empty statusmsg
$statusMsg="";

$tableStyle = "border=\"1\" cellpadding=\"1\" cellspacing=\"2\" ";

# Create a new table object
$table = new HTML_Table($tableStyle);
$table->setColAttributes(1,"align=\"center\"");

# Define a table header
$headerStyle = "";
$colHeaders = array("License Server","Description", "Status", "Current Usage", "Available features/license","Master", "Version");
$table->addRow($colHeaders, $headerStyle, "TH");
# set width on description col
$table->setColAttributes(1,"width=\"180\"");

# grab all the different server types
foreach ($server as $host) {
    $type[]=$host['type'];
}
# return only unique types
$types = array_unique($type);

# loop thru each unique type and make up status table
foreach ($types as $type) {
    $servers = findServers($type,"type");

    if (sizeof($servers)>0) {
        $table->addRow(array(strtoupper($type) . " Servers"),$headerStyle,"TH");
        $table->setCellAttributes(($table->getRowCount() -1),0,"colspan='" .$table->getColCount() ."'");

        for ( $i = 0 ; $i < sizeof($servers) ; $i++ ) {
            $cur = current($servers);
            $status_array = getDetails($cur);
            # does this host contain a webui?
            # currently only RLM offers webui
            if (isset($cur["webui"])) {
                $host = "<a href=\"".$cur["webui"]."\">".$cur["hostname"]."</a>";
            } else {
                $host = $cur["hostname"];
            }
            $table->AddRow(array($host,
                                $cur["desc"],
                                strtoupper($status_array["status"]["service"]),
                                $status_array["status"]["clients"],
                                $status_array["status"]["listing"],
                                $status_array["status"]["master"],
                                $status_array["status"]["version"]));

            # Set the background color of status cell
            $table->updateCellAttributes( ($table->getRowCount() - 1) , 2, "class='" . $status_array["status"]["service"] . "'");
            $table->updateCellAttributes( 1 , 0, "");
            # fetch status
            $statusMsg=AppendStatusMsg($statusMsg,$status_array["status"]["msg"]);
            # next!
            next($servers);
        }
    }
}

# Display the table
$table->display();

#footer
include_once('version.php');

print ("\n");
print ('<script language="javascript" type="text/javascript">');
print ("document.getElementById('msg').innerHTML = \"".$statusMsg."\";");
# if we have a msg, make the box visiable
if (strlen($statusMsg)>1) {
    print ("document.getElementById('msg').style.visibility = 'visible';");
}
print ('</script>');
?>

</body></html>
