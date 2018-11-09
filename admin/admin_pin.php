<?php
/**
 * [Apr 24 2010] Rework
 * 1. Generate PIN
 * 2. List PIN
 * 3. EDIT PIN
 * 4. DELETE PIN
 * 5. List Reseller
 * 6. Show Reseller
 * 7. Edit Reseller
 * 8. View Reseller Order
 */
/*
PIN TABLE
---------
CREATE TABLE IF NOT EXISTS `pin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pin` varchar(24) NOT NULL,
  `pass` int(8) NOT NULL,
  `type` varchar(255) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `price` float(8,2) NOT NULL DEFAULT '0.00',
  `activate_time` int(12) NOT NULL DEFAULT '0',
  `length` int(4) NOT NULL DEFAULT '1',
  `create_time` int(12) NOT NULL DEFAULT '0',
  `expire_time` int(12) NOT NULL DEFAULT '0',
  `reseller_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

RESELLERS TABLE
---------------
CREATE TABLE IF NOT EXISTS `resellers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(120) NOT NULL DEFAULT 'waiting',
  `type` varchar(120) NOT NULL DEFAULT 'normal',
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `regdate` int(12) NOT NULL,
  `ip` varchar(15) NOT NULL DEFAULT '127.0.0.1',
  `email` varchar(255) NOT NULL,
  `telephone` varchar(200) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `address` text,
  `country` varchar(255) NOT NULL,
  `clientpay` varchar(255) DEFAULT NULL,
  `customerpay` varchar(255) DEFAULT NULL,
  `introduction` text,
  `deposit` float(10,2) NOT NULL DEFAULT '0.00',
  `amounts` float(12,2) NOT NULL DEFAULT '0.00',
  `pins` int(10) NOT NULL DEFAULT '0',
  `updated` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
*/


