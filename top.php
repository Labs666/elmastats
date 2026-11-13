<?php
  //header("Refresh: 0; url=http://oz.kiev.ua/nw/");
  //die();

  ini_set('session.gc_maxlifetime', 86400);
  ini_set('session.cookie_lifetime', 86400);

  session_save_path(__DIR__ . '/sessions');

  session_start();
  error_reporting(E_ERROR);

  if ($_GET["logout"] == 1) {
    session_unset();
    session_destroy();
  }

  //functions
  //include("../func.php");
  include("func.php");

  $sxtime = mtime();
  $timezone = 5;

  //updates in acc.php
  $acc_status = "";
  if (endsWith($_SERVER["PHP_SELF"], "acc.php")) {
    //Check if userNAME is updated in acc.php
    /*if ($_POST["nick"] != $_SESSION["nick"] && isset($_SESSION["nick"]) && $_POST["nick"] != "") {
      rename("usersz/" . $_SESSION["nick"], "usersz/" . $_POST["nick"]);
      //$users[$_POST["nick"]] = $users[$_SESSION["nick"]];
      $prnick = $_SESSION["nick"];
      $_SESSION["nick"] = $_POST["nick"];
    }*/
  }

  //load users
  $users = array(array());
  $handle = opendir("usersz/");
  while (false !== ($file = readdir($handle))) {
    if ($file != "." && $file != "..") $users[$file] = loadUser($file);
  }
  closedir($handle);
  uksort($users, "cmpa");


  //update user if changed in acc.php
  if (endsWith($_SERVER["PHP_SELF"], "acc.php")) {
    if (sizeof($_POST) > 0) {
      $acc_status .= "<br/>";
      $error = false;
      //Skip nick testing
      /*if ($users[$_POST["nick"]]["nick"] != "" && $users[$_POST["nick"]]["nick"] != $_SESSION["nick"]) {
        $acc_status .= "<span style=\"color: #FF0000\">Another user with this nick already exists</span><br/>";
        $error = true;
      }
      if (strlen($_POST["nick"]) < 2) {
        $acc_status .= "<span style=\"color: #FF0000\">Nick must be at least 2 chars</span><br/>";
        $error = true;
      } elseif (strlen($_POST["nick"]) > 10) {
        $acc_status .= "<span style=\"color: #FF0000\">Nick cant be longer than 10 chars (faks up tabels)</span><br/>";
        $error = true;
      }
      if (illegalChars($_POST["nick"])) {
        $acc_status .= "<span style=\"color: #FF0000\">Nick contains illegal chars (" . illegalChars("", true) . ")</span><br/>";
        $error = true;
      }*/
      if ($_POST["pwd"] != $_POST["pwd2"] && $_POST["pwd"] != "") {
        $acc_status .= "<span style=\"color: #FF0000\">Passwords don't match</span><br/>";
        $error = true;
      } elseif(strlen($_POST["pwd"]) < 3 && $_POST["pwd"] != "") {
        $acc_status .= "<span style=\"color: #FF0000\">Password must be at least 3 chars</span><br/>";
        $error = true;
      } elseif(strlen($_POST["pwd"]) > 30 && $_POST["pwd"] != "") {
        $acc_status .= "<span style=\"color: #FF0000\">Password can't be longer than 30 chars</span><br/>";
        $error = true;
      }
      if (strlen($_POST["team"]) > 12 && $_POST["team"] != "") {
        $acc_status .= "<span style=\"color: #FF0000\">Team name can't be longer than 12 chars</span><br/>";
        $error = true;
      }
      if ($error == false) {
        $fh = fopen("usersz/" . $_SESSION["nick"], "w");
        //fwrite($fh, $_POST["nick"]. "\n");
        fwrite($fh, $_SESSION["nick"] . "\n");
        fwrite($fh, $_POST["elmaname"] . "\n");
        if ($_POST["country"] != "NULL") {
          fwrite($fh, strtolower($_POST["country"]) . "\n");
        } else {
          fwrite($fh, strtolower($users[$_SESSION["nick"]]["country"]) . "\n");
        }
        fwrite($fh, $_POST["team"] . "\n");
        if ($_POST["pwd"] != "") {
          fwrite($fh, strtolower(md5(md5($_POST["pwd"]))) . "\n");
        } else {
          fwrite($fh, strtolower($users[$_SESSION["nick"]]["pwd"]) . "\n");
        }
        fwrite($fh, $users[$_SESSION["nick"]]["registered"] . "\n");
        fwrite($fh, $_POST["email"] . "\n");
        fwrite($fh, $_POST["theme"] . "\n");
        fwrite($fh, $_POST["timezone"] . "\n");
        fwrite($fh, $_POST["timeformat"] . "\n");
        fclose($fh);
        $users[$_SESSION["nick"]] = loadUser($_SESSION["nick"]);
        $acc_status .= "<span style=\"color: #00CC00\">User updated!</span><br/>";
      }
    }
  }

  //update user if changed in adm.php
  $admins = array("Labs", "Kopaka", "Arzenik");
  if (endsWith($_SERVER["PHP_SELF"], "adm.php")
    && (!isset($_SESSION["nick"]) || !in_array($_SESSION["nick"], $admins, true))) {
    http_response_code(403);
    exit("Access denied");
}
  if (endsWith($_SERVER["PHP_SELF"], "adm.php")) {
if (isset($_POST["OGnick"]) && in_array($_SESSION["nick"], $admins, true)) {
      $adm_acc_status .= "<br/>";
      $error = false;
      if ($_POST["pwd"] != $_POST["pwd2"] && $_POST["pwd"] != "") {
        $adm_acc_status .= "<span style=\"color: #FF0000\">Passwords don't match</span><br/>";
        $error = true;
      } elseif(strlen($_POST["pwd"]) < 3 && $_POST["pwd"] != "") {
        $adm_acc_status .= "<span style=\"color: #FF0000\">Password must be at least 3 chars</span><br/>";
        $error = true;
      } elseif(strlen($_POST["pwd"]) > 30 && $_POST["pwd"] != "") {
        $adm_acc_status .= "<span style=\"color: #FF0000\">Password can't be longer than 30 chars</span><br/>";
        $error = true;
      }
      if (strlen($_POST["team"]) > 12 && $_POST["team"] != "") {
        $adm_acc_status .= "<span style=\"color: #FF0000\">Team name can't be longer than 12 chars</span><br/>";
        $error = true;
      }
      if ($error == false) {
        $fh = fopen("usersz/" . $_POST["OGnick"], "w");
        //fwrite($fh, $_POST["nick"]. "\n");
        fwrite($fh, $_POST["OGnick"] . "\n");
        fwrite($fh, $_POST["elmaname"] . "\n");
        if ($_POST["country"] != "NULL") {
          fwrite($fh, strtolower($_POST["country"]) . "\n");
        } else {
          fwrite($fh, strtolower($users[$_POST["OGnick"]]["country"]) . "\n");
        }
        fwrite($fh, $_POST["team"] . "\n");
        if ($_POST["pwd"] != "") {
          fwrite($fh, strtolower(md5(md5($_POST["pwd"]))) . "\n");
        } else {
          fwrite($fh, strtolower($users[$_POST["OGnick"]]["pwd"]) . "\n");
        }
        fwrite($fh, $users[$_POST["OGnick"]]["registered"] . "\n");
        fwrite($fh, $_POST["email"] . "\n");
        fwrite($fh, $_POST["theme"] . "\n");
        fwrite($fh, $_POST["timezone"] . "\n");
        fwrite($fh, $_POST["timeformat"] . "\n");
        fclose($fh);
        $users[$_POST["OGnick"]] = loadUser($_POST["OGnick"]);
        $adm_acc_status .= "<span style=\"color: #00CC00\">User updated!</span><br/>";
      }
    }
  }

  //Load intnames
  $intnames = array();
  $lines = file("intnames.txt");
  $i = 0;
  foreach ($lines as $line_num => $line) {
    $i++;
    $line = str_replace("\r\n", "\n", $line);
    $intnames[$i] = substr($line, 0, strlen($line)-1);
  }

  //User stuff
  if (strlen($_POST["nick"]) > 0 && !isset($_SESSION["nick"])) {
    $status = "";
    foreach ($users as $cItem) {
      if ($_POST["nick"] == $cItem["nick"]) {
        $exists = true;
        if (md5(md5($_POST["pwd"])) == $cItem["pwd"]) {
          //Logged in
          $_SESSION["nick"] = $_POST["nick"];
          if ($_POST["remembar"] == "ye")
            setcookie(session_name(),$_COOKIE[session_name()],time()+60*60*24*365,$params["path"],$params["domain"],$params["secure"],$params["httponly"]);

        } else {
          //Wrong pwd
          $status = "Wrong password!";
        }
      }
    }
    if (!$exists) {
      $status = "User doesn't exist!";
    }
  }

  //Log :D
  viewCounterAdd();
  writeLog(getIp() . " -> " . $_SERVER["PHP_SELF"] . (isset($_SESSION["nick"]) ? " (user: " . $_SESSION["nick"] . ")" : ""));


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>elmastats</title>
<link href="css/<?php

  if (isset($_SESSION["nick"])) {
    echo($users[$_SESSION["nick"]]["theme"] . ".css");
  } else {
    echo("Default.css");
  }
  //echo("refreshed.css");
  //<a href="https://www.youtube.com/watch?v=jl4d2IVBXPU">restore old theme tutorial</a>
  echo("?" . time());
