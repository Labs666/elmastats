<?php include("top.php"); ?>
<?php
  echo("<b>All totaltimes</b><br/>");

// Simple determination of which method to sort by based on URL GET variable.
  $sort = 0;
  switch($_GET['sort_by']){
    case "avg": $sort = 1; break;
    case "diff": $sort = 2; break;
  }

  $utimes = array();
  $unicks = array();
  $c = 0;
  foreach ($users as $cItem) {
    $utimes[] = stateTimes($cItem["nick"], $cItem["elmaname"]);
    $unicks[] = $cItem["nick"];
    $c++;
  }

  $tts = array();

  // Iterate for each user
  for ($y = 0;$y < $c;$y++) {
    // Preliminary variable declarations
    $ctt = 0; $avgtt = 0; $finished = 0; $cItem["nick"] = $unicks[$y];

    // Iterate for each internal level
    for ($x = 0;$x < 54;$x++) {
      $AvgTime = 0;

      //TT calculations
      $cTime = $utimes[$y][$x+1][1];

      // Elma state.dat will input a default time of 10 minutes for uncleared levels, and also overwrite an internal time above 10 minutes with a time of 10 minutes of TT calculation.
      // These two conditional emulate this behaviour.
      if (!$cTime || $cTime > 60000) $cTime = 60000;
      $ctt += (int)$cTime;

      //Average calculations (and # of times handling)
      for ($z = 0;$z < 10;$z++) {
        $xTime = $utimes[$y][$x+1][$z+1];
        if (!$xTime) {
          $AvgTime += 60000;
        } else {
          $AvgTime += min((int)$xTime, 60000);
          $finished++;
        }
      }
      $avgtt += round($AvgTime / 10);
    }

    // Store in array
    if ($ctt < 60000*54) {
      $cItem["time"] = $ctt;
      $cItem["avgtime"] = $avgtt;
      // Prevent users from breaking the Avg/Regular TT difference stat by having less than 540 times logged.
      if($finished == 540) { 
        $cItem["timediff"] = $avgtt - $ctt; 
      }
      else $cItem["timediff"] = $ctt;
      $cItem["fnsh"] = $finished;
      $tts[] = $cItem;
    }
  }

  // Sort List by either TT, Avg TT or Difference depending on $sort variable defined by URL. Sorts by TT by default.
  if($sort == 0)  usort($tts, "cmp");
  else if($sort == 1)  usort($tts, "cmpavg");
  else if($sort == 2)  usort($tts, "cmpdiff");
  else usort($tts, "cmp");

  echo("<br/>");
  //echo("<table><tr><td width=\"20px\"></td><td width=\"180px\"><b>Kuski</b></td><td width=\"81px\"><center><b>TT</b></center></td><td width=\"172px\"><center><b>Updated</b></center></td></tr></table>");
  echo("<table class=\"times\">");
  echo("<tr><th class=\"times\" width=\"20px\"></th><th class=\"times\" width=\"180px\">Kuski</th>");
  echo("<th class=\"times\" width=\"81px\"><center><a href=\"tts.php\">TT</a></center></th>");
  echo("<th class=\"times\" width=\"81px\"><center><a href=\"tts.php?sort_by=avg\">Average TT</a></center></th>");
  echo("<th class=\"times\" width=\"81px\"><center><a href=\"tts.php?sort_by=diff\">Diff. Avg</a></center></th>");
  echo("<th class=\"times\" width=\"81px\"><center>Times</center></th>");
  echo("<th class=\"times\" width=\"121px\"><center>Updated</center></th></tr>\n");

  for ($y = 0;$y < count($tts);$y++) {
    $str = "";
    if ($y > 0) $str = " title=\"+" . formatElmaTime($tts[$y]["time"]-$tts[0]["time"]) . " to " . $tts[0]["nick"] . "'s TT\"";
    echo("<tr><td class=\"times\" width=\"20px\">" . ($y+1) . ". </td>");
    echo("<td class=\"times\" width=\"180px\">" . man($tts[$y]["nick"]) . "</td>");
    echo("<td class=\"times\" width=\"81px\"" . $str . "><center>" . coloredtttime($tts[$y]["time"]) . "</center></td>");
    echo("<td class=\"times\" width=\"81px\"" . $str . "><center>" . coloredtttime($tts[$y]["avgtime"]) . "</center></td>");
    echo("<td class=\"times\" width=\"81px\"" . $str . "><center>" . ($tts[$y]["timediff"] > 0 ? "+" . formatElmaTime($tts[$y]["timediff"], true) : "-") . "</center></td>");
    echo("<td class=\"times\" width=\"81px\"" . $str . "><center>" . $tts[$y]["fnsh"] . "/540</center></td>");
    echo("<td class=\"times\"
            width=\"121px\"
            title=\"".date("d/m/y - H:i:s", ttime(filemtime("statefilesz/" . $tts[$y]["nick"] . ".dat"))) ."\">&nbsp;" .
            timeSince(filemtime("statefilesz/" . $tts[$y]["nick"] . ".dat")) . " ago</td></tr>\n");
  }
  echo("</table>");
?>
<?php include("tpo.php"); ?>
