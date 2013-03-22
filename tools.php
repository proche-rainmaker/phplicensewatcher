<?php
#
# $Id: tools.php 61155 2013-03-19 22:36:07Z proche $
#

# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#                                                                             #
# B00zy's timespan script v1.2                                               #
#                                                                             #
# timespan -- get the exact time span between any two moments in time.        #
#                                                                             #
# Description:                                                                #
#                                                                             #
#        class timespan, function calc ( int timestamp1, int timestamp2)      #
#                                                                             #
#        The purpose of this script is to be able to return the time span     #
#        between any two specific moments in time AFTER the Unix Epoch        #
#        (January 1 1970) in a human-readable format. You could, for example, #
#        determine your age, how long you have been married, or the last time #
#        you... you know. ;)                                                  #
#                                                                             #
#        The class, "timespan", will produce variables within the class       #
#        respectively titled years, months, weeks, days, hours, minutes,      #
#        seconds.                                                             #
#                                                                             #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#                                                                             #
# Example 1. B00zy's age.                                                     #
#                                                                             #
#        $t = new timespan( time(), mktime(0,13,0,8,28,1982));                #
#        print "B00zy is $t->years years, $t->months months, ".               #
#                "$t->days days, $t->hours hours, $t->minutes minutes, ".     #
#                "and $t->seconds seconds old.\n";                            #
#                                                                             #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #

define('day', 60*60*24 );
define('hour', 60*60 );
define('minute', 60 );

class timespan
    {
    var $years;
    var $months;
    var $weeks;
    var $days;
    var $hours;
    var $minutes;
    var $seconds;

    function leap($time)
        {
        if (date('L',$time) and (date('z',$time) > 58))
            return (double)(60*60*24*366);
        else
            {
            $de = getdate($time);
            $mkt = mktime(0,0,0,$de['mon'],$de['mday'],($de['year'] - 1));
            if ((date('z',$time) <= 58) and date('L',$mkt))
                return (double)(60*60*24*366);
            else
                return (double)(60*60*24*365);
            }
        }
    function readable()
        {
        $values = array('years','months','weeks','days','hours','minutes','seconds');
        foreach ($values as $k => $v)
            if ($this->{$v}) $fmt .= ( $fmt? ', ': '') . $this->{$v} . " $v";
        return $fmt . ( $fmt? '.': '') ;
        }

    function timespan($after,$before)
        {
        # Set variables to zero, instead of null.
        
        $this->years = 0;
        $this->months = 0;
        $this->weeks = 0;
        $this->days = 0;
        $this->hours = 0;
        $this->minutes = 0;
        $this->seconds = 0;

        $duration = $after - $before;

        # 1. Number of years
        $dec = $after;

        $year = $this->leap($dec);

        while (floor($duration / $year) >= 1)
            {
	    # We don't need this VV
            #print date("F j, Y\n",$dec);

            $this->years += 1;
            $duration -= (int)$year;
            $dec -= (int)$year;
            
            $year = $this->leap($dec);
            }

        # 2. Number of months
        $dec = $after;
        $m = date('n',$after);
        $d = date('j',$after);

        while (($duration - day) >= 0)
            {
            $duration -= day;
            $dec -= day;
            $this->days += 1;

            if ( (date('n',$dec) != $m) and (date('j',$dec) <= $d) )
                {
                $m = date('n',$dec);
                $d = date('j',$dec);

                $this->months += 1;
                $this->days = 0;
                }
            }
        # 3. Number of weeks.
        $this->weeks = floor($this->days / 7);
        $this->days %= 7;

        # 4. Number of hours, minutes, and seconds.
        $this->hours = floor($duration / (60*60));
        $duration %= (60*60);

        $this->minutes = floor($duration / 60);
        $duration %= 60;

        $this->seconds = $duration;
        }
    }


function generate_error_image($str) {
    
        header("Content-type: image/png");
        $im = @imagecreate (300, 200)
            or die ("Cannot Initialize new GD image stream");
        $background_color = imagecolorallocate ($im, 220, 210, 60);
        $text_color = imagecolorallocate ($im, 233, 14, 91);
        imagestring ($im, 1, 5, 5,  $str, $text_color);
        imagestring ($im, 1, 5, 25,  "Please check your settings.", $text_color);
        imagepng($im);
        imagedestroy($im);
    
}

###################################################################################################
# Insert values into existing RRDs. If no date is supplied it defaults to current time
###################################################################################################
function insert_into_rrd($name, $payload, $date = "N") {
    global $rrdtool_bin;
    global $rrd_dir;

    $ret_var=0;
    $filename = $rrd_dir . "/" . $name . ".rrd";
    $res = create_rrd($filename);
    if ($res) {
        // create_rrd failed
        // so fail insert_into_rrd
        $ret_var=2;
    } else {
        exec ($rrdtool_bin . " update " . $filename . " " . $date . ":" . $payload, $output=array(),$ret_var);	
    }
    
    switch ($ret_var) {
        case 1:
            print $name . ".rrd unable to be updated <br>\n";
            break;
        case 2:
            print $name . ".rrd unable to be created <br>\n";
            break;
    } 
    return $ret_var;
}