function show_pin() {
	global $db, $input, $baseWeb;
	
	// set current page number
	$current_page = $input[page] ? $input[page] : 1;
	$number_per_page = $input[num] ? $input[num] : 20;
	
	// set query limit
	$start_num = $current_page * $input[num];
	
	// if got search
	if ($input[column_name]) {
		if ($input[column_value]) {
			$search_query = "$input[column_name] = '$input[column_value]'";
		}
		else {
			$search_query = "1";
		}
	}
	else {
		$search_query = "1";
	}
	
	if ($input[order_name]) {
		if ($input[order_sort]) {
			$order_query = "order by $input[order_name] $input[order_sort]";
		}
		else {
			$order_query = "order by p.id desc";
		}
	}
	else {
		$order_query = "order by p.id desc";
	}
	
	
	// get row from database
    $db->setQuery("select p.*, r.name as reseller_name from pin as p left join resellers as r on p.reseller_id = r.id where $search_query $order_query limit $start_num, $number_per_page");
    $db->query();
    $rows = $db->loadrowlist( );
    
    // show it
    ?>
<table class=adminlist align=center>
  <tr>
  	<th align="center" colspan="9">pin List: </th>
  </tr>
   <tr>
    <td class=tdrow1>&nbsp;</td>
    <td class=tdrow1><b>pin Code<b></td>
    <td class=tdrow1><b>pin Type<b></td>
    <td class=tdrow1><b>Status (used or not)<b></td>
    <td class=tdrow1><b>Premium Lenght<b></td>
    <td class=tdrow1><b>Create Date<b></td>
    <td class=tdrow1><b>Expire Date<b></td>
    <td class=tdrow1><b>Reseller Name<b>;</td>
    <td class=tdrow1>&nbsp;</td>
  </tr>
    <?php
    foreach ($rows as $row) {
    	echo "<tr>";
    	
    	$row[status] = $row[used] ? "Used (Activated on: " . date("M d, Y",$row[activate_time]) . ")" : "Nope";
    	
    	$used_color = $row[used] ? "style='color:#990000'" : "style='color:#339900'";
    	
    	$reseller_name = $row[reseller_id] ? $row[reseller_name] : "System Admin";
    	
    	echo "<td class=tdrow1><input name='pin_id' type='checkbox' value='{$row[id]}' /></td>";
    	echo "<td class=tdrow1><b $used_color>$row[pin]</b></td>";
    	echo "<td class=tdrow1>$row[type]</td>";
    	echo "<td class=tdrow1>$row[status]</td>";
    	echo "<td class=tdrow1>$row[length] Days</td>";
    	echo "<td class=tdrow1>".date("M d, Y",$row[create_time])."</td>";
    	echo "<td class=tdrow1>".date("M d, Y",$row[expire_time])."</td>";
    	echo "<td class=tdrow1><a href='index.php?admin=pin&column_name=p.reseller_id&column_value={$row[reseller_id]}&order_sort=asc&order_name=p.create_time&Search=search'>$reseller_name</a></td>";

    	echo "<td class=tdrow1><a href='$baseWeb/admin/index.php?admin=pin&act=del&id=$row[id]&page=$current_page&num=$number_per_page'>Delete It</a> - "
    		. "<a href='$baseWeb/admin/index.php?admin=pin&act=mod&id=$row[id]&page=$current_page&num=$number_per_page'>Modify It</a></td>";

    	
    	echo "</tr>";
    }
    
    ?>
</table>

</form>
    <?php
    
    generate_page_links();
    
    ?>

    
<form action="" method="get">
<input type="hidden" name="admin" value="pin">
<table class=adminlist align=center>
  <tr>
  	<th align="center" colspan="9">pin Search: </th>
  </tr>
  <tr>
    <td class='tdrow1' width="150">Field</td>
    <td class='tdrow1' width="100">Operator</td>
    <td class='tdrow1'>Value</td>
  </tr>
  <tr>
    <td class='tdrow1'>
	<select name="column_name">
	<option value="">Default</option>
	<option value="p.pin">PIN</option>
	<option value="p.type">Type</option>
	<option value="p.used">Used</option>
	<option value="p.length">Length</option>
	<option value="p.reseller_id">Reseller ID</option>
	</select>
	</td>
    <td class='tdrow1'>=</td>
    <td class='tdrow1'><input name="column_value" type="text" size="60" /> <br />Type in (system,freetrial,reseller) , Used in 1 or 0</td>
  </tr>
  <tr>
  <td class='tdrow1' width="150">Order By:</td>
  <td class='tdrow1' width="100">
	<select name="order_sort">
	<option value="asc">ASC</option>
	<option value="desc">DESC</option>
	</select>
  </td>
  <td class='tdrow1'>
  <select name="order_name">
  <option value="p.create_time">Date</option>
  <option value="p.reseller_id">Reseller</option>
  <option value="p.type">Type</option>
  </select>
  </td>
  </tr>
  <tr>
  <td class='tdrow1' colspan="3">
  <input name="Search" type="submit" value="search" />
  </td>
  </tr>
</table>
</form>
    <?php
}

