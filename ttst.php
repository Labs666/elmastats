<?php
  //crap script used to make TT images
  //parameters:
  //  u     user
  //  bg    bg color (b: blue, g: green...)
  
  ob_start();
  include("func.php");
  addUbCounter();

  $utimes = stateTimes($_GET["u"]);
  if ($utimes != NULL) { 
    //kalkulate tt
    $tt = 0;
    for ($x = 0; $x < 54; $x++) {
      $cTime = (int)($utimes[$x + 1][1] ?? 0);
      $tt += ($cTime > 0) ? min($cTime, 60000) : 60000;
    }
    $str = formatElmaTime($tt);
  } else {
    $str = "N/A";
  }
  //Load users
  $users = array(array());
  $files = array();
  $handle = opendir("usersz/"); 
  while (false !== ($file = readdir($handle))) { 
    $files[] = "usersz/" . $file;
  }
  closedir($handle);
  foreach ($files as $file) {
    $lines = file($file);
    $i = 0;
    foreach ($lines as $line_num => $line) {
      $i++;
      switch ($i) {
        case 1: $nick = substr($line, 0, strlen($line)-1); $users[$nick]["nick"] = $nick; break;
        case 2: $users[$nick]["elmaname"] = substr($line, 0, strlen($line)-1); break;
        case 3: $users[$nick]["country"] = substr($line, 0, strlen($line)-1); break;
        case 4: $users[$nick]["team"] = substr($line, 0, strlen($line)-1); break;
        case 5: $users[$nick]["pwd"] = substr($line, 0, strlen($line)-1); break;
      }
    }
  }
  $country = $users[$_GET["u"]]["country"];
  $bgc = "b";
  if (file_exists("img/ub" . $_GET["bg"] . ".png")) $bgc = $_GET["bg"];
  $img = imagecreatefrompng("img/ub" . $bgc . ".png");
  if ($str != "N/A") {
    $tex = imagecreatefrompng("flags/" . $country . ".png");
    list($texW, $texH, $texT, $texA) = getimagesize("flags/" . $country . ".png");
    imagecopy($img, $tex, 4, 4, 0, 0, $texW, $texH);
    
    $fontcolor = imagecolorallocate($img, 255, 255, 255);
    imagestring($img, 4, 24, 1, $_GET["u"], $fontcolor);
    imagestring($img, 4, 125, 1, $str, $fontcolor);
  }
  if (ob_get_length()) ob_clean();
  header("Content-Type: image/png");
  imagepng($img);
  imagedestroy($img);
?>