function bulk_rrd($name,$payload) {
    global $rrdtool_bin;
    global $rrd_dir;

    $ret_var=0;
    $filename = $rrd_dir . "/" . $name . ".rrd";
    $res = create_rrd($filename);
    exec($rrdtool_bin . " update " . $filename . " " . $payload,$output=array(),$retvar);
    return $ret_var;
}

function create_rrd($filename) {
    global $collection_interval;
    global $rrd_dir;
    global $rrdtool_bin;

    # set ret_var to 0 
    # meaning file exists
    $ret_var=0;
    if ( ! file_exists($filename) ) {
        ##################################################################
        # Convert the collection interval into seconds
        ##################################################################
        $step = $collection_interval * 60;
        # Startime is today - a year
        $startdate = time() - 365 * 1440 * 60;
        $rrd_arg = $rrdtool_bin . " create " . $filename . " --start " . $startdate .
        " --step " .  $step . 
        " DS:used:GAUGE:900:0:10000" .
        " DS:max:GAUGE:900:0:10000 " . 
        "RRA:AVERAGE:0.5:1:800 RRA:AVERAGE:0.5:6:800 RRA:AVERAGE:0.5:24:800 RRA:AVERAGE:0.5:288:800 " .
        "RRA:MAX:0.5:1:800 RRA:MAX:0.5:6:800 RRA:MAX:0.5:24:800 RRA:MAX:0.5:288:800";
        exec($rrd_arg,$output=array(),$ret_var);
    }
    return $ret_var;
}

function writeLicense_Usage($usage) {
    # db connection
    require_once("DB.php");
    global $dsn;
    global $db_hostname;
    global $db_username;
    global $db_password;
    # rrd
    global $rrdtool_bin;

    $date = date('Y-m-d');
    $time = date('H:i') . ":00";
    
    $db = DB::connect($dsn, true);

    if (DB::isError($db)) {
        die ($db->getMessage());
    }
    $sql1 = "INSERT INTO license_usage (flmusage_server,flmusage_product,flmusage_date,flmusage_time,flmusage_users) ";
    if (isset($usage) && is_array($usage)) {
        # build sql stmt
        $sql = $sql1 . "VALUES ('$usage[0]','$usage[1]','$date','$time',$usage[2])";
        
        # build rrd stmt
        # rrd filename is combination of server and feature
        $cleanName = cleanHostname($usage[0]);
        $name = $cleanName . "-" . strtolower($usage[1]);
        $payload = $usage[2].":".$usage[3];

        if (isset($_GET["debug"]) && ($_GET["debug"]==1)) {
            print_sql($sql);
            print $name . " " .$payload . "<br>\n";
        } else {
            if ( isset($db_hostname) && isset($db_username) && isset($db_password) ) {
                $recordset = $db->query($sql);
                if (DB::isError($recordset)) {
                    print_sql($sql);
                    die ($recordset->getMessage());
                }       
            }
            if ( isset($rrdtool_bin) && is_executable($rrdtool_bin)) {
                $res = insert_into_rrd($name,$payload);
            }
        }
        
    }
    $db->disconnect();
    unset($array);
}

function cleanHostname($name) {
   if (strstr($name,'@')) {
        # remove port and domain (1234@foo.example.com > foo)
        preg_match("/\d+\@(\w+).*/",$name,$host);
    } elseif (strstr($name,'.')) {
        # remove domain (foo.example.com > foo)
        preg_match("/(\w+).*/",$name,$host);
    } elseif (strstr($name,' ')) {
        # remove spaces (foo1 foo2 foo3 > foo1_foo2_foo3)
        # pad array to 1
        $host = array(1=>preg_replace("/ /","_",$name));
    } else {
        # pad array to 1
        $host = array(1=>$name);
    }
    return strtolower($host[1]);
}
// function findServers
// called from index.php
// used to filter main server array down to more managable chunks
// can filter out all 'flexlm' licenses, or search for all services
// hosted from a single server

function findServers($needle,$key="type",$needle2=NULL,$key2=NULL) {
    global $server;
    $pos=array();
    for ($i=0;$i<sizeof($server);$i++) {
        if (!($needle2===NULL) && !($key2===NULL)) {
            if ((stristr($server[$i][$key],$needle)) && (stristr($server[$i][$key2],$needle2))) {
                //we need to get the real array position in there somehow
                //this pos is used for the links
                $splice=array_splice($server[$i],-1,-1,array($i));
                $pos[]=$server[$i];
            }
        }
        else {
            if (stristr($server[$i][$key],$needle)) {
                //we need to get the real array position in there somehow
                //this pos is used for the links
                $splice=array_splice($server[$i],-1,-1,array($i));
                $pos[]=$server[$i];
            }
        }
    }
    return $pos;
}

