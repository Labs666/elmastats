<?php

  include("func.php");
  
  if (file_exists("usersz/" . $_GET["u"])) {
    $n = 0;
    while (file_exists("historyz/" . $_GET["u"] . "_" . $n)) $n++;
    $ctimes = array(array(array()));
    for ($x = 0;$x < $n;$x++) $ctimes[$x] = htimes("historyz/" . $_GET["u"] . "_" . $x);
    $tts = array();
    $tss = array();
    $maxtt = 0;
    $mintt = 666666666666;
    switch ($_GET["m"]) {
      case "tt":
        for ($x = 0;$x < $n;$x++) {
          $tts[$x] = 0;
          for ($y = 0;$y < 54;$y++) {
            $tts[$x] += ($ctimes[$x][$y+1][1] > 0 ? $ctimes[$x][$y+1][1] : 60000);
          }
          $tss[$x] = $ctimes[$x][0][0];
          if ($maxtt < $tts[$x]) $maxtt = $tts[$x];
          if ($mintt > $tts[$x]) $mintt = $tts[$x];
        }
        break;
      case "int":
        $int = $_GET["i"];
        if ($int < 1) $int = 1;
        if ($int > 54) $int = 54;
        for ($x = 0;$x < $n;$x++) {
          $tts[$x] = ($ctimes[$x][$int][1] > 0 ? $ctimes[$x][$int][1] : 60000);
          $tss[$x] = $ctimes[$x][0][0];
          if ($maxtt < $tts[$x]) $maxtt = $tts[$x];
          if ($mintt > $tts[$x]) $mintt = $tts[$x];
        }
        break;
    }
    $ttborder = ($maxtt-$mintt)/10; //10% border
    $maxtt += $ttborder;
    $mintt -= $ttborder;
    $ttspan = $maxtt-$mintt;
    $firstYear = (int)date("Y", $tss[0]);
    $lastYear = (int)date("Y", $tss[$n - 1]);
    $axisStart = $tss[0];
    $axisEnd = time();
    $timespan = $axisEnd - $axisStart;
    
    //robbla
    $bort = 8; $borb = 48;
    $borl = 70; $borr = 32;
    $imgw = 799; $imgh = 352;
    if (isset($_GET["w"])) $imgw = $_GET["w"];
    if (isset($_GET["h"])) $imgh = $_GET["h"];
    if ($imgw < 200) $imgw = 200;
    if ($imgh < 100) $imgh = 100;
    $canw = $imgw-$borl-$borr;
    $canh = $imgh-$bort-$borb;
    //$segw = $canw/($n > 1 ? $n-1 : 1);
    $ttm = ($ttspan == 0 ? 1 : $canh/$ttspan);
    
    $charw = 5;
    
    $img = imagecreatetruecolor($imgw, $imgh);
    
    //colors
    $c_alpha = imagecolorallocate($img, 255, 0, 255);
    if (($_GET["dark"] ?? "0") != "1") {
      $c_canv = imagecolorallocate($img, 50, 0, 0);
      $c_line = imagecolorallocate($img, 255, 0, 0);
      $c_line2 = imagecolorallocate($img, 100, 0, 0);
      $c_border = imagecolorallocate($img, 255, 0, 255);
      $c_text = imagecolorallocate($img, 204, 204, 204);
      $c_ticks = imagecolorallocate($img, 100, 100, 100);
    } else {
      $c_canv = imagecolorallocate($img, 200, 200, 200);
      $c_line = imagecolorallocate($img, 50, 0, 0);
      $c_line2 = imagecolorallocate($img, 180, 180, 180);
      $c_border = imagecolorallocate($img, 255, 0, 255);
      $c_text = imagecolorallocate($img, 0, 0, 0);
      $c_ticks = imagecolorallocate($img, 100, 100, 100);
    }
    
    //background
    imagecolortransparent($img, $c_alpha);
    imagefilledrectangle($img, 0, 0, $imgw, $imgh, $c_border);
    imagefilledrectangle($img, $borl, $bort, $imgw-$borr, $imgh-$borb, $c_canv);
    imagerectangle($img, $borl, $bort, $imgw-$borr, $imgh-$borb, $c_line2);
    
    //static text
    //imagestring($img, 2, 28-(strlen(formatElmaTime(floor($maxtt/100)*100))*$charw)/2, $bort-7, formatElmaTime(floor($maxtt/100)*100), $c_text);
    //imagestring($img, 2, 28-(strlen(formatElmaTime(floor($mintt/100)*100))*$charw)/2, $bort+$canh-7, formatElmaTime(floor($mintt/100)*100), $c_text);

    //tt ticks
    $tickreso = 10*100; //1 tick per x*100 sec
    if ($_GET["m"] == "int") $tickreso = 100;
    for ($x = 0;$x < floor($maxtt/$tickreso)-floor($mintt/$tickreso);$x++) {
      $ticktt = (floor($maxtt/$tickreso)-$x)*$tickreso;
      $liney = $bort+$canh-($ticktt-$mintt)*$ttm;
      if ($liney > $bort && $liney < $bort+$canh) imageline($img, $borl, $liney, $borl-4, $liney, $c_ticks);
    }
    
    //draw club lines+texts
    $domi = floor($canh/20);
    $tickreso = 100*60;
    if ($_GET["m"] == "int") $tickreso = 100;
    $num = floor($maxtt/$tickreso)-floor($mintt/$tickreso);
    for ($x = 0;$x < $num;$x++) {
      $clubtt = (floor($maxtt/$tickreso)-$x)*$tickreso;
      $liney = $bort+$canh-($clubtt-$mintt)*$ttm;
      if ($liney > $bort && $liney < $bort+$canh) {
        imageline($img, $borl-4, $liney, $borl-6, $liney, $c_ticks);
        imageline($img, $borl, $liney, $imgw-$borr, $liney, $c_line2);
      }
      if (($liney > $bort+10 && $liney < $bort+$canh-10) && ($num <= $domi || $x%ceil($num/$domi) == 0))
        imagestring($img, 2, 28-(strlen(formatElmaTime(floor($clubtt/100)*100))*$charw)/2, $liney-7, formatElmaTime(floor($clubtt/100)*100), $c_text);
    }
      
    //bottom text and vertical lines aand ticks
    $lastYear = max((int)date("Y", $tss[$n - 1]), (int)date("Y"));
    $axisEndYear = (int)(ceil($lastYear / 2) * 2);

    for ($year = $firstYear; $year <= $axisEndYear; $year++) {
      $tickTime = mktime(0, 0, 0, 1, 1, $year);
    if ($tickTime >= $axisStart && $tickTime <= $axisEnd) {
      $linex = $borl + ($tickTime - $axisStart) * ($canw / $timespan);

      imageline($img, $linex, $bort + $canh, $linex, $bort + $canh + 4, $c_ticks);
      imageline($img, $linex, $bort, $linex, $bort + $canh, $c_line2);
    }

    $firstFullYear = (int)date("Y", $axisStart) + 1;

    if (
      $tickTime >= $axisStart
      && $tickTime <= $axisEnd
      && $year >= $firstFullYear
  //  && ($year - $firstFullYear) % 2 === 0
    ) {
        $yearText = (string)$year;
        $textWidth = imagefontwidth(2) * strlen($yearText);
        $textX = (int)round($linex - $textWidth / 2);

        imagestring($img, 2, $textX, $bort + $canh + 18, $yearText, $c_text);
      }
    }

    $currentTime = time();

    if ($currentTime > $tss[$n - 1]) {
      $lastX = $borl + round(($tss[$n - 1] - $axisStart) / $timespan * $canw);
      $currentX = $borl + round(($currentTime - $axisStart) / $timespan * $canw);
      $lastY = $bort + $canh - ($tts[$n - 1] - $mintt) * $ttm;

      imageline($img, $lastX, $lastY, $currentX, $lastY, $c_line);
    }
    
    
    //tt line
    for ($x = 0;$x < $n-1;$x++) {
      //imageline($img, $borl+round($x*$segw), $bort+$canh-($tts[$x]-$mintt)*$ttm, $borl+round(($x+1)*$segw), $bort+$canh-($tts[$x+1]-$mintt)*$ttm, $c_line);
      imageline($img, $borl + round(($tss[$x] - $axisStart) / $timespan * $canw), $bort + $canh - ($tts[$x] - $mintt) * $ttm, $borl + round(($tss[$x + 1] - $axisStart) / $timespan * $canw), $bort + $canh - ($tts[$x + 1] - $mintt) * $ttm, $c_line);
    }
    
    
    
    
    header("Content-Type: image/png");
    imagepng($img);
    imagedestroy($img);
  }
?>
