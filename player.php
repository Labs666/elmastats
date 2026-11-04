<?php include("top.php"); ?>
<?php
  if ($users[$_GET["player"]]["nick"] != "") {

    $targets = loadTargets();

    $cItem = $users[$_GET["player"]];
    echo("<b>" . $cItem["nick"] . "</b>");
    echo("<br/><br/>");
    echo("<table>");
    $cnames = countries();
    //if($cItem["nick"]=="AndrY") $f=flag2("soviet");
    //else
    $f=flag($cItem["country"]);
    echo("<tr><td width=\"100px\">Nationality:</td><td>" . $f . " " . $cnames[strtoupper($cItem["country"])] . "</td></tr>");
    if ($cItem["team"] != "") {
      echo("<tr><td>Team:</td><td><a href=\"team.php?team=" . fix($cItem["team"]) . "\">" . fix($cItem["team"]) . "</a></td></tr>");
    } else {
      echo("<tr><td>Team:</td><td>none</td></tr>");
    }
    echo("<tr><td>Registered:</td><td>" . gmdate("F jS, Y - H:i:s", ttime($cItem["registered"])) . "</td></tr>");

    $utimes = stateTimes($cItem["nick"], $cItem["elmaname"]);
    $utt = 0;
    for ($y = 0;$y < 54;$y++) $utt += ($utimes[$y+1][1] > 0 ? $utimes[$y+1][1] : 60000);
    if ($utimes != NULL) {
      $vtimes = array();
      $c = 0;
      foreach ($users as $cItem) {
        $vtimes[] = stateTimes($cItem["nick"], $cItem["elmaname"]);
        $c++;
      }
      $tts = array();
      for ($x = 0;$x < $c;$x++) {
        $tt = 0;
        for ($y = 0;$y < 54;$y++)
          $tt += ($vtimes[$x][$y+1][1] > 0 ? $vtimes[$x][$y+1][1] : 60000);
        $tts[] = $tt;
      }
      sort($tts, SORT_NUMERIC);
      for ($x = 0;$x < $c;$x++) {
        if ($utt == $tts[$x]) {
          echo("<tr><td>Ranking:</td><td>#" . ($x+1) . "/" . $c . "</td></tr>");
          break;
        }
      }
    }

    echo("</table>");

    echo("<br/><br/>");
    if ($utimes == NULL) {
      echo("<span style=\"color: #FF0000\">This kuski man hasn't uploaded stats yet!</span><br/>");
    } else {
      $cItem = $users[$_GET["player"]];
      echo("<b>Times (updated " . gmdate("F jS, Y - H:i:s", ttime(filemtime("statefilesz/" . $cItem["nick"] . ".dat"))) . ")</b> ");
      echo("<a href=\"txt.php?u=" . $cItem["nick"] . "\">download stats.txt</a><br/><br/>");
      echo("<div class=\"tabber\">");

      echo("<div class=\"tabbertab\" title=\"Times\">");

      echo("<table class=\"times\" cellpadding=\"2\">");
      echo("<tr><th class=\"times\" width=\"180px\">Internal</th>");
      echo("<th class=\"times\" width=\"81px\"><center>Best time</center></th>");
      echo("<th class=\"times\" width=\"16px\"></th>"); // Spacer
      echo("<th class=\"times\" width=\"81px\"><center>Average</center></th>");
      echo("<th class=\"times\" width=\"81px\"><center>Diff. average</center></th>");
      echo("<th class=\"times\" width=\"16px\"></th>"); // Spacer
      echo("<th class=\"times\" width=\"81px\"><center>Next target</center></th>");
      echo("<th class=\"times\" width=\"81px\"><center>Diff. next</center></th>");
      echo("</tr>");
      $tt = 0;
      $ttIfBeatTargets = 0;
      $avgtt = 0;
      $sumofdiff = 0;
      for ($x = 0;$x < 54;$x++) {
        echo("<tr><td class=\"times\">" . internal($x+1, true) . "</td>");
        $bestTime = (int)($utimes[$x + 1][1] ?? 0);
        if ($bestTime <= 0) {
          $tt += 60000;
          $ttIfBeatTargets += 60000;
          $avgtt += 600000;

          echo("<td class=\"times\"><center></center></td>");
          echo("<td class=\"times\"></td>"); // Spacer
          echo("<td class=\"times\"><center>" . sttime($cItem["nick"], $x + 1, 60000, true, false, target(60000, $x + 1)) . "</center></td>");
          echo("<td class=\"times\"><center>-</center></td>");
          echo("<td class=\"times\"></td>"); // Spacer
          $emptyTarget = target(60000, $x + 1);
          if ($emptyTarget > 0) {
            $nextTargetTime = (int)$targets[$x + 1][$emptyTarget - 1];
            $ttIfBeatTargets -= 60000 - $nextTargetTime;
          }
          echo("<td class=\"times\"><center>" . ($emptyTarget > 0 ? tartime($x + 1, $targets[$x + 1][$emptyTarget - 1], $emptyTarget - 1) : "-") . "</center></td>");
          $nextTargetTime = ($emptyTarget > 0)
          ? (int)$targets[$x + 1][$emptyTarget - 1]
          : 0;

          echo("<td class=\"times\"><center>" .
          ($emptyTarget > 0
          ? "+" . formatElmaTime(60000 - $nextTargetTime)
          : "-") .
          "</center></td>");
          echo("</tr>");
          continue;
        }
        $t = target($bestTime, $x + 1);

        if ($bestTime <= 0) {
          $tt += 60000;
          $ttIfBeatTargets += 60000;
          $avgtt += 600000;

          if ($t > 0) {
            $ttChangeIfBeatTarget = abs((int)$targets[$x + 1][$t - 1]);
            $ttIfBeatTargets -= $ttChangeIfBeatTarget;
          }

          for ($column = 0; $column < 7; $column++) {
            echo("<td class=\"times\"></td>");
          }
          echo("</tr>");
          continue;
        }
        echo("<td class=\"times\"><center>" . sttime($cItem["nick"], $x+1, $utimes[$x+1][1], true, false, $t) . "</center></td>");
        echo("<td class=\"times\"></td>"); // Spacer
        $avg = 0;
        for ($y = 0;$y < 10;$y++) {
          $value = $utimes[$x+1][$y+1] ?? 0;
          $avg += (!$value ? 60000 : (int)$value);
          //$avg += ($utimes[$x+1][$y+1] == 0 ? 60000 : $utimes[$x+1][$y+1]);
        }
        $avgtt += $avg;
        $avg = round($avg/10);
        $AvgDiff = $avg - (int)$utimes[$x+1][1];
        echo("<td class=\"times\"><center>" . sttime($cItem["nick"], $x+1, $avg , true, false, target($avg, $x+1)) . "</center></td>");
        echo("<td class=\"times\"><center>" . ($AvgDiff > 0 ? "+" . formatElmaTime($AvgDiff) : "-") . "</center></td>");
        echo("<td class=\"times\"></td>"); // Spacer

        $tt += (int)($utimes[$x+1][1] == 0 ? 60000 : $utimes[$x+1][1]);
        $ttIfBeatTargets += (int)($utimes[$x+1][1] == 0 ? 60000 : $utimes[$x+1][1]);

        if($t > 0) {
          $ttChangeIfBeatTarget = abs((int)$targets[$x+1][$t-1]-(int)$utimes[$x+1][1]);
          $ttIfBeatTargets -= $ttChangeIfBeatTarget;
        }

        echo("<td class=\"times\"><center>" . ($t > 0 ? tartime($x+1, $targets[$x+1][$t-1], $t-1) : "-") . "</center></td>");
        echo("<td class=\"times\"><center>" . ($t > 0 ? "+" . formatElmaTime(abs($ttChangeIfBeatTarget)) : "-") . "</center></td>");

        echo("</tr>");
      }
      $avgtt = round($avgtt/10);
      $TTandAvgDiff = $avgtt - $tt;
      $sumofdiff = $tt - $ttIfBeatTargets;

      echo("<tr>");
      for ($column = 0; $column < 8; $column++) {
      echo("<th class=\"times\"></th>");
      }
      echo("</tr>");
      echo("<tr><td class=\"times\">Total time</td>");
      echo("<td class=\"times\"><center>" . coloredtttime($tt, true) . "</center></td>");
      echo("<td class=\"times\"></td>"); // Spacer
      echo("<td class=\"times\"><center>" . coloredtttime($avgtt, true) . "</center></td>");
      echo("<td class=\"times\"><center>" . ($TTandAvgDiff > 0 ? "+" . formatElmaTime($TTandAvgDiff, true) : "-") . "</center></td>");
      echo("<td class=\"times\"></td>"); // Spacer
      echo("<td class=\"times\"><center>" . coloredtttime($ttIfBeatTargets, true) . "</center></td>");
      echo("<td class=\"times\"><center>" . ($sumofdiff > 0 ? "+" . formatElmaTime($sumofdiff, true) : "-")  . "</center></td>");
      echo("</tr>");

      echo("</table>");
      echo("</div>");


      echo("<div class=\"tabbertab\" title=\"Full top10 table\">");
      echo("<table class=\"times\" cellpadding=\"2\">");
      echo("<tr><th class=\"times\" width=\"24px\">Int</th>");
      for ($x = 0;$x < 10;$x++) {
        echo("<th class=\"times\" width=\"81px\"><center>" . postr($x+1) . "</center></th>");
      }
      echo("<th class=\"times\" width=\"81px\"><center>Average</center></th>");
      echo("</tr>");

      $targetz = array(0, 0, 0, 0, 0, 0, 0, 0, 0);
      for ($x = 0;$x < 54;$x++) {
        $str = "";
        if ($x+1 < 10) $str = "&nbsp;";
        //echo("<tr><td class=\"times\">" . ($x+1) . ". " . $str . $intnames[$x+1] . "</td>");
        echo("<tr><td class=\"times\">" . internal($x+1, true, false, false) . "</td>");
        $bestTime = (int)($utimes[$x + 1][1] ?? 0);

        if ($bestTime <= 0) {
          for ($y = 0; $y < 10; $y++) {
            echo("<td class=\"times\"></td>");
          }
          echo("<td class=\"times\"><center>" . sttime($cItem["nick"], $x + 1, 60000, true, false, target(60000, $x + 1)) . "</center></td>");
          echo("</tr>");
          continue;
        }
        $avg = 0;
        for ($y = 0;$y < 10;$y++) {
          //if ($y == 0) {
          //echo("<td class=\"times\"" . $tcolors[target($utimes[$x+1][$y+1], $x)] . "><center>" . sttime($cItem["nick"], $x+1, $utimes[$x+1][$y+1], true) . "</center></td>");
          $value = (int)($utimes[$x + 1][$y + 1] ?? 0);

          if ($value > 0) {
            $t = target($value, $x + 1);
            echo("<td class=\"times\"><center>" . sttime($cItem["nick"], $x + 1, $value, true, false, $t) . "</center></td>");
            if ($y == 0) $targetz[$t]++;
          } else {
            echo("<td class=\"times\"></td>");
          }
          /*} else {
            echo("<td class=\"times\"><center>" . formatElmaTime($utimes[$x+1][$y+1], true) . "</center></td>");
          }*/
          $avg += (!$utimes[$x+1][$y+1] ? 60000 : $utimes[$x+1][$y+1]);
        }

        $avg = round($avg/10);
        echo("<td class=\"times\"><center>" . sttime($cItem["nick"], $x+1, $avg , true, false, target($avg, $x+1)) . "</center></td>");
        //echo("<td class=\"times\"><center>" . formatElmaTime(round($avg/10), true) . "</center></td>");
        echo("</tr>");
      }
      echo("<tr>");
      for ($column = 0; $column < 12; $column++) {
      echo("<th class=\"times\"></th>");
      }
      echo("</tr>");
      echo("<tr><td class=\"times\">TT</td>");
      echo("<td class=\"times\" colspan=\"10\"><center>" . coloredtttime($tt, true) . "</center></td>");
      echo("<td class=\"times\"><center>" . coloredtttime($avgtt, true) . "</center></td>");
      echo("</tr>");
      echo("</table>");
      echo("</div>");


      echo("<div class=\"tabbertab\" title=\"Improvements\">");
      echo("Total time improvement (since 14/12/10)<br/><br/>");
      $n = 0;
      while (file_exists("historyz/" . $_GET["player"] . "_" . $n)) $n++;
      if ($n > 1) {
        echo("<img alt=\"tt improvementz\" src=\"graph2.php?u=" . $_GET["player"] . "&amp;m=tt&amp;w=780&amp;h=352" . ($users[$_SESSION["nick"]]["theme"] == "Klassik" || $users[$_SESSION["nick"]]["theme"] == "Mopo" ? "&amp;dark=1" : "") . "&amp;fcuk=" . time() . "\"/>");
      } else {
        echo(error("this user has only uploaded stats once!!") . "<br/>");
      }
      echo("</div>");

      echo("</div>");


      echo("<br/><br/><b>Targets</b><br/><br/>");
      $ts = "s"; if ($targetz[0] == 1) $ts = "";
      echo($targetz[0] . " <span style=\"color: #FF0000\">world record" . $ts . "</span><br/>");

      $ts = "s"; if ($targetz[1] == 1) $ts = "";
      echo($targetz[1] . " <span style=\"color: #AA43DD\">godlike time" . $ts . "</span><br/>");

      $ts = "s"; if ($targetz[2] == 1) $ts = "";
      echo($targetz[2] . " <span style=\"color: #FF66CC\">legendary time" . $ts . "</span><br/>");

      $ts = "s"; if ($targetz[3] == 1) $ts = "";
      echo($targetz[3] . " <span style=\"color: #FF9C00\">world class time" . $ts . "</span><br/>");

      $ts = "s"; if ($targetz[4] == 1) $ts = "";
      echo($targetz[4] . " <span style=\"color: #FFF200\">professional time" . $ts . "</span><br/>");

      $ts = "s"; if ($targetz[5] == 1) $ts = "";
      echo($targetz[5] . " <span style=\"color: #00FF00\">good time" . $ts . "</span><br/>");

      $ts = "s"; if ($targetz[6] == 1) $ts = "";
      echo($targetz[6] . " <span style=\"color: #0090FF\">OK time" . $ts . "</span><br/>");

      $ts = "s"; if ($targetz[7] == 1) $ts = "";
      echo($targetz[7] . " <span style=\"color: #F3F5CA\">beginner time" . $ts . "</span><br/>");

      $ts = "s"; if ($targetz[8] == 1) $ts = "";
      echo($targetz[8] . " finished time" . $ts . "<br/>");

echo("<br/><br/><b>Compare to...</b><br/><br/>");

echo('<form action="compare.php" method="get">');
echo('<input type="hidden" name="u1" value="' .
     htmlspecialchars($cItem["nick"], ENT_QUOTES, "UTF-8") . '">');

echo('<select name="u2" onchange="this.form.submit()">');
echo('<option value="">Choose a player...</option>');

$compareUsers = $users;
uasort($compareUsers, function ($a, $b) {
  return strcasecmp($a["nick"] ?? "", $b["nick"] ?? "");
});
foreach ($compareUsers as $dItem) {
  $nick = $dItem["nick"] ?? "";

  if ($nick === "" || $nick === $cItem["nick"]
      || $nick === "!.gitignore" || $nick === "*") {
    continue;
  }

  $safeNick = htmlspecialchars($nick, ENT_QUOTES, "UTF-8");
  echo('<option value="' . $safeNick . '">' . $safeNick . '</option>');
}

echo('</select>');
echo('</form>');

      echo("<br/><br/>");
      echo("<b>Userbars and stuff</b><br/><br/>");
      echo("<div class=\"box\">");
      echo("<b>Standard</b><br/>");
      addub("ttst.php?u=" . $_GET["player"] . "&amp;bg=r", $_GET["player"]);
      addub("ttst.php?u=" . $_GET["player"] . "&amp;bg=y", $_GET["player"]);
      addub("ttst.php?u=" . $_GET["player"] . "&amp;bg=g", $_GET["player"]);
      addub("ttst.php?u=" . $_GET["player"], $_GET["player"]);
      addub("ttst.php?u=" . $_GET["player"] . "&amp;bg=p", $_GET["player"]);
      addub("ttst.php?u=" . $_GET["player"] . "&amp;bg=k", $_GET["player"]);
      echo("<br/><b>Simpel and customizabel</b><br/>");
      addub("tt.php?u=" . $_GET["player"], $_GET["player"]);
      addub("tt.php?u=" . $_GET["player"] . "&amp;s=TT:%20%5B:tt:%5D&amp;bg=000000&amp;c=FF0000", $_GET["player"]);
      echo("<br/><b>Eol</b><br/>");
      addub("bub.php?u=" . $_GET["player"], $_GET["player"]);
      addub("bub.php?u=" . $_GET["player"] . "&amp;s=&lt;" . $_GET["player"] . "&gt;%20hi%20my%20TT%20is%20:tt:%20:D)", $_GET["player"]);
      echo("</div>");
    }
  } else {
    echo("<span style=\"color: #FF0000\">Player \"" . $_GET["player"] . "\" doesn't exist!</span><br/>");
  }
?>
<?php include("tpo.php"); ?>