function getDetails($server) {
    # server[0] only defined if host was returned from findServers
    # so check to see if it exists, others default to 0
    if (isset($server[0])) {
        $pos = $server[0];
    } else {
        $pos = 0;
    }
    switch($server['type']) {
        case "flexlm":
            $ret=get_flexlm($server['hostname'],$pos);
            return $ret;
            break;
        case "spm":
            $ret=get_spm($server['hostname'],$pos);
            return $ret;
            break;
        case "rvl":
            $ret=get_rvl($server['hostname'],$pos);
            return $ret;
            break;
        case "sesi":
            $ret=get_sesi($server['hostname'],$pos);
            return $ret;
            break;
        case "tweak":
            $ret=get_tweak($server['hostname'],$pos);
            return $ret;
            break;
        case "rlm":
            $ret=get_rlm($server['hostname'],$pos);
            return $ret;
            break;
        case "pixar":
            $ret=get_pixar($server['hostname'],$pos);
            return $ret;
            break;
        default:
            die("no server type found");
    }
}

// function getTime
// called from common.php and version.php
// used to show how long page took to execute
function getTime() {
    $a = explode (' ',microtime());
    return(double) $a[0] + $a[1];
} 
                
// fucntion AppendStatusMsg
// used on index.php and by monitoring
// joins individual status msg into one string
function AppendStatusMsg($statusMsg,$msg="") {
    if ($msg!="") {
        //string needs to be a single line for javascript to display
        $statusMsg = $statusMsg . $msg . "<br>";
    }
    return $statusMsg;
}

function emailAlerts($host,$statusMsg) {
    global $notify_to;
    global $notify_from;
    global $notify_alert;
    global $URL;
    if (($notify_to) && ($statusMsg) && (muffleAlerts($host))) {
        $to      = $notify_alert . "," . $notify_to;
        $subject = "ALERT: License Server at " . $host['hostname'] . " is DOWN" ;
        $headers = "From: License Robot <" . $notify_from . ">\r\n" .
                    "Reply-To: " . $notify_from . "\r\n" .
                    "X-Mailer: PHP/" . phpversion();
        $msg = "Hostname: " . $host['hostname'] .
                "\nDescription: " . $host['desc'] .
                "\nType: " . $host['type'] .
                "\nMessage: " . $statusMsg .
                "\n\nURL: $URL";
        mail($to,$subject,$msg,$headers);
    }
}

//muffleAlerts
//return true if we want to muffle alerts for this host
function muffleAlerts($host) {
    require_once("DB.php");
    global $dsn;
    global $notify_resend;

    $mysqldate = date( 'Y-m-d H:i:s' );
    $alertdate = date('Y-m-d H:i:s', strtotime("-$notify_resend minutes"));

    $db = DB::connect($dsn, true);

    if (DB::isError($db)) {
        die ($db->getMessage());
    }

    # check for records
    $sql ="SELECT pkid from alert_events where type = '" . $host['type'] . "' and hostname = '" . $host['hostname'] . "' and datetime > '" . $alertdate . "' and datetime < '" . $mysqldate . "'";

    $recordset = $db->query($sql);
    if (DB::isError($recordset)) {
        print_sql($sql);
        die ($recordset->getMessage());
    }
    if ($recordset->numRows() == 0 ) {
        #nothing found
        $sql ="INSERT into alert_events(datetime,type,hostname) values('" . $mysqldate . "','". $host['type'] . "','" . $host['hostname'] . "')";
        $recordset = $db->query($sql);
        if (DB::isError($recordset)) {
            print_sql($sql);
            die ($recordset->getMessage());
        }
        return 1;
    } else {
        # we found a match!
        # muffle alerts
        return 0;
    }
}
 