?>" rel="stylesheet" type="text/css"/>
<link href="css/hi.css?<?php echo(time()); ?>" rel="stylesheet" type="text/css"/>
<link rel="shortcut icon" href="favicon.ico"/>
<script type="text/javascript" src="tabber.js"></script>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
</head>
<body>
<div id="main">
<a class="header" href="index.php"></a>
<div id="ads">
  <?php //add mopolauta link elmastatssdfsdf ?>
  <a href="https://elma.online/cup/WC9/" target="_blank">World Cup 9</a> /
  <a href="http://www.moposite.com/records_elma_wrs.php" target="_blank">Latest wr table</a> /
  <a href="http://elma.online" target="_blank">Elma Online</a> /
  <a href="http://mopolauta.moposite.com/" target="_blank">mopolauta</a> /
  <a href="https://multiwr.elma.online/" target="_blank">Internal multi wr</a>
</div>
<?php
  // sshoyer.net removal
  $host = $_SERVER['HTTP_HOST'];
  $now = new DateTime();
  $cutoff = new DateTime('2025-11-28');
  $isAllowedDomain = (
    $host === 'sshoyer.net' ||
    preg_match('/\.sshoyer\.net$/', $host) ||
    $host === 'localhost:8080'
  );
  if ($isAllowedDomain && $now < $cutoff) {
    echo("<div id='domain'>sshoyer.net domain will be removed late November 2025, please use <a href='https://stats.elma.online'>stats.elma.online</a></div>");
  }