// use to generate link for page
function generate_page_links() {
	global $db, $input, $baseWeb;
	
	// if got search
	if ($input[column_name]) {
		if ($input[column_value]) {
			$search_query = "$input[column_name] = '$input[column_value]'";
		}
		else {
			$search_query = "1";
		}
	}
	else {
		$search_query = "1";
	}
	
	if ($input[order_name]) {
		if ($input[order_sort]) {
			$order_query = "order by $input[order_name] $input[order_sort]";
		}
		else {
			$order_query = "order by p.id desc";
		}
	}
	else {
		$order_query = "order by p.id desc";
	}
	
	// set current page number
	$current_page = $input[page] ? $input[page] : 1;
	$number_per_page = $input[num] ? $input[num] : 20;
	
	// set query limit
	$start_num = $current_page * $input[num];
	
	// first sum total 
	$db->setQuery("select count(p.id) as total from pin as p where $search_query $order_query");
    $db->query();
    $row = $db->loadrow();
    
    $total_pin = $row[total];
	
	// divide by num per page
	$total_link = ceil($total_pin / $number_per_page);
	
	echo "<div class='pagelinks'>";
	
	$starting = 0;
	if ($current_page - 10 > 0) {
		$start_count = $current_page - 10;
	}
	else {
		$start_count = 1;
	}
	
	if ($current_page + 10 <= $total_link) {
		$until_count = $current_page + 10;
	}
	else {
		$until_count = $total_link;
	}
	
	$first_page = 1;

	$last_page = $total_link - 1;
	
	$next_page = "";
	if ($current_page + 1 < $total_link) {
		$next_page = $current_page + 1;
	}
	else {
		$next_page = $current_page;
	}
	
	$pre_page = "";
	if ($current_page - 1 > 0) {
		$pre_page = $current_page - 1;
	}
	else {
		$pre_page = 1;
	}
	
	// show page link
	echo "<a href='{$baseWeb}/admin/index.php?admin=pin&page=$first_page&num=$number_per_page'>[ << ]</a>";
	echo "<a href='{$baseWeb}/admin/index.php?admin=pin&page=$pre_page&num=$number_per_page'>[ < ]</a>";
	for ($i = $start_count; $i < $until_count; $i++) {
		if ($current_page == $i) {
			echo "$i";
		}
		else {
			echo "<a href='{$baseWeb}/admin/index.php?admin=pin&page=$i&num=$number_per_page'>$i</a>";
		}
	}
	echo "<a href='{$baseWeb}/admin/index.php?admin=pin&page=$next_page&num=$number_per_page'>[ > ]</a>";
	echo "<a href='{$baseWeb}/admin/index.php?admin=pin&page=$last_page&num=$number_per_page'>[ >> ]</a>";

	
	
	echo "</div>";
}

function modify_pin_form() {
	global $db, $input, $baseWeb;
	
    $db->setQuery("select * from pin where id=$input[id] limit 1");
    $db->query();
    $row = $db->loadrow( );
    
    
    
	
	?>
<form action="index.php?admin=pin&act=modify" method="post">
<input name="id" type="hidden" value="<?php echo $row[id]; ?>" />
<table class=adminlist align=center>
  <tr>
  	<th align="center" colspan=2>Modify pin Form: </th>
  </tr>
  <tr>
    <td class=tdrow1>PIN CODE:</td>
    <td class=tdrow1><input name="pin" type="text" maxlength="24" value="<?php echo $row[pin]; ?>" size="30" /></td>
  </tr>
  <tr>
    <td class=tdrow1>PIN PASS:</td>
    <td class=tdrow1><input name="pass" type="text" maxlength="8" value="<?php echo $row[pass]; ?>" size="30" /></td>
  </tr>
  <tr>
    <td class=tdrow1>USED:</td>
    <td class=tdrow1>
	<select name="used" size="1">
	<option value="1" <?php if ($row[used] == 1) { echo "selected='selected'";  } ?> >Yes</option>
	<option value="0" <?php if ($row[used] == 0) { echo "selected='selected'";  } ?> >No</option>
	</select>
    </td>
  </tr>
  <tr>
    <td class=tdrow1>Reseller ID:</td>
    <td class=tdrow1><input name="reseller_id" type="text" maxlength="6" value="<?php echo $row[reseller_id]; ?>" /> <br /> (0 represent owner)</td>
  </tr>
  <tr>
    <td class=tdrow1>PIN Price:</td>
    <td class=tdrow1><input name="price" type="text" maxlength="8" value="<?php echo $row[price]; ?>" /></td>
  </tr>
  <tr>
    <td class=tdrow1>Type:</td>
    <td class=tdrow1>
	<select name="pin_type" size="3">
	<option value="system" <?php if ($row[type] == 'system') { echo "selected='selected'";  } ?> >System / Private Use</option>
	<option value="reseller" <?php if ($row[type] == 'reseller') { echo "selected='selected'";  } ?> >Reseller</option>
	<option value="freetrial" <?php if ($row[type] == 'freetrial') { echo "selected='selected'";  } ?> >Free Trial / Coupon</option>
	</select>
	</td>
  </tr>
  <tr>
    <td class=tdrow1>Code Lenght:</td>
    <td class=tdrow1>
	<select name="pin_length" size="6">
	<option value="3" <?php if ($row[length] == '3') { echo "selected='selected'";  } ?> >3 Days</option>
	<option value="7" <?php if ($row[length] == '7') { echo "selected='selected'";  } ?> >7 Days</option>
	<option value="30" <?php if ($row[length] == '30') { echo "selected='selected'";  } ?> >30 Days / 1 Month</option>
	<option value="90" <?php if ($row[length] == '90') { echo "selected='selected'";  } ?> >90 Days / 9 Months</option>
	<option value="180" <?php if ($row[length] == '180') { echo "selected='selected'";  } ?> >180 Days / 6 Months</option>
	<option value="365" <?php if ($row[length] == '365') { echo "selected='selected'";  } ?> >365 Days / 1 Year</option>
	</select><br />
	(how long for premium user)
	</td>
  </tr>
  <tr>
    <td class=tdrow1>&nbsp;</td>
	<td class=tdrow1><input name="sumbit" type="submit" value="Modify It!" /></td>
  </tr>
</table>
</form>
	<?
}