function get_flexlm($server,$pos=0) {
    global $lmutil;
    global $today;
    #preload arrays
    $status_array=array();
    $license_array=array();
    $expiration_array=array();
    $user_array=array();

    # for [status]
    $service = "";
    $detaillink="<a href=\"details.php?listing=0&amp;server=" . $pos . "\">Details</a>" ;
    $listingexpirationlink="<a href=\"details.php?listing=1&amp;server=" . $pos . "\">Expiration dates</a>" ;
    $version="";
    $master="";
    $msg="";

    $total_licenses = 0;

    $fp = popen($lmutil . " lmstat -i -a -c " . $server,"r");

    while (!feof($fp)) {

        $line=fgets($fp,1024);

        # is it up? build [status]
        if (preg_match("/^(\w+|\w+\.\w+\.\w+): license server UP.*(\w\d+\.\d+).*/",trim($line),$res)) {
            $service="up";
            $master=$res[1];
            if (strpos($res[1],'.')) {
                $master = substr($res[1],0,strpos($res[1],'.'));
            }
            $version=$res[2];
        }
        if (preg_match("/Cannot connect to license server/",$line,$res)) {
            $service = "down";
            $detaillink="Details not available";
            $listingexpirationlink="Expiration dates not available";
            $msg="Cannot connect to $server";
            break;
        }

        if (preg_match("/Cannot read data/", $line, $out) ) {
            $service = "down";
            $detaillink="Details not available";
            $listingexpirationlink="Expiration dates not available";
            $msg="Cannot read data from $server";
            break;
        }

        if (preg_match("/Error getting status/", $line, $out) ) {
            $service = "down";
            $detaillink="Details not available";
            $listingexpirationlink="Expiration dates not available";
            $msg="Error getting status from $server";
            break;
        }

        /* Checking if vendor daemon has died evenif lmgrd is still running */
        if (preg_match("/vendor daemon is down/", $line, $out) ) {
            $service = "warning";
            $msg = "Vendor Daemon is down on $server";
            break;
        }

        ### END OF STATUS ###

        ### START OF LICENSES ###

        # look for features in the output. you will see stuff like
        # users of allegro_viewer: (total of 5 licenses available
        if ( preg_match('/(users of) (.*)(\(total of) (\d+) (.*) (total of) (\d+) /i', $line, $out) && !preg_match('/no such feature exists/i', $line) ) {
            $res = explode(":",trim($out[2]));
            $feature = $res[0];
            $license_array[$feature][] = array(
                "num_licenses"=> $out[4],
                "licenses_used" => $out[7]);
        }

        # nji: sometimes a vendor gives a "uncounted" file, with infinite licenses.
        if ( preg_match('/(users of) (.*)(\(uncounted, node-locked)/i', $line, $out) ) {
            $feature = $out[2];
            $license_array[$feature][] = array(
                "num_licenses" => "uncounted",
                "licenses_used" => "uncounted");
        }

        if ( preg_match('/(\w+)\s+(\d+|\d+.\d+)\s+(\d+)\s+(\d+-\w+-\d+)\s+(\w+)$/i', $line,$out) ) {
            # replace 1-jan-0 with 1-jan-2036
            $out[4] = str_replace("-jan-0","-jan-2036",$out[4]);
            # how many days remaining?
            $days_to_expiration = ceil ((strtotime($out[4]) - $today) / 86400);
            $expiration_array[$out[1]][] = array(
                    "vendor_daemon"=>$out[5],
                    "version"=>$out[2],
                    "expiration_date"=>$out[4],
                    "num_licenses"=>$out[3],
                    "days_to_expiration"=> $days_to_expiration,
                    "type"=>""
                );
        }
        ### END OF LICENSES ###

        ### START OF USERS ###
        if ( preg_match('/.* (\w\d+.\d+), vendor.*/', $line,$out) ) {
            $user_feature = $out[1];
        }

        # count the number of licenses. each used license has ", start" string in it
        if ( preg_match('/.*, start (\w+\s+\d+\/\d+\s\d+:\d+)/', $line,$out ) ){
            # Convert the date and time ie 12/5 9:57 to UNIX time stamp
            $time_checkedout = strtotime ($out[1]);
            $user_array[$feature][] = array(
                "line" => trim($line) . " (" . $user_feature . ")",
                "time_checkedout" => $time_checkedout);
        }

        ### END OF USERS ###
    }

    /* If I don't get explicit reason that the server is UP set the status to DOWN */
    if ( $service == "" ) {
        $class = "down";
        $detaillink="Details not available";
        $listingexpirationlink="Expiration dates not available";
        $msg="Unknown error from $server";
    }

    $status_array = array(
        "service"=>$service,
        "clients"=>$detaillink,
        "listing"=>$listingexpirationlink,
        "version"=>$version,
        "master"=>$master,
        "msg"=>$msg
        );

    # close open filehandle
    pclose($fp);
    $master = array(
        "status"=>$status_array,
        "licenses"=>$license_array,
        "expiration"=>$expiration_array,
        "users"=>$user_array
    );
    return $master;
}

