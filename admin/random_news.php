<p>
<?php
if ($_POST["act"] == "randnews") {

		// prepare date id variable
		$date_id = $_POST["year"].$_POST["month"].$_POST["day"];
		
		// check max news data is 3000
		$news_data = strlen($_POST["news"]) > 3000 ? substr($_POST["news"], 0, 3000) : $_POST["news"];
		
		// replace ' and "
		$news_data = mysql_real_escape_string($news_data);
		
		//echo $news_data . "<br>";
		
		// insert into database
		$db->setQuery("INSERT INTO randnews VALUES ('{$date_id}','{$news_data}')");
		$db->query();
		
		echo "<h2>News Inserted! For date: {$date_id}</h2>";
		
		// new date
		$new_date = mktime(0,0,0,$_POST["month"],$_POST["day"]+1,$_POST["year"]);
		
		$year = date("Y",$new_date);
		$month = date("m",$new_date);
		$day = date("d",$new_date);



}
elseif ($_POST["act"] == "keyword") {
    $keyword_char = $input["keyword"];
    $keyword_url = urlencode($input["keyword"]);

    // insert into database
    $db->setQuery("INSERT INTO keywords VALUES ('','{$keyword_char}','{$keyword_url}')");
    $db->query();

    echo "<h2>Keyword Inserted! Remember is Simplified & Traditional Chinese!</h2>";
}
else {
	// not insert
	$year = date("Y");
	$month = date("m");
	$day = date("d");
}



?>
</p>

<form action="" method="post">
<input name="act" type="hidden" value="randnews" />
<table border="0">
  <tr>
    <td>Date:</td>
    <td>
	<label> Year: 
    <select name="year" size="1">
	<option id="y2010" value="2010">2010</option>
	<option id="y2011" value="2011">2011</option>
	<option id="y2012" value="2012">2012</option>
	<option id="y2013" value="2013">2013</option>
	<option id="y2014" value="2014">2014</option>
	<option id="y2015" value="2015">2015</option>
	<option id="y2016" value="2016">2016</option>
	</select>
	</label>
	
	<label> Month: 
    <select name="month" size="1">
	<option id="m01" value="01">1</option>
	<option id="m02" value="02">2</option>
	<option id="m03"  value="03">3</option>
	<option id="m04"  value="04">4</option>
	<option id="m05"  value="05">5</option>
	<option id="m06"  value="06">6</option>
	<option id="m07"  value="07">7</option>
	<option id="m08"  value="08">8</option>
	<option id="m09"  value="09">9</option>
	<option id="m10"  value="10">10</option>
	<option id="m11"  value="11">11</option>
	<option id="m12"  value="12">12</option>
	</select>
	</label>
	
	<label> Day: 
    <select name="day" size="1">
	<option id="d01" value="01">1</option>
	<option id="d02" value="02">2</option>
	<option id="d03" value="03">3</option>
	<option id="d04" value="04">4</option>
	<option id="d05" value="05">5</option>
	<option id="d06" value="06">6</option>
	<option id="d07" value="07">7</option>
	<option id="d08" value="08">8</option>
	<option id="d09" value="09">9</option>
	<option id="d10" value="10">10</option>
	<option id="d11" value="11">11</option>
	<option id="d12" value="12">12</option>
	<option id="d13" value="13">13</option>
	<option id="d14" value="14">14</option>
	<option id="d15" value="15">15</option>
	<option id="d16" value="16">16</option>
	<option id="d17" value="17">17</option>
	<option id="d18" value="18">18</option>
	<option id="d19" value="19">19</option>
	<option id="d20" value="20">20</option>
	<option id="d21" value="21">21</option>
	<option id="d22" value="22">22</option>
	<option id="d23" value="23">23</option>
	<option id="d24" value="24">24</option>
	<option id="d25" value="25">25</option>
	<option id="d26" value="26">26</option>
	<option id="d27" value="27">27</option>
	<option id="d28" value="28">28</option>
	<option id="d29" value="29">29</option>
	<option id="d30" value="30">30</option>
	<option id="d31" value="31">31</option>
	</select>
	</label>
    </td>
  </tr>
  <tr>
    <td>News:</td>
    <td>
	<textarea name="news" cols="60" rows="18"></textarea>
	</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" value="Insert News!" /></td>
  </tr>
</table>
</form>

<script type="text/javascript">
// select current date or last used date + 1
document.getElementById("y<?php echo $year; ?>").selected = true;
document.getElementById("m<?php echo $month; ?>").selected = true;
document.getElementById("d<?php echo $day; ?>").selected = true;
</script>

</p>

<p>
Keyword Filter:
<form action="" method="post">
<input name="act" type="hidden" value="keyword" />

<input name="keyword" type="text" maxlength="20" />
<input name="" type="submit" value="Add!" />
</form>

</p>
