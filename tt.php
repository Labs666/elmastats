<?php
  //crap script used to make TT images
  //parameters:
  //  u     user
  //  s     string to display, :tt: = TT    :avg: = AvgTT
  //  b     border
  //  bg    background color
  //  c     text color
  //  x,y   x and y offset (resizes img also)

  ob_start();
  include("func.php");
  addUbCounter();
  function html2rgb($color) {
    if ($color[0] == "#") $color = substr($color, 1);
    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;
    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
    return array($r, $g, $b);
  }

  $utimes = stateTimes($_GET["u"]);
  if ($utimes != NULL) {
    //kalkulate tt & avg
    $tt = 0;
    $avg = 0;
  for ($x = 0; $x < 54; $x++) {
      $cTime = (int)($utimes[$x + 1][1] ?? 0);
      $tt += ($cTime > 0) ? min($cTime, 60000) : 60000;

      for ($y = 0; $y < 10; $y++) {
          $time = (int)($utimes[$x + 1][$y + 1] ?? 0);
          $avg += ($time > 0) ? min($time, 60000) : 60000;
      }
  }
    $strtt = formatElmaTime($tt);
    $stravg = formatElmaTime(round($avg/10));
  } else {
    $strtt = "N/A";
    $stravg = "N/A";
  }
  $s = ":tt:";
  if ($_GET["s"] != "") $s = $_GET["s"];
  $s = str_replace(":tt:", $strtt, $s);
  $s = str_replace(":avg:", $stravg, $s);
  $border = 1;
  if (isset($_GET["b"])) $border = $_GET["b"];
  $xp = 0;
  $yp = 0;
  if (isset($_GET["x"])) $xp = $_GET["x"];
  if (isset($_GET["y"])) $yp = $_GET["y"];
  $imgW = imagefontwidth(2)*strlen($s)+3+($border*2)+$xp;
  $imgH = 14+($border*2)+$yp;
  $img = imagecreatetruecolor($imgW, $imgH);
  $tc = imagecolorallocate($img, 255, 255, 0);
  imagecolortransparent($img, $tc);
  imagefilledrectangle($img, 0, 0, $imgW, $imgH, $tc);
  if (isset($_GET["bg"])) {
    $c = html2rgb($_GET["bg"]);
    if ($c[0] == 255 && $c[1] == 255 && $c[2] == 0) $c[0] = 254;
    $bc = imagecolorallocate($img, $c[0], $c[1], $c[2]);
    imagefilledrectangle($img, 0, 0, $imgW, $imgH, $bc);
  }
  if (isset($_GET["c"])) {
    $c = html2rgb($_GET["c"]);
    if ($c[0] == 255 && $c[1] == 255 && $c[2] == 0) $c[0] = 254;
    $fontcolor = imagecolorallocate($img, $c[0], $c[1], $c[2]);
  } else {
    $fontcolor = imagecolorallocate($img, 0, 0, 0);
  }
  imagestring($img, 2, 2+$border+$xp, $border+$yp, $s, $fontcolor);
  ob_clean();
  header("Content-Type: image/png");
  imagepng($img);
  imagedestroy($img);
?>