function get_spm($server,$pos=0) {

    global $spmstat;
    global $today;
    global $permdate;

    # preload arrays
    $status_array=array();
    $license_array=array();
    $expiration_array=array();
    $user_array=array();
    // dirty hack to force the order of return
    $exec="";
    if (preg_split("/ /",trim($server))) {
        $spm_server = explode(" ",$server);
        foreach ($spm_server as $spm) {
            $exec .= sprintf("%s -s -Kd %s 2>&1 ; ",$spmstat,$spm);
        }
    } else {
        $exec = sprintf("%s -s -Kd %s 2>&1",$spmstat,$server);
    }
        
    $fp = popen($exec, "r");

    $service = "";
    $clients="<a href=\"details.php?listing=0&amp;server=" . $pos . "\">Details</a>" ;
    $listing="<a href=\"details.php?listing=1&amp;server=" . $pos . "\">Expiration dates</a>" ;
    $masters=array();
    $version="";
    $msg="";

    while ( !feof ($fp) ) {
        $line = fgets ($fp, 1024);


        ### START STATS ###

        /* Look for an expression like this ie. SPM-daemon on 'spmhost(spmhost.domain.com)' */
        if (preg_match("/SPM-daemon on \'(\w+).*/", $line,$res ) ) {
            $service = "up";
            $master=$res[1];
            array_push($masters,$master);
        }

        if (preg_match("/SPMD-version:(\d+\.\d+\.\d+).*/", $line,$res)) {
            $version=$res[1];
        }
        if (preg_match("/Can\'t Connect to (\w+).*/i", $line, $res) ) {
            $service = "down";
            $clients="Details not available";
            $listing="Expiration dates not available";
            $msg="Cant connect to $res[1]";
            break;
        }
        ### END STATUS ###

        ### START EXPIRATION/LICENSE ###

        if ( preg_match("/.* FL/",$line, $out) ) {
            $license = preg_split ("/\s+/", trim($out[0]));
            # PERM XSI license doesnt have date fields, check size of $license
            if (sizeof($license) > 5) {
                $days_to_expiration = ceil((strtotime($license[6]) - $today ) / 86400 );
            } else {
                $days_to_expiration = ceil((strtotime($permdate) - $today) / 86400 );
                $license[6] = "permanent";
            }
            # build expiration_array

            $expiration_array[$license[0]][] = array(
                "vendor_daemon" => "spm",
                "version" => $license[1],
                "expiration_date" => $license[6],
                "num_licenses" => $license[2],
                "licenses_used" => $license[3],
                "days_to_expiration" => $days_to_expiration,
                "type" => $master );
            # build license_array
            $feature = $license[0];
            $license_array[$feature][] = array(
                "num_licenses" => $license[2],
                "licenses_used" => $license[3],
                "extra" => $master
                );

        }

        ### END EXPIRATION/LICENSE ###

        ### START CLIENTS ###

        if ( preg_match("/.*connected since (\d+\/\d+\/\d+\s+\d+:\d+:\d+)/", $line,$out) ) {
            $time_checkedout = trim($out[1]);
            $user_array[$feature][] = array("line" => trim($line),"time_checkedout" => strtotime($time_checkedout));
        }

        ### END CLIENTS ###


    }
    # close open filehandle
    pclose($fp);
   
    /* If I don't get explicit reason that the server is UP set the status to DOWN */
    if ( $service == "" ) {
        $service = "warning";
        $clients="Details not available";
        $listing="Expiration dates not available";
        $msg="No data returned from $server";
    }
    $masters=array_unique($masters);
    $ret="";
    foreach ($masters as $key) {
        $ret .= $key . " <br>";
    }
    $status_array = array(
        "service"=>$service,
        "clients"=>$clients,
        "listing"=>$listing,
        "version"=>$version,
        "master"=>$ret,
        "msg"=>$msg
        );

    $master = array("status"=>$status_array,"licenses"=>$license_array,"expiration"=>$expiration_array,"users"=>$user_array);
    return $master;
}

function get_rvl($server, $pos=0) {
    global $rvlstatus;
    global $today;
    global $permdate;

    # preload arrays
    $status_array=array();
    $license_array=array();
    $expiration_array=array();
    $user_array=array();

    $service="";
    $clients="<a href=\"details.php?listing=0&amp;server=".$pos."\">Details</a>";
    $listing="<a href=\"details.php?listing=1&amp;server=".$pos."\">Expiration dates</a>";
    $master="";
    $version="";
    $msg="";

    //add redirection so we can get stderr
    $fp = popen("$rvlstatus 2>&1","r");

        $x = -1;

    while (!feof($fp)) {
        $line = fgets ($fp,1024);
        $line = trim($line);

        ### START STATUS ###
        if (preg_match("/RE:Vision Effects Floating License Status (\d+\.\d+)/",$line,$res)) {
            $d_version=$res[1];
        }
        if (preg_match("/License #1/",$line,$res)) {
            $service="up";
        }
        if (preg_match("/Server: (\w+).*/",$line,$res)) {
            $master=$res[1];
        }
        if (preg_match("/Could not find network license information/",$line,$res)) {
            $service="down";
            $clients="Details not available";
            $listing="Expiration dates not available";
            $msg="Could not find network license information from $server";
            break;
        }
        if (preg_match("/No network license server is defined for this machine/",$line,$res)) {
            $service="down";
            $clients="Details not available";
            $listing="Expiration dates not available";
            $msg="No network license server is defined for $server";
            break;
        }

        if (preg_match("/ERROR/",$line,$res)) {
            $service="down";
            $clients="Details not available";
            $listing="Expiration dates not available";
            $msg="Unable to connect to $server";
            break;
        }

        ### END STATUS ###
        ### START LICENSE/EXPIRATION ###

        if (preg_match("/^Type: \w+:\s+(\d+)\s+.*/i",$line,$res)) {
            $x++;
            $num_licenses[$x] = $res[1];
        }
        if (preg_match("/^Product\(s\): (\w+)/",$line,$res)) {
            $vendor[$x]=$res[1];
        }
        if (preg_match("/^Version: (\w\d.\d)/",$line,$res)) {
            $l_version[$x] =$res[1];
        }
        if (preg_match("/^Host system: (\w+)/",$line,$res)) {
            $type[$x]=$res[1];
        }
        if (preg_match("/^Expiry date: (\w+):\s+(\w+):\s+(\w+)/",$line,$res)) {
            $date = $res[3];
            if ($date == "never") {
                $exp_date[$x] = $permdate;
            } else {
                $exp_date[$x] = $date;
            }
        }
        if (preg_match("/^Product type: (\w+)/",$line,$res)) {
            $feature[$x] = $res[1];
        }
        if (preg_match("/^Current clients: (\d+)/",$line,$res)) {
            $licenses_used[$x] = $res[1];
        }

        ### END EXPIRATION ###

        ### START USERS ###
        # grab checked out lics now 
        if ( preg_match('/.*\:\s(\w+\s+\w+\s+\d+\s+\d+:\d+:\d+\s\d{4})/i',$line,$out)) {
            $user_array[$feature[$x]][] = array(
                "line" => trim($out[0]),
                "time_checkedout" => strtotime($out[1])
            );
        }

        ### END USERS ###
    }
    # close open filehandle
    pclose($fp);


    # recreate license_array and expiration array
    for ($t=0;$t<sizeof($feature);$t++) {
        $license_array[$feature[$t]][] = array(
            "num_licenses" => $num_licenses[$t],
            "licenses_used" => $licenses_used[$t]
        );
        $expiration_array[$feature[$t]][] = array(
            "vendor_daemon" => $vendor[$t],
            "version" => $l_version[$t],
            "expiration_date" => $exp_date[$t],
            "num_licenses" => $num_licenses[$t],
            "days_to_expiration" => ceil((strtotime($exp_date[$t]) - $today ) / 86400 ),
            "type" => $type[$t]
        );
    }


    $status_array = array(
        "service"=>$service,
        "clients"=>$clients,
        "listing"=>$listing,
        "version"=>$d_version,
        "master"=>$master,
        "msg"=>$msg
    );

    $master = array(
        "status"=>$status_array,
        "licenses"=>$license_array,
        "expiration"=>$expiration_array,
        "users"=>$user_array
    );
    return $master;
}

