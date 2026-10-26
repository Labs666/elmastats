<?php include("top.php"); ?>
<?php
  $targets = loadTargets();

  $topnum = $_GET["top"];
  if ($topnum == 0) $topnum = 10;
  echo("<b>Top" . $topnum . "s for all internals</b><br/><br/>");
  $utimes = array();
  $unicks = array();
  $c = 0;
  foreach ($users as $cItem) {
    $utimes[] = stateTimes($cItem["nick"], $cItem["elmaname"]);
    $unicks[] = $cItem["nick"];
    $c++;
  }

  $victories = array();
  $combinedTT = 0;

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
    $combinedTT += isset($top[0]["time"])
    ? (int)$top[0]["time"]
    : 60000;
    if (!empty($top[0]["nick"])) {
    $winner = $top[0]["nick"];
    $victories[$winner] = ($victories[$winner] ?? 0) + 1;
    }
    
    echo("<table class=\"times\">\n");
    echo("<tr><th class=\"times\" width=\"20px\">" . ($x+1) . ".</th>");
    echo("<th class=\"times\" width=\"180px\">" . internal($x+1, false, true, false) . "</th>");
    echo("<th class=\"times\" width=\"81px\"></th></tr>\n");
    for ($y = 0;$y < $topnum;$y++) {
      if ($top[$y]["nick"] != "") {
        echo("<tr>");
        echo("<td class=\"times\" width=\"20px\">" . ($y+1) . ".</td>");
        echo("<td class=\"times\" width=\"180px\">" . man($top[$y]["nick"]) . "</td>");
        $str = "";
        if ($y > 0) $str = " title=\"+" . formatElmaTime((int)$top[$y]["time"]-(int)$top[0]["time"]) . " to " . $top[0]["nick"] . "'s time\"";
        echo("<td class=\"times\" width=\"81px\"" . $str . "><center>" . sttime($top[$y]["nick"], $x+1, $top[$y]["time"], true, false, target($top[$y]["time"], $x+1)) . "</center></td>");
        echo("</tr>\n");
      }
    }
    echo("</table>");
    
    echo("<br/>");
    if ($x == 26) echo("</div><div class=\"lefty\">");
  }
  echo("</div>");
  echo("<div class=\"lefty\">");
  echo("<br/><b>Top10 records</b><br/><br/>");
  uksort($victories, function ($a, $b) use ($victories) {
    $winsOrder = $victories[$b] <=> $victories[$a];
    if ($winsOrder !== 0) {
      return $winsOrder;
    }

     return strcasecmp($a, $b);
  });
  echo("<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">");
  foreach ($victories as $nick => $wins) {
    echo("<tr><td>" . man($nick) . "&nbsp;</td>");
    echo("<td>" . $wins . " records</td></tr>");
  }
  echo("<tr><td colspan=\"2\">&nbsp;</td></tr>");
  echo("<tr><td><b>Combined TT:</b>&nbsp;</td><td>" .
  coloredtttime($combinedTT, true) . "</td></tr>");
    echo("</table>");
    echo("</div>");
?>
<?php include("tpo.php"); ?>
