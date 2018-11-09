<?
header("Expires: 0");
header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
header("Content-type: image/jpeg");

session_start();
make_captcha();

function make_captcha($lx=80,$ly=28,$nb_noise=0,$noise=0) {
  $font_file=getcwd()."/comic.ttf";
  $private_key = $_SESSION['authkey']= mt_rand(100, 999);
  $_SESSION['authkey_expire']= time()+(60*12); // set expired time of session
  
  // include, then open connection, then select db
  include_once("config.php");
  $db = mysql_connect($sql_host, $sql_user, $sql_pass);
  mysql_select_db($sql_database);
  // delete all captcha that is same ip on database, then insert into new captcha backup
  // @mysql_query("delete from captcha where ip='$_SERVER[REMOTE_ADDR]'");
  @mysql_query("insert into captcha values (null , '".time()."', '$private_key', '$_SERVER[REMOTE_ADDR]')");
  // close db
  mysql_close($db);

  $long=strlen($private_key);
  $image = imagecreatetruecolor($lx,$ly);
  $back=ImageColorAllocate($image,intval(rand(224,255)),intval(rand(224,255)),intval(rand(224,255)));
  ImageFilledRectangle($image,0,0,$lx,$ly,$back);
  if ($noise) { // rand characters in background with random position, angle, color
    for ($i=0;$i<$nb_noise;$i++) {
      $size=intval(rand(8,14));
      $angle=intval(rand(0,360));
      $x=intval(rand(10,$lx-10));
      $y=intval(rand(28,$ly-5));
      $color=imagecolorallocate($image,intval(rand(160,224)),intval(rand(160,224)),intval(rand(160,224)));
      $text=chr(intval(rand(45,250)));
      ImageTTFText ($image,$size,$angle,$x,$y,$color,$font_file,$text);
    }
  }
  else { // random grid color
    for ($i=0;$i<$lx;$i+=10) {
      $color=imagecolorallocate($image,intval(rand(160,224)),intval(rand(160,224)),intval(rand(160,224)));
      imageline($image,$i,0,$i,$ly,$color);
    }
    for ($i=0;$i<$ly;$i+=10) {
      $color=imagecolorallocate($image,intval(rand(160,224)),intval(rand(160,224)),intval(rand(160,224)));
      imageline($image,0,$i,$lx,$i,$color);
    }
  }
  // private text to read
  for ($i=0,$x=15; $i<$long;$i++) {
    $r=intval(rand(0,128));
    $g=intval(rand(0,128));
    $b=intval(rand(0,128));
    $color = ImageColorAllocate($image, $r,$g,$b);
    $shadow= ImageColorAllocate($image, 255, 255, 255);
    $size=intval(rand(12,17));
    $angle=intval(rand(-30,30));
    $text=strtoupper(substr($private_key,$i,1));

    if(!function_exists('ImageTTFText'))
    {
    imagestring($image,7,$x+2,5,$text,$shadow);
    imagestring($image,7,$x,8,$text,$color);
    }
    else
    {
    @ImageTTFText($image,$size,$angle,$x+2,22,$shadow,$font_file,$text);
    @ImageTTFText($image,$size,$angle,$x,21,$color,$font_file,$text);
    }
    
    $x+=$size+6;
  }

  imagejpeg($image, '', 25);

  ImageDestroy($image);
}
exit();
?>