function get_sesi($server,$pos=0) {
    global $sesictrl;
    global $today;
    $year = date('Y');
    $x = -1;

    # preload arrays
    $status_array=array();
    $license_array=array();
    $expiration_array=array();
    $user_array=array();

    $service="down";
    $clients="Details not available";
    $listing="Expiration dates not available";
    $master="";
    $version="";
    $msg="$server unavailable";

    $fp = popen($sesictrl . " -v -i -h " . $server . " 2>&1","r");
    while (!feof($fp)) {
        $line = fgets ($fp,1024);

        ### START STATUS ###

        if (preg_match("/----- SERVER (\w+).*/",trim($line),$res)) {
            $master=$res[1];
            $service="up";
            $clients="<a href=\"details.php?listing=0&amp;server=".$pos."\">Details</a>" ;
            $listing="<a href=\"details.php?listing=1&amp;server=".$pos."\">Expiration dates</a>" ;
            $msg="";
        }
        if (preg_match("/sesinetd: Version (\d+\.\d+\.\d+).*/",trim($line),$res)) {
            $version=$res[1];
        }

        ### END STATUS ###

        ### START LICENSES/EXPIRATION ###

        if ( preg_match('/(\w+):\s+(\d+)\s+\"(\w+|\w+-\w+)\s+(\d+.\d+)\s+\"\s+(\w+)\s+(\d+-\w+-\d{4}).*/i',$line,$out)) {
            $x++;
            $feature = $out[3];
            $features[$x] = $feature;
            $num_licenses[$x] = $out[2];
            # if license has expired, its total used is reported as 0
            # it also doesnt list how many 'free', so lets force it
            if ($out[2] == 0) {
                $licenses_used[$x] = "0";
            }
            
            $expiration_array[$feature][] = array(
                "vendor_daemon" => $out[5],
                "version" => $out[4],
                "expiration_date" => $out[6],
                "num_licenses" => $out[2],
                "days_to_expiration" => ceil((strtotime($out[6]) - $today ) / 86400 ),
                "type" => $out[1]
            );
            $extra[$x] = $out[1];
        }
        if ( preg_match('/(\d+)\s+\w+\s+\w+$/i',$line,$out3)) {
            $licenses_used[$x] = $out3[1];
        }

        ### END LICENSES/EXPIRATION ###

        ### START USERS ###

        if ( preg_match('/.*\s(\w+\s+\d+)\s+(\d+\:\d+)/i',$line,$out2)) {
            $user_array[$feature][] = array(
            "line" => $out2[0],
            "time_checkedout" => strtotime($out2[1] . " " . $year . " " . $out2[2])
            );
        }

        ### END USERS ###
    }
    //build license_array
    for ($t=0;$t<sizeof($features);$t++) {
        $license_array[$features[$t]][] = array(
            "num_licenses" => $num_licenses[$t],
            "licenses_used" => ($num_licenses[$t] - $licenses_used[$t]),
            "extra" => $extra[$t],
        );
    }
    //build status array
    $status_array=array(
        "service"=>$service,
        "clients"=>$clients,
        "listing"=>$listing,
        "version"=>$version,
        "master"=>$master,
        "msg"=>$msg
    );
    //build master array
    $master = array(
        "status"=>$status_array,
        "licenses"=>$license_array,
        "expiration"=>$expiration_array,
        "users"=>$user_array
    );
    return $master;
}

