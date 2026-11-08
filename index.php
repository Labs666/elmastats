<?php include("top.php"); ?>
<b>Some stuff about elmastats</b><br/><br/>
elmastats is a site by jon<br/>
its a statistics site for <a href="http://www.elastomania.com">elastomania</a> by balazs rozsa :D<br/>
start by kreating an acc <a href="register.php">here</a>, then upload your state.dat file <a href="up.php">here</a>!<br/>
there are a couple of advantages of using teh state.dat instead of stats.txt, such as:<br/>
&nbsp;- its encrypted, which makes it much harder to hax<br/>
&nbsp;- you dont have to quit elma for it to update (you can just alt-tab and upload when youve done a new pr/wr)<br/>
&nbsp;- i guess thats it<br/>
anyway heres some lists<br/><br/><br/>
<?php
  $targets = loadTargets();

  function parseLine2($cline) {
    $data = array();
    $data["nick"] = substr($cline, 0, strpos($cline, "|"));
    $cline = substr($cline, strpos($cline, "|")+1);
    $data["int"] = substr($cline, 0, strpos($cline, "|"));
    $cline = substr($cline, strpos($cline, "|")+1);
    $data["time1"] = substr($cline, 0, strpos($cline, "|"));   
    $cline = substr($cline, strpos($cline, "|")+1);
    $data["time2"] = substr($cline, 0, strpos($cline, "|"));
    $data["time"] = substr($cline, strpos($cline, "|")+1);
    return $data;
  }
  
  echo("<div class=\"lefty\">");
  echo("<div class=\"box2\">");
  echo("<b>Last improvements</b><br/><br/>");
  $lines = is_readable("newtajms2") ? file("newtajms2") : [];
  if (!is_array($lines)) $lines = [];
    echo("<table>");
    echo("<tr><th width=\"160px\">Kuski</th><th width=\"32px\">Int</th><th width=\"218px\">Improvement</th><th>When</th></tr>");
    $levelTimesByUser = array();

  foreach ($users as $rankUser) {
    $rankNick = $rankUser["nick"] ?? "";
    if ($rankNick === "" || $rankNick === "!.gitignore" || $rankNick === "*") {
      continue;
    }

    $rankTimes = stateTimes($rankNick, $rankUser["elmaname"] ?? "");
    if ($rankTimes === NULL) {
      continue;
    }

    for ($level = 1; $level <= 54; $level++) {
      $rankTime = (int)($rankTimes[$level][1] ?? 0);
      if ($rankTime > 0) {
        $levelTimesByUser[$level][] = array(
          "nick" => $rankNick,
          "time" => $rankTime
        );
      }
    }
  }
  $xs = count($lines);
  if ($xs > 30) $xs = 30;
  $y=0;
  for ($x = 0;$y < $xs;$x++) {
    $data = parseLine2(ld($lines[count($lines)-1-$x]));
    $level = (int)$data["int"];
    $oldTime = (int)$data["time1"];
    $newTime = (int)$data["time2"];
    $oldRank = 1;
    $newRank = 1;

    foreach ($levelTimesByUser[$level] ?? array() as $rankEntry) {
      if ($rankEntry["nick"] === $data["nick"]) {
        continue;
      }

      if ($rankEntry["time"] < $oldTime) $oldRank++;
      if ($rankEntry["time"] < $newTime) $newRank++;
    }
    if (man($data["nick"]) != $data["nick"]) {
      echo("<tr>");
      echo("<td>" . man($data["nick"]) . "</td>");
      echo("<td>" . internal($data["int"], true, false, false, true) . "</td>");

      $oldRankText = ($oldRank < 100) ? " (" . $oldRank . ".)" : "";
      $newRankText = ($newRank < 100) ? " (" . $newRank . ".)" : "";

    echo("<td style=\"white-space:nowrap;\">" .
         "<span style=\"display:inline-block; width:9ch; text-align:left;\">" .
         sttime($data["nick"], $data["int"], $data["time1"], false, false, target($data["time1"], $data["int"])) .
         "</span>" .
         "<span style=\"display:inline-block; width:5ch; text-align:right;\">" .
         $oldRankText .
         "</span>" .
         "<span style=\"display:inline-block; width:4ch; text-align:center;\">-&gt;</span>" .
         "<span style=\"display:inline-block; width:9ch; text-align:left;\">" .
         sttime($data["nick"], $data["int"], $data["time2"], false, false, target($data["time2"], $data["int"])) .
         "</span>" .
         "<span style=\"display:inline-block; width:5ch; text-align:right;\">" .
         $newRankText .
         "</span></td>");

      echo("<td>" . date("d/m/y", ttime($data["time"])) . "</td>");
      echo("</tr>");
      $y++;
      }
    }
    echo("</table>");
    echo("</div></div>");
    
  function parseLine($cline) {
    $data = array();
    $data["nick"] = substr($cline, 0, strpos($cline, "|"));
    $cline = substr($cline, strpos($cline, "|")+1);
    $data["tt"] = substr($cline, 0, strpos($cline, "|"));
    $data["time"] = substr($cline, strpos($cline, "|")+1);
    return $data;
  }
  
  echo("<div class=\"lefty\" style=\"float:right; margin-right:0px;\">");
  echo("<div class=\"box2\">");
  echo("<b>Last TT minute breaks</b><br/><br/>");
  $lines = file("newttz");
  echo("<table>");
  echo("<tr><th width=\"160px\">Kuski</th><th width=\"32px\">TT</th><th>When</th></tr>");
  $currentBreakTTs = array();
  $shownBreaks = array();
  if ($lines) {
    $lineCount = count($lines);
    $shown = 0;

    for ($x = 0; $x < $lineCount && $shown < 30; $x++) {
      $data = parseLine(ld($lines[$lineCount - 1 - $x]));
      $nick = $data["nick"];

      if (!array_key_exists($nick, $currentBreakTTs)) {
        $playerTimes = stateTimes($nick, $users[$nick]["elmaname"] ?? "");
        if ($playerTimes === NULL) {
          $currentBreakTTs[$nick] = null;
        } else {
          $currentTotal = 0;

          for ($level = 1; $level <= 54; $level++) {
            $time = (int)($playerTimes[$level][1] ?? 0);
            $currentTotal += ($time > 0) ? min($time, 60000) : 60000;
          }
          $currentBreakTTs[$nick] = $currentTotal;
        }
      }

      $breakStillValid = $currentBreakTTs[$nick] !== null
        && $currentBreakTTs[$nick] < (((int)$data["tt"] + 1) * 6000);

      if ($breakStillValid &&
          $data["nick"] != "" &&
          man($data["nick"]) != $data["nick"] &&
          (int)$data["tt"] < 60) {
        $breakKey = strtolower(trim($data["nick"])) . "|" . (int)$data["tt"];

        if (isset($shownBreaks[$breakKey])) {
          continue;
        }

        $shownBreaks[$breakKey] = true;
        echo("<tr>");
        echo("<td>" . man($data["nick"]) . "</td>");
        echo("<td>" . $data["tt"] . "</td>");
        echo("<td>" . date("d/m/y", ttime($data["time"])) . "</td>");
        echo("</tr>");
        $shown++;
      }
    }
  }
  echo("</table>");
  echo("</div></div>");
  echo('<div style="clear:both;"></div>');
  /*
  $lines = file("newtajms");
  echo("<table>");
  echo("<tr><th class=\"left\" width=\"180px\">Kuski</th><th class=\"left\" width=\"170px\">Internal</th><th class=\"left\" width=\"170px\">Improvement</th><th class=\"left\" width=\"150px\">Date</th></tr>");
  $xs = count($lines)-1-30;
  if ($xs < 0) $xz = 0;
  for ($x = count($lines)-1;$x > $xs;$x--) {
    echo(substr($lines[$x], 0, strlen($lines[$x])-1));
  }
  echo("</table>");
  */
?>
<?php include("tpo.php"); ?>
