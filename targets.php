<?php include("top.php"); 
  $info = fetchtargetinfo();
  
  echo("<table class=\"times\">\n");
  echo("<th class=\"times\" width=\"250px\">Internal</th>");
  echo("<th class=\"times\" width=\"81px\"><center>World Record</center></th>\n");
  echo("<th class=\"times\" width=\"81px\"><center>Godlike</center></th>\n");
  echo("<th class=\"times\" width=\"81px\"><center>Legendary</center></th>\n");
  echo("<th class=\"times\" width=\"81px\"><center>World Class</center></th>\n");
  echo("<th class=\"times\" width=\"81px\"><center>Professional</center></th>\n");
  echo("<th class=\"times\" width=\"81px\"><center>Good</center></th>\n");
  echo("<th class=\"times\" width=\"81px\"><center>OK</center></th>\n");
  echo("<th class=\"times\" width=\"81px\"><center>Beginner</center></th>\n");

  for ($x = 0;$x < 54;$x++) {

    //Increment target total times
    $tt0 = $tt0 + $info[$x+1]["0"];
    $tt1 = $tt1 + $info[$x+1]["1"];
    $tt2 = $tt2 + $info[$x+1]["2"];
    $tt3 = $tt3 + $info[$x+1]["3"];
    $tt4 = $tt4 + $info[$x+1]["4"];
    $tt5 = $tt5 + $info[$x+1]["5"];
    $tt6 = $tt6 + $info[$x+1]["6"];
    $tt7 = $tt7 + $info[$x+1]["7"];

      //generate table
      echo("<tr><td class=\"times\">" . internal($x+1, true) . "</td>");
      echo("<td class=\"times\"><center>" . sttime("targets", $x+1, $info[$x+1]["0"], true, false, 0, false). "</center></td>");
      echo("<td class=\"times\"><center>" . tartime($x+1, $info[$x+1]["1"], 1) . "</center></td>");
      echo("<td class=\"times\"><center>" . tartime($x+1, $info[$x+1]["2"], 2) . "</center></td>");
      echo("<td class=\"times\"><center>" . tartime($x+1, $info[$x+1]["3"], 3) . "</center></td>");
      echo("<td class=\"times\"><center>" . tartime($x+1, $info[$x+1]["4"], 4) . "</center></td>");
      echo("<td class=\"times\"><center>" . tartime($x+1, $info[$x+1]["5"], 5) . "</center></td>");
      echo("<td class=\"times\"><center>" . tartime($x+1, $info[$x+1]["6"], 6) . "</center></td>");
      echo("<td class=\"times\"><center>" . tartime($x+1, $info[$x+1]["7"], 7) . "</center></td>");
      echo("</tr>\n");
    }
    //Spacer row
    echo("<th class=\"times\"></th><th class=\"times\"></th><th class=\"times\"></th><th class=\"times\"></th><th class=\"times\"></th><th class=\"times\"></th><th class=\"times\"></th><th class=\"times\"></th><th class=\"times\"></th>");
  
    //Total times
    echo("<tr><td class=\"times\">" . "Total time: " . "</td>");
    echo("<td class=\"times\"><center>" . formatElmaTime($tt0) . "</center></td>");
    echo("<td class=\"times\"><center><a href=\"https://up.elma.online/u/7refxarroc/targetGOD.zip\">" . formatElmaTime($tt1) . "</a></center></td>");
    echo("<td class=\"times\"><center><a href=\"https://up.elma.online/u/upp1a3fym8/targetLEG.zip\">" . formatElmaTime($tt2) . "</a></center></td>");
    echo("<td class=\"times\"><center><a href=\"https://up.elma.online/u/alhjjg0xqg/targetWC.zip\">" . formatElmaTime($tt3) . "</a></center></td>");
    echo("<td class=\"times\"><center><a href=\"https://up.elma.online/u/e6252sj434/targetPRO.zip\">" . formatElmaTime($tt4) . "</a></center></td>");
    echo("<td class=\"times\"><center><a href=\"https://up.elma.online/u/vomkh0ntxo/targetGOOD.zip\">" . formatElmaTime($tt5) . "</a></center></td>");
    echo("<td class=\"times\"><center><a href=\"https://up.elma.online/u/yvx3hil14t/targetOK.zip\">" . formatElmaTime($tt6) . "</a></center></td>");
    echo("<td class=\"times\"><center><a href=\"https://up.elma.online/u/yczpiyye9s/targetBEG.zip\">" . formatElmaTime($tt7) . "</a></center></td>");
  
  echo("</table></div>");
 include("tpo.php"); ?>
