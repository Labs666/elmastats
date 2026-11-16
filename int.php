<?php include("top.php"); ?>
<?php
  $targets = loadTargets();
  $int = intval($_GET["int"]);
  if ($int < 1) $int = 1;
  if ($int > 54) $int = 54;
  echo("<div class=\"lefty\">");
  if ($int > 1) echo("<a href=\"int.php?int=" . ($int-1) . "\">" . "&lt;-</a> ");
  echo("<b>" . internal($int, true, true, false) . "</b>");
  if ($int < 54) echo(" <a href=\"int.php?int=" . ($int+1) . "\">" . "-&gt;</a>");
  echo("<br/><br/>");
  $utimes = array();
  $unicks = array();
  $c = 0;
  foreach ($users as $cItem) {
    $utimes[] = stateTimes($cItem["nick"], $cItem["elmaname"]);
    $unicks[] = $cItem["nick"];
    $c++;
  }

  $top = array();
  for ($y = 0;$y < $c;$y++) {
    $cItem["nick"] = $unicks[$y];
    $cItem["time"] = (int)($utimes[$y][$int][1] ?? 0);
    if ($cItem["time"] > 0) $top[] = $cItem;
  }
  usort($top, "cmp");
  
  echo("<table class=\"times\">\n");
  echo("<tr><th class=\"times\" width=\"20px\"></th>");
  echo("<th class=\"times\" width=\"180px\">Kuski</th>");
  echo("<th class=\"times\" width=\"81px\"><center>Time</center></th></tr>\n");
  for ($y = 0;$y < count($top);$y++) {
    if ($top[$y]["nick"] != "") {
      echo("<tr>");
      echo("<td class=\"times\" width=\"20px\">" . ($y+1) . ".</td>");
      echo("<td class=\"times\" width=\"180px\">" . man($top[$y]["nick"]) . "</td>");
      $str = "";
      if ($y > 0) $str = " title=\"+" . formatElmaTime((int)$top[$y]["time"]-(int)$top[0]["time"]) . " to " . $top[0]["nick"] . "'s time\"";
      echo("<td class=\"times\" width=\"81px\"" . $str . "><center>" . sttime($top[$y]["nick"], $int, $top[$y]["time"], true, false, target($top[$y]["time"], $int)) . "</center></td>");
      echo("</tr>\n");
    }
  }
  echo("</table>");
  
  echo("</div><div class=\"lefty\">");
  echo("<b>All internals</b><br/><br/>");
  for ($x = 0;$x < 54;$x++) {
    echo(internal($x+1, true) . "<br/>");
  }
  echo("</div>");
?>
<?php include("tpo.php"); ?>
