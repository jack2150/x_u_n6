<?

  function showlistform ()
  {
    global $baseUrl;
    global $input;
    global $ThumbFile;
    global $db;
    $input[s] = intval ($input[s]);
    $per_num = intval ($_SESSION[report_pages]);
    if ($per_num == 0)
    {
      $_SESSION[report_pages] = $per_num = 10;
    }

    if (($orderad != 'ASC' AND $orderad != 'DESC'))
    {
      $orderad = '';
    }

    if (isset ($input[search]))
    {
      $orderby = $_SESSION[report_orderby] = $input[report_orderby];
      $orderad = $_SESSION[report_AD] = $input[report_AD];
      $_SESSION[report_field] = $input[report_field];
      $_SESSION[report_func] = $input[report_func];
      $_SESSION[report_values] = $input[report_values];
      $_SESSION[report_field2] = $input[report_field2];
      $_SESSION[report_func2] = $input[report_func2];
      $_SESSION[report_values2] = $input[report_values2];
      $_SESSION[report_pages] = $input[report_pages];
      $_SESSION[report_status] = $input[report_status];
    }

    $orderby = ($_SESSION[report_orderby] == '' ? 'date' : $_SESSION[report_orderby]);
    $orderad = ($_SESSION[report_AD] == '' ? 'DESC' : $_SESSION[report_AD]);
    $per_num = intval ($_SESSION[report_pages]);
    if ($per_num == 0)
    {
      $_SESSION[report_pages] = $per_num = 10;
    }

    if ($orderby)
    {
      $order = '' . 'ORDER BY ' . $orderby . ' ' . $orderad . ' ';
    }

    if ((($_SESSION[report_field] != '' AND $_SESSION[report_func] != '') AND $_SESSION[report_field] != 'u.username'))
    {
      if ($_SESSION[report_field] == 'date')
      {
        $condition = '' . $_SESSION['report_field'] . ' ' . $_SESSION['report_func'] . ' \'' . mysql_escape_string (strtotime ($_SESSION[report_values] . ' 00:00:00')) . '\'';
      }
      else
      {
        $condition = '' . $_SESSION['report_field'] . ' ' . $_SESSION['report_func'] . ' \'' . mysql_escape_string ($_SESSION[report_values]) . '\'';
      }
    }

    if ((($_SESSION[report_field2] != '' AND $_SESSION[report_func2] != '') AND $_SESSION[report_field2] != 'u.username'))
    {
      if ($_SESSION[report_field] == 'date')
      {
        $condition2 = '' . $_SESSION['report_field2'] . ' ' . $_SESSION['report_func2'] . ' \'' . mysql_escape_string (strtotime ($_SESSION[report_values2] . ' 00:00:00')) . '\'';
      }
      else
      {
        $condition2 = '' . $_SESSION['report_field2'] . ' ' . $_SESSION['report_func2'] . ' \'' . mysql_escape_string ($_SESSION[report_values2]) . '\'';
      }

      $condition .= ($condition ? ' AND ' . $condition2 : $condition2);
    }

    $condition .= ($condition == '' ? '1' : '');
    $db->setQuery ('' . 'select *
                  from reports as r
                  left join files as f on r.upload_id=f.id
                  where ' . $condition . '
                  ');
    $db->query ();
    $total = $db->getNumRows ();
    $cur_page = $input[s] / $per_num;
    $info = array ('total' => $total, 'page' => $per_num, 'cur_page' => $cur_page, 'baseUrl' => $baseUrl . ('' . '?&admin=report&type=images&' . $order));
    $pageLinks = buildpagelinks ($info);
    $db->setQuery ('' . 'select f.*,s.domain,r.*,u.user
                  from reports as r
                  left join files as f on r.upload_id=f.upload_id
                  left join users as u on u.id=f.uid
                  left join server as s on s.server_id=f.server_id
                  where ' . $condition . '
                  ' . $order . '
                  limit ' . $input['s'] . ',' . $per_num . '
                  ');
    $db->query ();
    $rows = $db->loadRowList ();
    showreportth ($input[type]);
    if ($rows)
    {
      foreach ($rows as $row)
      {
        $data = $row;
        $sourceWeb = 'http://' . $row[domain];
        $fileurl = '' . $sourceWeb . '/files/' . $row['file'];
        $data[fileurl] = $fileurl;
        $data[date] = date ('m/d/Y', $data[date]);
        $data[user] = ($row[uid] == 0 ? 'Guest' : $data[user]);
        showreportrow ($data);
        unset ($data);
      }
    }

    showreporttt ($pageLinks);
  }

  function showreportrow ($data)
  {
    global $baseUrl;
    echo '' . '    <tr><td align=left class=\'tdrow1\' width=10%>
    <input type=\'checkbox\' name=idList[' . $data['report_id'] . '_' . $data['upload_id'] . ']>
    </td>
    <td align=left class=\'tdrow1\' width=60%>
    <b>Reporter</b>:' . $data['fullname'] . '(<a href="mailto:' . $data['email'] . '">' . $data['email'] . '</a>,<a href="?admin=set&act=banip&ip=' . $data['ip'] . '">BAN ' . $data['ip'] . '</a> at ' . $data['date'] . ')
    <br><b>Subject</b>:' . $data['subject'] . '
    <br><b>Problem</b>:' . $data['problems'] . '
    <br><textarea cols=80 rows=5>
    ' . $data['details'] . '
    </textarea>
    </td><td class=\'tdrow1\' valign=top width=30%>
     <b>File ID</b>:' . $data['upload_id'] . '<br>
     <b>File Name</b>:' . $data['name'] . '<br>
     <b>File Downloads</b>:' . $data['downloads'] . '<br>
     <b>File Owner</b>:' . $data['user'] . '<br>
     <a href=' . $data['fileurl'] . '>File Link</a>
    </td></tr>';
  }

  function showreportth ($type)
  {
    global $baseUrl;
    $title = ucfirst ($type);
    echo '' . '<form name=myform action="?admin=report" method="POST">
<input type="hidden" name="act" value="dels">
<input type="hidden" name="admin" value="report">
    <table class=adminlist border=0 align=center width=100%>
    <tr>
		<th align="center" colspan=\'3\'>Files Reports</th>
    </tr>
    <tr>
    <td colspan=2 align=left>
    <input type=\'checkbox\' name=allbox onclick=checkAll()>  Select all
    </td>
    </tr>';
  }

  function showreporttt ($pageLinks)
  {
    global $baseUrl;
    global $input;
    $pid = intval ($input[id]);
    echo '' . '    <tr><td align=\'left\' class=\'tdrow1\' valign=\'middle\' colspan=3>
    <input class=plainborder  type="submit" name="delete" value="Delete"  onclick="return opConfirm(\'Are You Sure Delete These Reports?\',1)">
    ' . $pageLinks . '
</form>
<form name=jump action="?admin=report" method=get>
<input type="hidden" name="admin" value="report">
<table width=\'110%\' cellspacing=\'0\' cellpadding=\'5\' align=\'center\' border=\'0\'>
<tr><th colspan=3 align=center>Search Reports</th></tr>
<tr>
<td class=\'row3\' width=\'10%\' align=\'center\'><b>Field</b></td>
<td class=\'row3\' width=\'15%\' align=\'right\'><b>Operator</b></td>
<td class=\'row3\' width=\'60%\' align=\'center\'><b>Value</b></td>
</tr>
<tr>
<td class=\'row1\'  width=\'10%\'  align=\'left\'><select name="report_field">
<option value="">Default</option>
<option value="upload_id">Reported Files</option>
<option value="email">Reporter Email</option>
<option value="r.IP">Report IP</option>
<option value="date">Report Date</option>
</select></td>
<td class=\'row2\'  width=\'15%\'  align=\'right\'><select name="report_func"><option value="=">=</option><option value="&gt;">&gt;</option><option value="&lt;">&lt;</option><option value="&gt;=">&gt;=</option><option value="&lt;=">&lt;=</option><option value="!=">!=</option><option value="LIKE">LIKE</option><option value="NOT LIKE">NOT LIKE</option></select></td>
<td class=\'row1\'  width=\'60%\'  align=\'left\'><input type=\'text\' name=\'report_values\' value=\'' . $_SESSION['report_values'] . '\' size=\'25\' class=\'textinput\'>(wildcard: "%" when "like" or "not like",date format:m/d/y)</td>
</tr>
<tr>
<td class=\'row1\'  width=\'10%\'  align=\'left\'><select name="report_field2">
<option value="">AND</option>
<option value="upload_id">Reported Files</option>
<option value="email">Reporter Email</option>
<option value="r.IP">Report IP</option>
<option value="date">Report Date</option>
</select></td>
<td class=\'row2\'  width=\'15%\'  align=\'right\'><select name="report_func2"><option value="=">=</option><option value="&gt;">&gt;</option><option value="&lt;">&lt;</option><option value="&gt;=">&gt;=</option><option value="&lt;=">&lt;=</option><option value="!=">!=</option><option value="LIKE">LIKE</option><option value="NOT LIKE">NOT LIKE</option></select></td>
<td class=\'row1\'  width=\'60%\'  align=\'left\'><input type=\'text\' name=\'report_values2\' value=\'' . $_SESSION['report_values2'] . '\' size=\'25\' class=\'textinput\'>(wildcard: "%" when "like" or "not like",date format:m/d/y)</td>
</tr>
<tr>
<td class=\'row1\'  width=\'10%\'  align=\'left\'><b>Order By</b></td>
<td class=\'row2\'  width=\'15%\'  align=\'left\'><select name="report_orderby">
<option value="">Default</option>
<option value="date">Report Date</option>
</select><select name=report_AD class="dropdown"><option value=>Default</option><option value=ASC>ASC</option><option value=DESC>DESC</option></select></td>
<td class=\'row1\'  width=\'60%\'  align=\'left\'>
<b>Show</b>: <input type=\'text\' name=\'report_pages\' value=\'' . $_SESSION['report_pages'] . '\' size=\'8\' class=\'textinput\'>records per page
</td>
</tr>
<tr>
<td class=\'row1\' colspan=\'3\' align=\'left\' class=\'\'><input type=\'submit\' name=\'search\' value=\'Search\' size=\'30\' class=\'textinput\'><input type=\'reset\' name=\'reset\' value=\'Reset\' size=\'30\' class=\'textinput\'></td>
</tr>
<script>
s=document.jump.report_orderby.options;
for(i=0;i<s.length;i++)
{
   if(s[i].value==\'' . $_SESSION['report_orderby'] . '\')
   {
     s[i].selected=\'true\';
     break;
   }
}
</script>
<script>
s=document.jump.report_AD.options;
for(i=0;i<s.length;i++)
{
   if(s[i].value==\'' . $_SESSION['report_AD'] . '\')
   {
     s[i].selected=\'true\';
     break;
   }
}
</script>
<script>
s=document.jump.report_func.options;
for(i=0;i<s.length;i++)
{
   if(s[i].value==\'' . $_SESSION['report_func'] . '\')
   {
     s[i].selected=\'true\';
     break;
   }
}
s=document.jump.report_func2.options;
for(i=0;i<s.length;i++)
{
   if(s[i].value==\'' . $_SESSION['report_func2'] . '\')
   {
     s[i].selected=\'true\';
     break;
   }
}
</script>
<script>
s=document.jump.report_field.options;
for(i=0;i<s.length;i++)
{
   if(s[i].value==\'' . $_SESSION['report_field'] . '\')
   {
     s[i].selected=\'true\';
     break;
   }
}
s=document.jump.report_field2.options;
for(i=0;i<s.length;i++)
{
   if(s[i].value==\'' . $_SESSION['report_field2'] . '\')
   {
     s[i].selected=\'true\';
     break;
   }
}
</script></table>
    <td></tr>
    </table>
    </form>';
  }

  if (!defined ('IN_ADMIN'))
  {
    exit ('hack attempted!');
  }

  $act = $input[act];
  switch ($act)
  {
    case 'dels':
    {
      foreach ($input[idList] as $key => $value)
      {
        $id = split ('_', $key);
        $db->setQuery ('' . 'delete from reports where report_id=\'' . $id['0'] . '\'');
        $db->query ();
        if ($input[delimg] == 1)
        {
          $db->setQuery ('' . 'update files set deleted=1 where upload_id=\'' . $id['1'] . '\'');
          $db->query ();
          $db->setQuery ('' . 'delete from reports where upload_id=\'' . $id['1'] . '\'');
          $db->query ();
          continue;
        }
      }

      redirect ('admin=report', 'Delete Successfully!');
      break;
    }

    default:
    {
      showlistform ();
    }
  }

?>
