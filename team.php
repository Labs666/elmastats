<?php include("top.php"); ?>
<style>
  #mid {
    overflow-x: hidden;
  }

  #teamViewport {
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: none;
  }

  #teamViewport::-webkit-scrollbar {
    display: none;
  }

  #teamScrollTop,
  #teamScrollBottom {
    display: block !important;
    max-width: 100%;
    overflow-x: scroll !important;
    overflow-y: hidden !important;
    scrollbar-width: thin !important;
  }

  #teamScrollTop::-webkit-scrollbar,
  #teamScrollBottom::-webkit-scrollbar {
    display: block;
    height: 8px;
  }

  #teamTable th,
  #teamTable td {
    border: 0 !important;
    border-right: 1px dotted #000000 !important;
    border-bottom: 1px dotted #000000 !important;
  }

  #teamTable th:not(:first-child),
  #teamTable td:not(:first-child) {
    border-right: 1px dotted #000000 !important;
  }

  #teamTable tr:first-child th {
    border-bottom: 1px dotted #000000 !important;
  }

  #teamTable th:first-child,
  #teamTable td:first-child {
    position: sticky;
    left: 0;
    z-index: 20;
    isolation: isolate;
    background: #303030 !important;
    text-indent: 0px; /*changes levelnames position*/
    border-left: 1px solid #000000 !important;
    border-right-style: none !important;
    border-right: 1px dotted #000000 !important;
  }

  #teamTable th:first-child {
    z-index: 21;
    background: #3F3F3F !important;
  }
  #teamTable {
    border-left: 0 !important;
  border-right: 0 !important;
    border-bottom: 0 !important;
  }

  #teamTable tr:last-child td {
    border-bottom: 1px dotted #000000 !important;
  }
  #teamTable th:last-child,
  #teamTable td:last-child {
    border-right: 1px solid #000000 !important;
  }
  #teamCombinedTT {
    border-right: 0 !important;
  }

  #teamCombinedTT.at-right {
    box-shadow: inset -1px 0 0 #000000;
  }
  #teamCombinedTT td:last-child {
    background-image: repeating-linear-gradient(
      to bottom,
      #000000 0 1px,
      transparent 1px 3px
    ) !important;
    background-size: 1px 100%;
    background-position: calc(100% - 614px) top;
    background-repeat: no-repeat;
  }