?>
<div id="left">
<?php if (isset($_SESSION["nick"])) { ?>
  <div class="menuheader">
    <?php
      echo("<div id=\"ahead\">" . man($_SESSION["nick"]));
      $utimes = stateTimes($_SESSION["nick"], $users[$_SESSION["nick"]]["elmaname"]);
if ($utimes != NULL) {
  $tt = 0;
  for ($x = 0; $x < 54; $x++) {
    $time = (int)($utimes[$x + 1][1] ?? 0);
    $tt += ($time > 0) ? min($time, 60000) : 60000;
  }

  $ttRank = 1;
  foreach ($users as $rankUser) {
    $rankNick = $rankUser["nick"] ?? "";

    if ($rankNick === "" || $rankNick === $_SESSION["nick"]
        || $rankNick === "!.gitignore" || $rankNick === "*") {
      continue;
    }

    $rankTimes = stateTimes($rankNick, $rankUser["elmaname"] ?? "");
    if ($rankTimes === NULL) {
      continue;
    }

    $rankTotal = 0;
    for ($x = 0; $x < 54; $x++) {
      $time = (int)($rankTimes[$x + 1][1] ?? 0);
      $rankTotal += ($time > 0) ? min($time, 60000) : 60000;
    }

    if ($rankTotal < 60000 * 54 && $rankTotal < $tt) {
      $ttRank++;
    }
  }

  echo("<br/>TT: " . coloredtttime($tt) .
       ($tt < 60000 * 54 ? " (" . $ttRank . ".)" : ""));
}
      echo("</div>");
    ?>
  </div>
  <a href="up.php">upload stats</a><br/>
  <a href="uprec.php">upload recs</a><br/>
  <a href="acc.php">acc settings</a><br/>
  <a href="up.php?logout=1">log out</a><br/>
  <?php
    if (isset($_SESSION["nick"]) && in_array($_SESSION["nick"], $admins, true)) {
      echo('<a href="adm.php?nick=' . urlencode($_SESSION["nick"]) . '">admin</a><br/>');
}
?>
<?php } else { ?>
  <div class="menuheader"><b>user menu</b></div>
  <a href="up.php">log in</a><br/>
  <a href="register.php">register</a><br/>
<?php } ?>
<br/>
<div class="menuheader"><b>main menu</b></div>
<a href="index.php">main page</a><br/>
<a href="top10.php">top10s</a><br/>
<a href="tts.php">totaltimes</a><br/>
<a href="natl.php">national stats</a><br/>
<a href="recs.php">top10 recs</a><br/>
<a href="compare.php">compare</a><br/>
<a href="targets.php">target times</a><br/>
<a href="players.php">players</a><br/>
<a href="teams.php">teams</a><br/>
<br/>
<div class="menuheader"><b>internals</b></div>
<table><tr>
<?php
  for ($x = 0;$x < 54;$x++) {
    echo("<td width=\"20px\">" . internal($x+1, true, false, false, false) . "</td>");
    if (($x+1)%6 == 0 && $x < 53) echo("</tr><tr>");
  }
?>
</tr></table>
<br/>
<div class="menuheader"><b>other stuff</b><br/></div>
<a href="suggestions.php">suggestions</a><br/>
<a href="site.php">site stats</a><br/>
<a href="about.php">about</a><br/>
</div>
<div id="mid">
