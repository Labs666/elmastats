<?php
  /*Stats.txt generator
  Parameters:
    u - username
  */

  include("func.php");
    
  $utimes = stateTimes($_GET["u"]);
  $str = "";
  if ($utimes != NULL) {
    //Load intnames
    $intnames = array();
    $lines = file("intnames.txt");
    $i = 0;
    foreach ($lines as $line_num => $line) {
      $i++;
      $line = str_replace("\r\r\n", "\r\n", $line);
      $intnames[$i] = substr($line, 0, strlen($line)-1);
    }
    
    $str .= "This text file is generated automatically each time you quit the\r\nELMA.EXE program. If you modify this file, you will loose the\r\nchanges next time you run the game. This is only an output file, the\r\nbest times are stored in the STATE.DAT binary file.\r\nRegistered version 1.3\r\n";
    $str .= "\r\nSingle player times:\r\n";
    
    $tt = 0;
    for ($x = 0;$x < 54;$x++) {
      $str .= "\r\nLevel " . ($x+1) . ", " . $intnames[$x+1] . ":\r\n";
      !$utimes[$x+1][1] || $utimes[$x+1][1] > 60000 ? $tt += 60000 : $tt += $utimes[$x+1][1];
      for ($y = 0;$y < 10;$y++) {
        if (!$utimes[$x+1][$y+1]) break;
        $str .= "    " . formatElmaTime($utimes[$x+1][$y+1]) . "    " . $_GET["u"] . "\r\n";
      }
    }
    $str .= "\r\n\r\nThe following are the single player total times for individual players.\r\nIf a player doesn't have a time in the top ten for a level, this\r\nwill add ten minutes to the total time.\r\n";
    $totalTime = formatElmaTime($tt, false, true);
    $totalTime = preg_replace('/^(\d):/', '0$1:', $totalTime);
    $separator = ($tt >= 360000) ? " " : "    ";
    $str .= $totalTime . $separator . $_GET["u"];
  }
  
  header("Content-Type: text/plain");
  header("Content-Disposition: attachment; filename=\"" . $_GET["u"] . ".txt\"");
  echo($str);
?>
