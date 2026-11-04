<?php
  /*Belmaquote 1.0 USerbar version
  Parameters:
    u - usernameik
    s - string to be displayed, 0x0D for newline, :tt:  =  TT
    b - border width in pixels
    t - background texture without .png (ground, sky... rnd)
  */

  ob_start();
  include("func.php");
  addUbCounter();
    
  $str = "";
    
  $utimes = stateTimes($_GET["u"]);
  if ($utimes != NULL) { 
    //kalkulate tt
    $tt = 0;
    for ($x = 0;$x < 54;$x++) {
      $cTime = (int)($utimes[$x + 1][1] ?? 0);
      if ($cTime <= 0) $cTime = 60000;
      $tt += $cTime;
    }
    $str = formatElmaTime($tt);
  } else {
    $str = "N/A";
  }
  $s = ":tt:";
  if ($_GET["s"] != "") $s = $_GET["s"];
  $str = str_replace(":tt:", $str, $s);
  
  //Put all ASCII chars in a buffer
  $chars = array();
  $charW = array();
  $charH = array();
  for ($x = 32;$x <= 128;$x++) {
    if (file_exists("bfont/" . $x . ".png")) {
      $chars[$x] = imagecreatefrompng("bfont/" . $x . ".png");
      list($cW, $cH, $cT, $cA) = getimagesize("bfont/" . $x . ".png");
      $charW[$x] = $cW;
      $charH[$x] = $cH;
    }
  }

  //Read vshifts
  $vShift = array();
  $lines = file("bfont/vshifts.txt");
  $i = 0;
  foreach ($lines as $line_num => $line) {
    $vShift[$i] = substr($line, 0, strpos($line, " "));
    $i++;
  }

  $i = 0;
  $j = 0;
  $maxi = 0;
  for ($x = 0;$x < strlen($str);$x++) {
    $num = ord(substr($str, $x, 1));
    if ($num == 13) {
      $j += 12;
      $i = 0;
    } else {
      $i += $charW[$num]+1;
      if ($num == 32) $i += 2;
      if ($maxi < $i) $maxi = $i;
    }
  }
  $border = 5;
  if (isset($_GET["b"])) $border = $_GET["b"];
  if ($border < 0) $border = 0; if ($border > 500) $border = 500;
  $borderx = $border-2;
  $bordery = $border;
  $imgW = $borderx*2+$maxi;
  $imgH = $bordery*2+$j+12-2;
  if (isset($_GET["w"]) && $_GET["w"] > 0 && $_GET["w"] < 2048) $imgW = $_GET["w"];
  if (isset($_GET["h"]) && $_GET["h"] > 0 && $_GET["h"] < 2048) $imgH = $_GET["h"];

  $img = imagecreatetruecolor($imgW, $imgH);
  Imagefilledrectangle($img, 0, 0, $imgW, $imgH, ImageColorAllocate($img, 0, 0, 0));
  $texture = "ground";
  if (isset($_GET["t"]) && (file_exists("img/" . $_GET["t"] . ".png") || $_GET["t"] == "rnd")) $texture = $_GET["t"];
  if ($texture == "rnd") {
    $tx = array();
    $tx[0] = "ground"; $tx[1] = "sky"; $tx[2] = "brick";
    $tx[3] = "stone1"; $tx[4] = "stone2"; $tx[5] = "stone3";
    $texture = $tx[rand(0, 5)];
  }
  $tex = imagecreatefrompng("img/" . $texture . ".png");
  list($texW, $texH, $texT, $texA) = getimagesize("img/" . $texture . ".png");
  for ($y = 0;$y < ceil($imgH/$texH);$y++) {
    for ($x = 0;$x < ceil($imgW/$texW);$x++) {
      imagecopy($img, $tex, $x*$texW, $y*$texH, 0, 0, $texW, $texH);
    }
  }

  $i = 0;
  $j = 0;
  for ($x = 0;$x < strlen($str);$x++) {
    $num = ord(substr($str, $x, 1));
    if ($num == 13) {
      $j += 12;
      $i = 0;
    } else {
      imagecopy($img, $chars[$num], $borderx+$i, $bordery+$vShift[$num]+$j, 0, 0, $charW[$num], $charH[$num]);
      $i += $charW[$num]+1;
      if ($num == 32) $i += 2;
    }
  }

  ob_clean();
  header("Content-Type: image/png");
  imagepng($img);
  imagedestroy($tex);
  imagedestroy($img);
?>