function generate_pin_form() {
	// this function generate pin and save it in table
	
	?>
<form action="index.php?admin=pin&act=show_generate_pin" method="post">
<table class=adminlist align=center>
  <tr>
  	<th align="center" colspan=2>Generate pin Form: </th>
  </tr>
  <tr>
    <td class=tdrow1>Reseller ID:</td>
    <td class=tdrow1><input name="reseller_id" type="text" maxlength="6" value="0" /> <br /> (0 represent owner)</td>
  </tr>
  <tr>
    <td class=tdrow1>PIN Price:</td>
    <td class=tdrow1><input name="price" type="text" maxlength="8" value="0.00" /></td>
  </tr>
  <tr>
    <td class=tdrow1>Type:</td>
    <td class=tdrow1>
	<select name="pin_type" size="3">
	<option value="system">System / Private Use</option>
	<option value="reseller" selected="selected">Reseller</option>
	<option value="freetrial">Free Trail / Coupon</option>
	</select>
	</td>
  </tr>
  <tr>
    <td class=tdrow1>Total pin:</td>
    <td class=tdrow1><input name="total_pin" type="text" value="1" maxlength="3" /> <br />Maximum 999 pin</td>
  </tr>
  <tr>
    <td class=tdrow1>Code Lenght:</td>
    <td class=tdrow1>
	<select name="pin_length" size="6">
	<option value="3">3 Days</option>
	<option value="7">7 Days</option>
	<option value="30" selected="selected">30 Days / 1 Month</option>
	<option value="90">90 Days / 9 Months</option>
	<option value="180">180 Days / 6 Months</option>
	<option value="365">365 Days / 1 Year</option>
	</select><br />
	(how long for premium user)
	</td>
  </tr>
  <tr>
    <td class=tdrow1>&nbsp;</td>
	<td class=tdrow1><input name="sumbit" type="submit" value="Generate Now!" /> <input name="reset" type="reset" value="Reset" /></td>
  </tr>
</table>
</form>
	<?php
}

