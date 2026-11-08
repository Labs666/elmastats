<?php include("top.php"); ?>
<?php
  $dudes = array();
  $c = 0;
  while (true) {
    if ($_GET["u" . ($c+1)] != "") {
      $dudes[$c] = $_GET["u" . ($c+1)];
    } else {
      break;
    }
    $c++;
  }

  echo("<b>Stats comparing</b><br/><br/>");

  $sortedUsers = $users;
    uasort($sortedUsers, function ($a, $b) {
    return strcasecmp($a["nick"] ?? "", $b["nick"] ?? "");
  });
  //"Pickboxes" XD
  echo("<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td><form action=\"compare.php\" method=\"get\">");
  $str = "";
  for ($x = 0;$x < 6;$x++) {
    echo("<select name=\"u" . ($x+1) . "\">");
    if ($cItem["nick"] != $dudes[$x]) {
      echo("<option value=\"\" selected=\"selected\"></option>");
    } else {
      echo("<option value=\"\"></option>");
    }
    echo("<optgroup label=\"Players\">");
    foreach ($sortedUsers as $cItem) {
      if ($cItem["nick"] == '!.gitignore' || $cItem["nick"] == '*') { continue; }
      if ($cItem["nick"] != "") {
        if ($cItem["nick"] == $dudes[$x]) {
          echo("<option value=\"" . $cItem["nick"] . "\" selected=\"selected\">" . $cItem["nick"] . "</option>");
        } else {
          echo("<option value=\"" . $cItem["nick"] . "\">" . $cItem["nick"] . "</option>");
        }
      }
    }
    echo("</optgroup>");
    echo("<optgroup label=\"Teams\">");
    echo("<option value=\"teamslolol\">Teams here</option>");
    echo("</optgroup>");
    echo("<optgroup label=\"Nations\">");
    echo("<option value=\"nationdfsdfsdfsf\">Nations here</option>");
    echo("</optgroup>");
    $str .= "u" . ($x+1) . "=" . $dudes[$x] . "&amp;";
    echo("</select>");
  }
  echo("<br/><br/><input type=\"submit\" value=\"Update\"/>");
  echo("</form></td></tr></table><br/>");


  $utimes = array();
  for ($x = 0; $x < $c; $x++) {
    $utimes[$x] = stateTimes($dudes[$x]);
  }

  $compareTimes = array();
  for ($x = 0; $x < $c; $x++) {
    for ($level = 1; $level <= 54; $level++) {
      $time = (int)($utimes[$x][$level][1] ?? 0);
      $compareTimes[$x][$level] = ($time > 0) ? min($time, 60000) : 0;
    }
  }
  $ok = true;
  for ($x = 0;$x < $c;$x++) {
    if ($utimes[$x] == NULL) {
      $ok = false;
      echo("<span style=\"color: #FF0000\">" . $dudes[$x] . " hasn't uploaded stats!</span><br/>");
    }
  }
  if ($ok) {
    echo("<table class=\"times\" cellpadding=\"2\">");
    echo("<tr><th class=\"times\" width=\"180px\">Level</th>");

    for ($y = 0;$y < $c;$y++) {
      echo("<th class=\"times\" width=\"81px\"><center><a href=\"player.php?player=" . $dudes[$y] . "\">" . $dudes[$y] . "</a></center></th>");
    }

    if ($c == 2) echo("<th class=\"times\" width=\"81px\"><center>Diff.</center></th>");
    echo("</tr>");
    $teamtt = 0;
    $victories = array();
    for ($x = 0;$x < 54;$x++) {
      echo("<tr><td class=\"times\">" . internal($x+1, true, true, true) . "</td>");
      $trman = 0;
      $trtajm = $utimes[0][$x+1][1];
      if (!$trtajm) $trtajm = 60000;
      for ($y = 1;$y < $c;$y++) {
        if ($utimes[$y][$x+1][1] > 0) {
          if ($utimes[$y][$x+1][1] < $trtajm) {
            $trtajm = $utimes[$y][$x+1][1];
            $trman = $y;
          } else {
            if ($utimes[$y][$x+1][1] == $trtajm) {
              $trman = -1;
            }
          }
        }
      }
      $teamtt += $trtajm;
      $victories[$trman] += 1;
      for ($y = 0;$y < $c;$y++) {
        $bulle = ""; $bulle2 = "";
        if ($trman == $y) { $bulle = "<span style=\"color: #00AA00\">"; $bulle2 = "</span>"; }
        if ($trtajm > $utimes[$y][$x+1][1]) { $wry = "-"; } else { $wry = "+"; }
        $diff2 = abs((int)$utimes[$y][$x+1][1]-(int)$trtajm);
        $str = $wry . formatElmaTime($diff2) . " to " . $dudes[$trman] . "'" . (endsWith($dudes[$trman], "s", false) || endsWith($dudes[$trman], "z", false) ? "" : "s") . " time";
        if ($y == $trman || $trman == -1) $str = "";
        if ($utimes[$y][$x+1][1] == 0) $str = "";
        //echo("<td class=\"times\" title=\"" . $str . "\"><center>" . $bulle . formatElmaTime($utimes[$y][$x+1][1], true) . $bulle2 . "</center></td>");
        $playerTime = (int)($utimes[$y][$x + 1][1] ?? 0);
        if ($playerTime <= 0) {
        $str = "";
        }
        $timeHtml = ($playerTime > 0)
          ? sttime($dudes[$y], $x + 1, $playerTime, true, $trman == $y)
          : "";
        echo("<td class=\"times\" title=\"" . $str . "\"><center>" . $bulle . $timeHtml . $bulle2 . "</center></td>");
      }
    if ($c == 2) {
      $rawTime1 = (int)($utimes[0][$x + 1][1] ?? 0);
      $rawTime2 = (int)($utimes[1][$x + 1][1] ?? 0);
      if ($rawTime1 <= 0 || $rawTime2 <= 0) {
        $diffText = "-";
      } else {
        $time1 = (int)$compareTimes[0][$x + 1];
        $time2 = (int)$compareTimes[1][$x + 1];
        $wry = ($time2 > $time1) ? "-" : "+";
        $diff2 = abs($time1 - $time2);
        $diffText = ($diff2 === 0) ? "No diff." : $wry . formatElmaTime($diff2);
      }
      echo("<td class=\"times\"><center>" . $diffText . "</center></td>");
    }
    echo("</tr>");
    }
    echo("<tr>");
    for ($column = 0; $column < 1 + $c + ($c == 2 ? 1 : 0); $column++) {
    echo("<th class=\"times\"></th>");
    }
    echo("</tr>");
    echo("<tr><td class=\"times\">Total Time</td>");
    $ttrman = 0;
    $tts = array();
    for ($y = 0;$y < $c;$y++) {
      $tt = 0;
    for ($x = 0; $x < 54; $x++) {
      $time = (int)($compareTimes[$y][$x + 1] ?? 0);
      $tt += ($time > 0) ? $time : 60000;
    }
      $tts[$y] = $tt;
      if ($tts[$y] < $tts[$ttrman]) $ttrman = $y;
    }
    for ($y = 0;$y < $c;$y++) {
      $bulle = ""; $bulle2 = "";
      if ($ttrman == $y) { $bulle = "<span style=\"color: #00AA00\">"; $bulle2 = "</span>"; }
      echo("<td class=\"times\"><center>" . $bulle . coloredtttime($tts[$y], true) . $bulle2 . "</center></td>");
    }
    if ($c == 2) {
      if ($tts[1] > $tts[0]) { $wry = "-"; } else { $wry = "+"; }
      $diff2 = abs($tts[1]-$tts[0]);
      echo("<td class=\"times\"><center>" . $wry . formatElmaTime($diff2) . "</center></td>");
    }

    /*$wrtt = 0;
    for ($x = 0;$x < $levNum;$x++) {
      $wrtt += $wrarray[$x+1];
    }
    echo("<td class=\"times\" rowspan=\"2\">" . formatElmaTime($wrtt, true) . "</td>");
    if ($wrtt > $teamtt) { $wrx = "-"; } else { $wrx = "+"; }
    $tt = abs($teamtt-$wrtt);
    echo("<td class=\"times\" rowspan=\"2\">" . $wrx . formatElmaTime($tt, true) . "</td></tr>");*/

    echo("</tr>");
    if ($c > 0) {
      echo("<tr><td class=\"times\" title=\"The TT of all the best times for each lev\">Combined TT</td>");
      echo("<td class=\"times\" colspan=\"" . ($c) . "\"><center>" . coloredtttime($teamtt, true) . "</center></td>");
      if ($c == 2) echo("<td class=\"times\"></td>");
      echo("</tr>");
    }

    echo("</table><br/><br/>");
    echo("<b>Victories</b><br/><br/>");
    echo("<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">");
    $victoryRows = array();

    for ($x = 0; $x < $c; $x++) {
      $wins = (int)($victories[$x] ?? 0);
      if ($wins > 0) {
        $victoryRows[] = array(
          "nick" => $dudes[$x],
          "wins" => $wins
        );
      }
    }

    usort($victoryRows, function ($a, $b) {
      if ($a["wins"] !== $b["wins"]) {
        return $b["wins"] <=> $a["wins"];
      }
      return strcasecmp($a["nick"], $b["nick"]);
    });

    foreach ($victoryRows as $row) {
      echo("<tr><td>" . man($row["nick"]) . "&nbsp;</td>");
      echo("<td>" . $row["wins"] . " victories</td></tr>");
    }
    echo("<tr><td colspan=\"2\">&nbsp;</td></tr>");
    echo("<tr><td><b>Best TT</b>&nbsp;</td><td>" . man($dudes[$ttrman]) . "</td></tr>");
    echo("</table>");
  }
?>
<?php include("tpo.php"); ?>
