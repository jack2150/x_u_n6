<div>
<?php
/**
 * This script test all mysqld connection to file servers
 * It test the connection
 * It test dlreadly & dlsession table together
 * It also test the httpd ping
 */

/**
 * This crontab change the upload server on 2,7,11,18 hours
 */
set_time_limit(10);

include ("../includes/http.php");  

// server id
$server_id = $_POST["server_id"];

/**
 * Step 1: Get all record from database 
 */
// open file server database 
include_once("../config.php");

// open sql connection and select databases
$conn = mysql_connect($sql_host, $sql_user, $sql_pass) 
	or die ('Error connecting to mysql');
	
if (mysql_select_db($sql_database)) {
	// get all servers from 
	$servers = mysql_query("select * from server where server_id = {$server_id} limit 1");
}
// close connection
mysql_close($conn);


// starting test every connections

while($server=mysql_fetch_assoc($servers)) {
	//print_r($server);
	
	echo "<b>".$server["name"] . " " . $server["http"] . $server["domain"] . "</b> - ";
	//echo $server["sql_host"] . " " . $server["sql_username"] . " " . $server["sql_password"] . " " . $server["sql_db"] . "<br />" . "<br />";

	// 1.2a : test sql connection
	/*
	$conn = mysql_connect($server["sql_host"], $server["sql_username"], $server["sql_password"]);
	if (!$conn) {
	 echo "<span style='color: #FF0000'>Server: " . $server["name"] . " , Cannot Connect!!!</span>". "<br />" . "<br />";
	}
	
	// 1.2b : test open database
	
	if ($conn) {
		
		
		if (mysql_select_db($server["sql_db"])) {

			// 1.2c : test insert table dlready

			$test = 0;
			$result = mysql_query("insert into dlready values 
				('','AAAAAAAAA','path','0','1200000000','0','1','1048576','1','1','0','1','','1','aaaaaa111111','123','127.0.0.1',0)");
			if (!$result) {
			    echo "<span style='color: #FF0000'>Could not query: " . $server["name"] . " " . mysql_error(). "<br />" . "<br />";
			    $test = 2;
			}
			else {
				// if success then delete that query
				mysql_query("delete from dlready where upload_id='AAAAAAAAA' and file='path'");
				$test = 1;
			}
			$result = "";
			
			
			// 1.2d : test insert table dlsession
			$result = mysql_query("insert into dlsessions values 
				('','AAAAAAAAA','0','127.0.0.1','1200000000','1200000000','0','0','0','1','1','0','aaaaaa111111')");
			if (!$result) {
			    die("<span style='color: #FF0000'>Could not query: " . $server["name"] . " " . mysql_error());
			    $test = 3;
			}
			else {
				// if success then delete that query
				mysql_query("delete from dlsessions where upload_id='AAAAAAAAA' and access_key='aaaaaa111111'");
				$test = 1;
			}
			$result = "";
			
			
			if ($test == 1) {
				echo "<span style='color: #009900'>Server Database: " . $server["name"] . " is ALL OK!</span>". "<br />" . "<br />";
			}
			elseif ($test == 2) {
				echo "<span style='color: #FF0000'>Could Insert query to: " . $server["name"] . " DLREADY TABLE!!!</span>". "<br />" . "<br />";
			}
			elseif ($test == 3) {
				echo "<span style='color: #FF0000'>Could Insert query to: " . $server["name"] . " DLSESSIONS TABLE!!!</span>". "<br />" . "<br />";
			}
		}
		else {
			die ("<span style='color: #FF0000'>Server: " . $server["name"] . " , Cannot Open Database!!!</span>". "<br />" . "<br />");
		}
		
	}
	mysql_close($conn);

	flush();
	*/
	
	/**
	 * 1.3: after test all the mysql connection and query, start testing httpd service
	 */
    $res = getres($server["http"].$server["domain"]);
	if ( substr( $res[code], 0, 1 ) == "3" ) {
    	echo "<span style='color: #009900'>httpd: " . $server["name"] . " redirect to Index, so is working FINE!</span> - ";
    }
    else {
    	echo "<span style='color: #FF0000'>httpd: " . $server["name"] . " , No Redirect, Error!!!</span> - ";
    }


	
	flush();
	
	echo "Max Threads - ".get_maxthread($server["domain"]);
	
	echo " <br /><hr /><br />";
	
	flush();
	
	// test every connection need wait 1 seconds
	//sleep(1);
}


function getres( $uri )
{
    $http = new http_class( );
    $http->follow_redirect = 0;
    $error = $http->getrequestarguments( $uri, $arguments );
    $arguments['Headers']['Pragma'] = "nocache";
    $error = $http->open( $arguments );
    if ( $error == "" )
    {
        $error = $http->sendrequest( $arguments );
        if ( $error == "" )
        {
            $headers = array( );
            $error = $http->readreplyheaders( $headers );
            if ( $error == "" )
            {
                $red_location = $headers['location'];
                do
                {
                    $error = $http->readreplybody( $body, 4096 );
                    if ( strlen( $body ) == 0 )
                    {
                        break;
                    }
                    $content .= $body;
                } while ( 1 );
            }
        }
    }
    $http->close( );
    return array( "code" => $http->response_status, "loc" => $red_location, "body" => $content );
}

function get_maxthread ($domain) {
	return file_get_contents("http://".$domain."/admin/httpd_process_numbers.php");
	
}

?>
</div>