function get_tweak($server, $pos=0) {
    global $tlm_server;
    global $today;

    # preload arrays
    $status_array=array();
    $license_array=array();
    $expiration_array=array();
    $user_array=array();
    
    // defaults
    $service="down";
    $clients="Details not available";
    $listing="Expiration dates not available";
    $master="";
    $version="";
    $msg="Unable to connect to $server";
    
    $fp = popen($tlm_server . " -s -h " . $server,"r");
    while (!feof($fp)) {
        $line = fgets ($fp,1024);

        ### START STATUS ###
        if (preg_match('/RESPONSE:\s+\w+\s+\w+\s+\w+\s+\w+\s+\'(\w+)\'.*$/i',trim($line),$res)) {
            $master=$res[1];
            $service="up";
            $clients="<a href=\"details.php?listing=0&amp;server=".$pos."\">Details</a>" ;
            $listing="<a href=\"details.php?listing=1&amp;server=".$pos."\">Expiration dates</a>" ;
            $msg="";
        }
        if (preg_match('/Version:\s+(\d+.\d+.\d+).*/i',trim($line),$res)) {
            $version=$res[1];
        }
        ### END STATUS ###

        ### START LICENSE/EXPIRATION ###
        if (preg_match('/^(\w+.\d)\W\s+(\w+\s+\d+)\W+\s+\W+\s+(\d+)\s+\w+\s+\w+\W+\s+(\d+)\s+\w+\W+\s+(\d+)\s+\w+\W+\s+\w+\s+\w+\s+(\d+-\w+-\d+)/',trim($line),$license)) {
            $feature = str_replace(" ","_",trim($license[2]));
            $days_to_expiration = ceil((strtotime($license[6]) - $today ) / 86400 );
            $license_array[$feature][] = array(
                "num_licenses" => $license[5],
                "licenses_used" => $license[3]
            );
            $expiration_array[$feature][] = array(
                "vendor_daemon" => "tlm",
                "version" => $license[2],
                "expiration_date" => $license[6],
                "num_licenses" => $license[5],
                "days_to_expiration" => $days_to_expiration,
                "type" => ""
            );
        }
        ### END LICENSE/EXPIRATION ###

        ### START USERS ###
        if ( preg_match("/.*checked out at (\w+\s+\w+\s+\d+\s+\d+:\d+:\d+\s+\d+)/",$line,$out) ) {
            $user_array[$feature][] = array(
                "line" => trim($line),
                "time_checkedout" => strtotime($out[1])
            );
        }
        ### END USERS ###
    }
    //build status array
    $status_array=array(
        "service"=>$service,
        "clients"=>$clients,
        "listing"=>$listing,
        "version"=>$version,
        "master"=>$master,
        "msg"=>$msg
    );

    $master = array(
        "status"=>$status_array,
        "licenses"=>$license_array,
        "expiration"=>$expiration_array,
        "users"=>$user_array
    );
    return $master;
}

function get_rlm($server,$pos=0) {
    global $rlmstat;
    global $permdate;
    global $today;

    # preload arrays
    $status_array=array();
    $license_array=array();
    $expiration_array=array();
    $user_array=array();

    $service="down";
    $clients="Details not available";
    $listing="Expiration dates not available";
    $master="";
    $version="";
    $msg="Unable to connect to $server";

    $fp = popen($rlmstat . " -a -c " . $server,"r");
    while (!feof($fp)) {
        $line = fgets ($fp,1024);

        ### START STATUS ###
        if (preg_match('/rlm status on (\w+).*/',trim($line),$res)) {
            $master=$res[1];
            $service="up";
            $clients="<a href=\"details.php?listing=0&amp;server=".$pos."\">Details</a>" ;
            $listing="<a href=\"details.php?listing=1&amp;server=".$pos."\">Expiration dates</a>" ;
            $msg="";
        }
        if (preg_match('/rlm software version (\w\d+.\d+).*/',trim($line),$res)) {
        $version=$res[1];
        }
        #check for failed ISV
        if (preg_match('/^(\w+)\s+\d+\s+(\w+)\s+(\d+)/',trim($line),$res)) {
            if ($res[2] != "Yes") {
                $service="warning";
                $msg="ISV $res[1] appears to be down on $server";
            }
        }
        ### END STATUS

        ### START LICENSES ###
        if (preg_match('/^(\w+)\s+(\w\d[.]\d)$/i',trim($line),$license)) {
            $feature = $license[1];
        } elseif (preg_match('/^[c]\w+.\s+(\d+)+.*[,]\s\w+.\s+(\d+)[,]/i',trim($line),$license)) {
            $license_array[$feature][] = array(
                "num_licenses" => $license[1],
                "licenses_used" => $license[2]
            );
        }
       if ( is_array($license_array) ) {
            if ( preg_match('/^(\w+)\s+\w\d[.]\d.\s+(\w+[@]\w+)\s+\d.\d\s+\w+\s+(\d+.\d+\s+\d+.\d+).*/i',trim($line),$user) ) {
                $feature = $user[1];
                $time_checkedout = $user[3];
                $user_array[$feature][] = array(
                    "line" => $line,
                    "time_checkedout" => strtotime($time_checkedout)
                );
            }
        }
        ### END LICENSES ###

        ### START EXPIRATION ###
        if (preg_match("/(\w+) license pool status.*/i",$line,$res)) {
            $vendor=$res[1];
        }
        # check for multiple string types:
        # foo v1.1
        # foo v300
        # foo v2012.1211
        if (preg_match('/^(\w+)\s+(\w\d[.]\d|\w\d+|\w\d+[.]\d+)$/i',trim($line),$license)) {
            $feature = $license[1];
            $ver = $license[2];
        //} elseif (preg_match('/^[c]\w+.\s+(\d+)+.*[,]\s\w+.\s+(\d+)/i',trim($line),$license)) {
        } elseif (preg_match('/^[c]\w+.\s+(\d+)+.*[,]\s\w+.\s+(\d+)[,]\s+\w+.\s+(\d+\-\w+\-\d{4}|\w+)/i',trim($line),$license)) {
            if ($license[3] == "permanent") {
                $expiration_date = $permdate;
            } else {
                $expiration_date = $license[3];
            }
           $expiration_array[$feature][] = array(
                "vendor_daemon" => $vendor,
                "version" => $ver,
                "expiration_date" => $license[3],
                "num_licenses" => $license[1],
                "days_to_expiration" => ceil((strtotime($expiration_date) - $today) / 86400),
                "type" => ""
            );
        }
        ### END EXPIRATION ###
    }
    //build final array
    $status_array=array(
        "service"=>$service,
        "clients"=>$clients,
        "listing"=>$listing,
        "version"=>$version,
        "master"=>$master,
        "msg"=>$msg
    );

    $master = array(
        "status"=>$status_array,
        "licenses"=>$license_array,
        "expiration"=>$expiration_array,
        "users"=>$user_array
    );
    return $master;
}