</style>
<?php
  $dudes = array();
  $c = 0;
  $nousers = true;
  $eror = false;
  if ($_GET["team"] == "") {
    echo("<b>Players without team</b><br/>");
    $eror = true;
  }
  foreach ($users as $cItem) {
    if (empty($cItem["nick"]) || $cItem["nick"] === '!.gitignore' || $cItem["nick"] === '*') {
      continue;
    }
    if ($cItem["team"] == $_GET["team"]) {
      if ($nousers) {
        $nousers = false;
        if ($_GET["team"] != "") echo("<b>" . $_GET["team"] . "</b><br/><br/>");
      }
      //echo(flag($cItem["country"]) . " <a href=\"player.php?player=" . $cItem["nick"] . "\">" . $cItem["nick"] . "</a><br/>");
      $dudes[$c] = $cItem["nick"];
      $c++;
    }
  }
  natcasesort($dudes);
  $dudes = array_values($dudes);
  foreach ($dudes as $nick) {
    echo(man($nick, false) . "<br/>");
  }
  if ($nousers) {
    echo("<span style=\"color: #FF0000\">Team \"" . $_GET["team"] . "\" has no kuskis!</span><br/>");
    $eror = true;
  }
  if (!$eror) {
    $utimes = array();
    for ($x = 0;$x < $c;$x++) {
      $utimes[$x] = stateTimes($dudes[$x], $users[$dudes[$x]]["elmaname"]);
    }

    echo("<br/><br/><b>Team table</b><br/><br/>");
    $scrollbarDisplay = ($c <= 7) ? "none" : "block";
    echo('<div id="teamScrollTop" style="display:' . $scrollbarDisplay . ' !important; max-width:100%; overflow-x:auto; overflow-y:hidden; scrollbar-width:thin; height:16px;"><div id="teamScrollTopInner" style="height:1px;"></div></div>');
    echo('<div id="teamViewport" style="max-width:100%; overflow-x:auto; overflow-y:hidden; scrollbar-width:none;">');
    echo('<table id="teamTable" class="times" cellpadding="2" style="white-space:nowrap; width:max-content; border-collapse:separate; border-spacing:0;">');
    echo("<tr><th class=\"times\" width=\"180px\">Level</th>");

    for ($y = 0; $y < $c; $y++) {
      echo("<th class=\"times\" width=\"81px\"><center><a href=\"player.php?player=" . $dudes[$y] . "\">" . $dudes[$y] . "</a></center></th>");
    }

    echo("</tr>");
    $teamtt = 0;
    $victories = array();
    for ($x = 0;$x < 54;$x++) {
      echo("<tr><td class=\"times\">" . internal($x+1, true) . "</td>");
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
      if ($trman >= 0) {
        $victories[$trman] = ($victories[$trman] ?? 0) + 1;
      }
      for ($y = 0;$y < $c;$y++) {

                
        //This function call fetches the target time threshold for the current player we're printing, on the current internal. 
        //It returns an integer from 0 to 8 which then gets passed to the coloured time formatter
        $t = target($utimes[$y][$x+1][1], $x+1);

        //We format each cell differently based on its traits. 
        //The team-record holder ($trman) should be in bold.
        //We co-opt the tooltip element to display difference from the displayed time to the team-record holder.
        //Finally, we also style each time based on target-time colours.

        $bulle = ""; $bulle2 = "";
        if ($trman == $y) { $bulle = "<span style=\"color: #00AA00\">"; $bulle2 = "</span>"; }
        if ($trtajm > $utimes[$y][$x+1][1]) { $wry = "-"; } else { $wry = "+"; }
          $diff2 = abs((int)$utimes[$y][$x+1][1]-(int)$trtajm);
          $str = "";
        if ($trman >= 0 && $y != $trman) {
          $str = $wry . formatElmaTime($diff2) . " to " . $dudes[$trman] . "'s time";
        }
        if ($y == $trman || $trman == -1) $str = "";

          $playerTime = (int)($utimes[$y][$x + 1][1] ?? 0);
          $timeHtml = ($playerTime <= 0)
          ? ""
          : sttime($dudes[$y], $x + 1, $playerTime, true, $trman == $y, $t);
        if ($trman == $y) {
          $timeHtml = str_replace("<a ", "<a style=\"color: #00FF00 !important\" ", $timeHtml);
        }

        echo("<td class=\"times\" title=\"" . $str . "\"><center>" . $bulle . $timeHtml . $bulle2 . "</center></td>");
      }
      echo("</tr>");
    }
    echo("<tr>");
    for ($column = 0; $column < $c + 1; $column++) {
    echo("<th class=\"times\"></th>");
    }
    echo("</tr>");
    echo("<tr><td class=\"times\">Total Time</td>");
    $ttrman = 0;
    $tts = array();
    for ($y = 0;$y < $c;$y++) {
      $tt = 0;
      for ($x = 0;$x < 54;$x++) {
      $time = (int)($utimes[$y][$x + 1][1] ?? 0);
      $tt += ($time > 0) ? $time : 60000;
      }
      $tts[$y] = $tt;
      if ($tts[$y] < $tts[$ttrman]) $ttrman = $y;
    }
    for ($y = 0;$y < $c;$y++) {
    echo("<td class=\"times\"><center>" . coloredtttime($tts[$y], true) . "</center></td>");
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
    echo("</table>");
    echo("</div>");
    echo('<table id="teamCombinedTT" data-team-size="' . $c . '" class="times" cellpadding="2" style="width:100%; table-layout:fixed; border-collapse:separate; border-spacing:0; border-top:none !important; border-left:1px solid #000000 !important; border-bottom:1px solid #000000 !important;">');
    echo('<tr><td class="times" style="width:180px; border-top:none !important; border-left:none !important; border-bottom:none !important; border-right:1px dotted #000000 !important; text-align:left;">Combined TT</td><td class="times" style="text-align:center; border:none !important;"><center>' . coloredtttime($teamtt, true) . '</center></td></tr>');
    echo('</table>');
    echo('<div id="teamScrollBottom" style="display:' . $scrollbarDisplay . ' !important; max-width:100%; overflow-x:auto; overflow-y:hidden; scrollbar-width:thin; height:16px; transform:translateY(-6px);"><div id="teamScrollBottomInner" style="height:1px;"></div></div>');
echo('<script>
(function () {
  var topScroll = document.getElementById("teamScrollTop");
  var topInner = document.getElementById("teamScrollTopInner");
  var bottomScroll = document.getElementById("teamScrollBottom");
  var bottomInner = document.getElementById("teamScrollBottomInner");
  var viewport = document.getElementById("teamViewport");
  var combinedTT = document.getElementById("teamCombinedTT");

  function updateCombinedBorder() {
    var maxScroll = viewport.scrollWidth - viewport.clientWidth;
    combinedTT.classList.toggle(
      "at-right",
      viewport.scrollLeft >= maxScroll
    );
  }

  viewport.addEventListener("scroll", updateCombinedBorder);
  var table = document.getElementById("teamTable");
  var combinedTT = document.getElementById("teamCombinedTT");
  var lastSource = null;
  var syncing = false;

  function syncWidth() {
    var scrollWidth = viewport.scrollWidth + "px";
    topInner.style.width = scrollWidth;
    bottomInner.style.width = scrollWidth;

    if (parseInt(combinedTT.getAttribute("data-team-size"), 10) <= 7) {
      combinedTT.style.width = table.getBoundingClientRect().width + "px";
    }
  }

  function moveTable(source) {
    if (syncing) return;
    lastSource = source;
    viewport.scrollLeft = source.scrollLeft;
  }

  topScroll.onscroll = function () {
    moveTable(topScroll);
  };

  bottomScroll.onscroll = function () {
    moveTable(bottomScroll);
  };

  function syncOnRelease() {
    if (!lastSource) return;

    var source = lastSource;
    var target = source === topScroll ? bottomScroll : topScroll;

    syncing = true;
    target.scrollLeft = source.scrollLeft;
    viewport.scrollLeft = source.scrollLeft;
    lastSource = null;

    window.setTimeout(function () {
      syncing = false;
    }, 50);
  }

  document.addEventListener("pointerup", syncOnRelease);
  document.addEventListener("mouseup", syncOnRelease);

  syncWidth();
  updateCombinedBorder();

  window.addEventListener("resize", function () {
    syncWidth();
    updateCombinedBorder();
  });
})();
</script>');
    echo("<br/><br/>");
    echo("<b>Team records</b><br/><br/>");
    echo("<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">");
    for ($x = 0; $x < $c; $x++) {
      if (!isset($victories[$x])) {
        $victories[$x] = 0;
      }
    }

    uksort($victories, function ($a, $b) use ($victories, $dudes) {
      $countOrder = $victories[$b] <=> $victories[$a];
      if ($countOrder !== 0) {
        return $countOrder;
      }
      return strcasecmp($dudes[$a], $dudes[$b]);
    });
    foreach ($victories as $x => $victoryCount) {
      echo("<tr><td>" . man($dudes[$x]) . "&nbsp;</td>");
      echo("<td>" . $victoryCount . " records</td></tr>");
    }
    echo("<tr><td colspan=\"2\">&nbsp;</td></tr>");
    echo("<tr><td><b>Best TT</b>&nbsp;</td><td>" . man($dudes[$ttrman]) . "</td></tr>");
    echo("</table>");
  }
?>
<?php include("tpo.php"); ?>