function generate_pin_now() {
	global $db,$input;
	
	$alphabets = array('a','b','c','d','e','f','g','h','i','j','k','l',
		'm','n','o','p','q','r','s','t','u','v','w','x','y','z');
	
	if (!$input[pin_type]) {
		echo "<h3>Please select pin type!!!</h3>";
	}
	
	if (!$input[pin_length]) {
		echo "<h3>Please enter total pin lenght!</h3><br>";
	}
	
	if (!$input[total_pin]) {
		echo "<h3>Please enter total generate numbers!</h3><br>";
	}
	
	// prepare query
	$insert_query = "INSERT INTO `pin` (`id` ,`pin`, `pass`,`type` ,`price` , `used` ,`activate_time` ,`length` ,`create_time` ,`expire_time` ,`reseller_id`) VALUES ";
	
	$current_time = time();
	$expire_time = $current_time + 2 * 365 * 24 * 60 * 60;
	
	// generate pin
	for ($i = 0; $i < $_POST[total_pin]; $i++) {
		// first generate a new pin code
		/**
		 * PIN CODE FORMAT:
		 * XUN6 - NAME
		 *	8DIGIT - RANDOM
		 *	4CHAR - RANDOM
		 *	6DIGIT - Date Create
		 *	4CHAR - Reseller ID
		 *	
		 *	Total: 4+8+4+6+4 = 18 Chars
		 *	
		 *	SAMPLE: XUN601234567UDLE1204100003
		 *	SAMPLE: XUN6-01234567-UDLE-120410-0003
		 */
		
		$company_name = "xun6";
		$random_6digits = mt_rand(10,99).mt_rand(100,999).mt_rand(3,9); // 6 digits
		$random_4chars = $alphabets[mt_rand(0,25)].$alphabets[mt_rand(0,25)].$alphabets[mt_rand(0,25)].$alphabets[mt_rand(0,25)]; // 4 char
		$generate_date = date("dmy",$current_time);
		$reseller_id = sprintf("%04d",$input["reseller_id"]);
		$pinpass = mt_rand(10000000,99999999);
		
		$pin_code = $company_name.$random_6digits.$random_4chars.$generate_date.$reseller_id;
		
		//$pin_code = strtoupper(substr(md5(mt_rand()),0,25));
		
		// separate it into a pin code
		/*
		for ($j = 0; $j < 20; $j+=5) {
			$real_pin .= substr($pin_code,$j,5) . "-";
		}
		$real_pin .= substr($pin_code,20,5);
		*/

		// check database exist
		$ok = 1;
		while ($ok) {
			$db->setQuery("select * from pin where pin = '$pin_code'");
			$db->query();
    		
    		if ($db->getNumRows()) {
    			// exist, generate again
				// first generate a new pin code
				$company_name = "xun6";
				$random_6digits = mt_rand(10,99).mt_rand(100,999).mt_rand(3,9); // 6 digits
				$random_4chars = $alphabets[mt_rand(0,25)].$alphabets[mt_rand(0,25)].$alphabets[mt_rand(0,25)].$alphabets[mt_rand(0,25)]; // 4 char
				$generate_date = date("dmy",$current_time);
				$reseller_id = sprintf("%04d",$input["reseller_id"]);
				$pinpass = mt_rand(10000000,99999999);
				
				$pin_code = $company_name.$random_6digits.$random_4chars.$generate_date.$reseller_id;
    			
    			$ok = 1;
    		}
    		else {
    			// not exist
    			$ok = 0;
    		}
		}
		
		$insert_values = "";
		$insert_values = " (NULL , '$pin_code', '$pinpass','$input[pin_type]' ,'$input[price]' , '0', '0', '$input[pin_length]', '$current_time', '$expire_time', '$input[reseller_id]')";
		$temp_query = $insert_query . $insert_values;
		
		// insert into table
		$db->setQuery($temp_query);
		$db->query();
		
		// delete pin code
		$pin_code = "";
	}
	
	// after than show generate pin
	
}



if ( !defined( "IN_ADMIN" ) )
{
    exit( "hack attempted!" );
}

$act = $input[act];
switch ( $act ) {
	case "del":
		// del a single pin
		$db->setQuery("delete from pin where id=$input[id] limit 1");
		$db->query();
		
		redirect( "admin=pin&page=$input[page]&num=$input[num]", "Deleted successfully!" );
		break;
	case "mod":
		modify_pin_form();
		break;
	case "modify":
		if ($input[used]) {
			$activate_time = time();
			$query = "used='$input[used]', activate_time = '$activate_time'";
		}
		else {
			$query = "used='$input[used]'";
		}
		
		$db->setQuery("update pin set pin='$input[pin]', pass='$input[pass]', $query, type='$input[pin_type]', price='$input[price]',
			 reseller_id='$input[reseller_id]',length='$input[pin_length]' where id='$input[id]'");
		$db->query();
		
		redirect( "admin=pin&act=mod&id=$input[id]", "Modify successfully!" );
		
		break;
    case "generate_pin_form" :
    	generate_pin_form();
    	// generate pin
 		break;
    case "show_generate_pin" :
		// delete pin
		generate_pin_now();
		redirect( "admin=pin", "Generate Successfully!" );
        break;
    case "search" :
		// search pin
        break;
    default :
        show_pin();
}

?>