function get_pixar($server,$pos=0) {
    global $pixar_query;
    global $permdate;
    global $unlimited;
    global $today;

    # preload arrays
    $status_array=array();
    $license_array=array();
    $expiration_array=array();
    $user_array=array();

    $service="down";
    $clients="Details not available";
    $listing="Expiration dates not available";
    $master="";
    $version="";
    $msg="Unable to connect to $server";

    $fp = popen($pixar_query . " " . $server,"r");
    while (!feof($fp)) {
        $line = fgets ($fp,1024);

        ### START STATUS ###
        if (preg_match("/(\w+)\s(\w\d.\d)\s.*/",$line,$res)) {
            $service = "up";
            $clients="<a href=\"details.php?listing=0&amp;server=".$pos."\">Details</a>";
            $listing="<a href=\"details.php?listing=1&amp;server=".$pos."\">Expiration dates</a>";
            $version = $res[2];
            $msg="";
        }
        if (preg_match("/hostinfo:\s+(\w+)\s.*/",$line,$res)) {
            $master = $res[1];
        }
        ### END STATUS

        ### START LICENSES / EXPIRATION ###
        //    0 / 2      v15.000 PhotoRealistic-RenderMan                   10-feb-2011
        //    0 / *       v1.000 RPS-13.5                                   10-feb-2011
        if (preg_match("/(\w+)\s(\w\d.\d)\s.*/",$line,$res)) {
            $vendor = $res[1];
        }
        if (preg_match('/^(\d+)\s+\/\s+(\d+|\*)\s+(\w\d+.\d+)\s+([A-Za-z0-9-.]+)\s+([A-Za-z0-9-]+)/',trim($line),$res)) {
            $num_licenses = $res[2];
            if ($num_licenses == '*') {
                $num_licenses = $unlimited;
            }
            $license_array[$res[4]][] = array(
                "num_licenses"=>$num_licenses,
                "licenses_used"=>$res[1]
                );
            $expiration_array[$res[4]][] = array(
                "vendor_daemon" => $vendor,
                "version" => $res[3],
                "expiration_date" => $res[5],
                "num_licenses" => $num_licenses,
                "days_to_expiration" => ceil((strtotime($res[5])-$today) / 86400),
                "type" => $today
            );
        }
        ### END EXPIRATION ###
        ### START USERS ###
        // cmcnish@sf003c,9499            (69104b1:565d03a9)
        // 4    PRMan-Linux-x86-64        17.000  22-Jan-14:46 (31m)
        // 4    PhotoRealistic-RenderMan  17.000  22-Jan-14:46 (31m)
        // 4    RPS-17.0                   1.000  22-Jan-14:46 (31m)
        //if (preg_match("/^(\w+\@\   w


    }
    //build final array
    $status_array=array(
        "service"=>$service,
        "clients"=>$clients,
        "listing"=>$listing,
        "version"=>$version,
        "master"=>$master,
        "msg"=>$msg
    );

    $master = array(
        "status"=>$status_array,
        "licenses"=>$license_array,
        "expiration"=>$expiration_array,
        "users"=>$user_array
    );
    return $master;

}

?>
