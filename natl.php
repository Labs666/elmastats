<?php include("top.php"); ?>
<?php
  $unicks = array();
  $c = 0;
  $nousers = true;
  $eror = false;
  $targets = loadTargets();
  $cnames = countries();
  if ($_GET["country"] == "") {
    echo("<b>Choose nationality</b><br/>");
    $nc = array();
    foreach ($users as $cItem) {
      if ($cItem["country"] != "") {
        $duplicate = false;
        foreach ($nc as $nk => $n) if ($cItem["country"] == $nk) $duplicate = true;
        if (!$duplicate) {
          $nc[$cItem["country"]] = 1;
        } else {
          $nc[$cItem["country"]] += 1;
        }

        /*$utimes = stateTimes($cItem["nick"], $cItem["elmaname"]);
        $tt = 0;
        for ($x = 0;$x < 54;$x++) {
          if ($utimes[$x+1][1] == 0) {
            $tt += 60000;
          } else {
            $tt += $utimes[$x+1][1];
          }
        }
        $nc[$cItem["country"]]["tt"] += $tt;*/
      }
    }
    /*usort($ntt, "cmp");

    echo("<table class=\"times\">");
    $x = 0;
    foreach ($ntt as $n => $tt) {
      $x++;
      echo("<tr class=\"times\"><td class=\"times\" width=\"20px\">" . $x . ".</td>");
      echo("<td class=\"times\" width=\"200px\">");
      echo(flag($n) . " <a href=\"natl.php?country=" . $n . "\">" . $cnames[strtoupper($n)] . "</a></td>");
      echo("<td class=\"times\" width=\"81px\">" . formatElmaTime($tt) . "</td>");
      $wry = "s"; if ($ncc[$n] == 1) $wry = "";
      echo("<td class=\"times\">" . $ncc[$n] . " kuski" . $wry . "</td></tr>");
    }
    echo("</table>");*/

    uksort($nc, function ($a, $b) use ($nc, $cnames) {
      // More players of same nation goes first.
      $countOrder = $nc[$b] <=> $nc[$a];
      if ($countOrder !== 0) {
      return $countOrder;
    }
      // If the players in nation are the same, make it alphabet.
      $nameA = $cnames[strtoupper($a)] ?? $a;
      $nameB = $cnames[strtoupper($b)] ?? $b;
      $nameOrder = strcasecmp($nameA, $nameB);
      return $nameOrder !== 0 ? $nameOrder : strcasecmp($a, $b);
    });
    echo("<table>");
    foreach ($nc as $nk => $n) {
      if($nk == '!.gitignore') { 
        continue; }
      else {
        echo("<tr><td width=\"200px\">");
        echo(flag($nk) . " <a href=\"natl.php?country=" . $nk . "\">" . $cnames[strtoupper($nk)] . "</a><br/>");
        echo("</td><td>" . $n . " kuski" . ($n == 1 ? "" : "s") . "</td></tr>");
      }
    }
    echo("</table>");
    $eror = true;
  }
  $country = $cnames[strtoupper($_GET["country"])];
  foreach ($users as $cItem) {
    if ($cItem["country"] == $_GET["country"]) {
      if ($nousers) {
        $nousers = false;
        if ($_GET["country"] != "") echo(flag($cItem["country"]) . " <b>" . $country . "</b><br/><br/>");
      }
      $unicks[$c] = $cItem["nick"];
      $c++;
    }
  }

  if ($nousers) {
    if ($country == "") {
      echo("<span style=\"color: #FF0000\">Non-existing country!</span><br/>");
    } else {
      echo("<span style=\"color: #FF0000\">No kuskis from " . $country . "!</span><br/>");
    }
    $eror = true;
  }
  if (!$eror) {
    $utimes = array();
    for ($x = 0;$x < $c;$x++) {
      $utimes[$x] = stateTimes($unicks[$x], $users[$unicks[$x]]["elmaname"]);
    }

    echo("<b>National totaltime table for " . $country . "</b><br/><br/>");
    echo("<table class=\"times\">");
    echo("<tr><th class=\"times\" width=\"20px\"></th><th class=\"times\" width=\"180px\">Kuski</th>");
    echo("<th class=\"times\" width=\"81px\"><center>TT</center></th>");
    echo("<th class=\"times\" width=\"265px\"><center>Updated</center></th></tr>");
    $tts = array();
    $cItem = array();
    for ($y = 0;$y < $c;$y++) {
      $ctt = 0;
      $cItem["nick"] = $unicks[$y];
      for ($x = 0;$x < 54;$x++) {
        $cTime = $utimes[$y][$x+1][1];
        if (!$cTime) $cTime = 60000;
        $ctt += (int)$cTime;
      }
      if ($ctt < 60000*54) {
        $cItem["time"] = $ctt;
        $tts[] = $cItem;
      }
    }
    usort($tts, "cmp");

    for ($y = 0;$y < count($tts);$y++) {
      $str = "";
      if ($y > 0) $str = " title=\"+" . formatElmaTime($tts[$y]["time"]-$tts[0]["time"]) . " to " . $tts[0]["nick"] . "'s TT\"";
      echo("<tr><td class=\"times\" width=\"20px\">" . ($y+1) . ". </td>");
      echo("<td class=\"times\" width=\"180px\">" . man($tts[$y]["nick"]) . "</td>");
      echo("<td class=\"times\" width=\"81px\"" . $str . "><center>" . coloredtttime($tts[$y]["time"]) . "</center></td>");
      echo("<td class=\"times\" width=\"265px\">&nbsp;" . date("d/m/y - H:i:s", ttime(filemtime("statefilesz/" . $tts[$y]["nick"] . ".dat"))) .
           " (" . timeSince(filemtime("statefilesz/" . $tts[$y]["nick"] . ".dat")) . " ago)</td></tr>");
    }
    echo("</table><br/><br/>");

    $topnum = $_GET["top"];
    if ($topnum == 0) $topnum = 10;
    $victories = array();
    $besttt = 0;
    echo("<b>Top " . $topnum . "s for all internals</b><br/><br/>");
    echo("<div class=\"lefty\">");
    for ($x = 0;$x < 54;$x++) {
      $top = array();
      for ($y = 0;$y < $c;$y++) {
        $cItem["nick"] = $unicks[$y];
        $cItem["time"] = $utimes[$y][$x+1][1];
        if (!$cItem["time"]) $cItem["time"] = 60000;
        if ($cItem["time"] < 60000) $top[] = $cItem;
      }
      usort($top, "cmp");
      $besttt += isset($top[0]["time"]) ? (int)$top[0]["time"] : 60000;
      echo("<table class=\"times\">");
      echo("<tr><th class=\"times\" width=\"20px\">" . ($x+1) . ".</th>");
      echo("<th class=\"times\" width=\"180px\">" . internal($x+1) . "</th>");
      echo("<th class=\"times\" width=\"81px\"></th></tr>");
      for ($y = 0;$y < $topnum;$y++) {
        if ($top[$y]["nick"] != "") {
          $t = target($top[$y]["time"], $x+1);
          echo("<tr>");
          echo("<td class=\"times\" width=\"20px\">" . ($y+1) . ".</td>");
          echo("<td class=\"times\" width=\"180px\">" . man($top[$y]["nick"]) . "</td>");
          $str = "";
          if ($y > 0) $str = " title=\"+" . formatElmaTime((int)$top[$y]["time"]-(int)$top[0]["time"]) . " to " . $top[0]["nick"] . "'s time\"";
          echo("<td class=\"times\" width=\"81px\"" . $str . "><center>" . sttime($top[$y]["nick"], $x+1, $top[$y]["time"], false, true, $t) . "</center></td>");
          echo("</tr>");
        }
      }
      $victories[$top[0]["nick"]] += 1;
      echo("</table>");
      echo("<br/>");
      if ($x == 26) echo("</div><div class=\"lefty\">");
    }
    echo("</div>");
    echo("<div class=\"lefty\">");
    echo("<br/><b>National records</b><br/><br/>");
    uksort($victories, function ($a, $b) use ($victories) {
      $recordOrder = $victories[$b] <=> $victories[$a];
      if ($recordOrder !== 0) {
        return $recordOrder;
      }

      return strcasecmp($a, $b);
    });
    echo("<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">");
    foreach ($victories as $man => $v) {
    echo("<tr><td>" . man($man) . "&nbsp;</td>");
    echo("<td>" . $v . " records</td></tr>");
    }
    echo("<tr><td colspan=\"2\">&nbsp;</td></tr>");
    echo("<tr><td><b>National TT:</b>&nbsp;</td>");
    echo("<td>" . coloredtttime($besttt, true) . "</td></tr>");
    echo("</table>");
    echo("</div>");
  }
?>
<?php include("tpo.php"); ?>
