<?php

  function ld($line) {
    return substr($line, 0, strlen($line)-1);
  }

  function hcheck($path, $state) {
    $h1 = hash_file("crc32b", $state);
    $lines = file($path);
    return $h1 == ld($lines[0]);
  }

  function htimes($path) {
    $lines = file($path);
    $times = array(array());
    for ($z = 0;$z < 54;$z++) {
      $lines[$z+1] = ld($lines[$z+1]);
      for ($x = 0;$x < 10;$x++) {
        $times[$z+1][$x+1] = substr($lines[$z+1], 0, strpos($lines[$z+1], "|"));
        $lines[$z+1] = substr($lines[$z+1], strpos($lines[$z+1], "|")+1);
      }
    }
    if (count($lines) > 55) $times[0][0] = $lines[55];
    return $times;
  }

  function stateTimes($user, $elmaname = "") {
    if ($user == "") return NULL;
    $times = array(array());

    if (file_exists("statefileszh/" . $user) && hcheck("statefileszh/" . $user, "statefilesz/" . $user . ".dat"))
      return htimes("statefileszh/" . $user);

    $byData = array();
    if (file_exists("statefilesz/" . $user . ".dat")) {
      $fh = fopen("statefilesz/" . $user . ".dat", "rb");
      fread($fh, 4);
      $ebp8 = 0x17;
      $ebp10 = 0x2636;
      for ($z = 0;$z < 54;$z++) {
        for ($x = 0;$x < 688;$x++) {
          $byData[$x] = ord(fread($fh, 1));
          $byData[$x] = $byData[$x]^($ebp8 & 0xFF);
          $ebp10 = toSignedWord($ebp10)+toSignedWord(($ebp8%0x0D3F)*0x0D3F);
          $ebp8 = toSignedWord($ebp10*0x1F+0x0D3F);
        }
        $t = $byData[0]+($byData[1] << 8)+($byData[2] << 16)+($byData[3] << 24);
        $x1 = 0;
        for ($x = 0;$x < $t;$x++) {
          $nick = "";
          for ($y = 0;$y < 15;$y++) {
            if ($byData[44+($x*15)+$y] == 0) break;
            $nick .= chr($byData[44+($x*15)+$y]);
          }
          if ($elmaname == "" || strtolower($elmaname) == strtolower($nick)) {
            $times[$z+1][$x1+1] = $byData[($x+1)*4]+($byData[($x+1)*4+1] << 8)+($byData[($x+1)*4+2] << 16)+($byData[($x+1)*4+3] << 24);
            $x1++;
          }
        }
      }
      fclose($fh);

      $fh = fopen("statefileszh/" . $user, "w");
      fwrite($fh, hash_file("crc32b", "statefilesz/" . $user . ".dat") . "\n");
      for ($z = 0;$z < 54;$z++) {
        for ($x = 0;$x < 10;$x++) {
          fwrite($fh, $times[$z+1][$x+1] . "|");
        }
        fwrite($fh, "\n");
      }
      fclose($fh);
      //chmod("statefileszh/" . $user , 0777);
      return $times;
    } else {
      return NULL;
    }
  }

  function toSignedWord($value) {
    $value = ($value & 0xFFFF);
    if ($value & 0x8000) $value = -((~$value & 0xFFFF) + 1);
    return $value;
  }

  function man($nikk, $showteam = true, $showflag = true) {
    global $users;
    if ($users[$nikk]["nick"] == "") return $nikk;
    $tx = "";
    if ($users[$nikk]["team"] != "" && $showteam == true) $tx = " [<a href=\"team.php?team=" . fix($users[$nikk]["team"]) . "\">" . fix($users[$nikk]["team"]) . "</a>]";
    $f = "";
    if ($showflag) {
     // if($users[$nikk]["nick"] == "AndrY")
     //   $f = flag2("soviet") . " ";
      //else
      $f = flag($users[$nikk]["country"]) . " ";
    }
    return $f . "<a href=\"player.php?player=" . $users[$nikk]["nick"] . "\">" . $users[$nikk]["nick"] . "</a>" . $tx;
  }

  function flag2($country) {
    return "<a href=\"natl.php?country=" . $country . "\"><img style=\"vertical-align: middle\" alt=\"" . $country . "\" src=\"flags/" . $country . ".png\"/></a>";
  }

  function flag($country) {
    return flag2($country);
    //return "<a href=\"natl.php?country=" . $country . "\"><img style=\"vertical-align: middle\" alt=\"" . $country . "\" src=\"flags/flag.png\"/></a>";
  }

  function tartime($intnum, $time, $target) {
    $tcolors = array("FF0000", "AA43DD", "FF66CC", "FF9C00", "FFF200", "00FF00", "0090FF", "F3F5CA");
    $targetnames = array("wr", "god", "leg", "wc", "pro", "good", "ok", "beg");
    $str1 = "";
    $str2 = "";
    $recname = "targetrecs/"  . $targetnames[$target] . str_pad($intnum, 2, "0", STR_PAD_LEFT) . ".rec";

    if(file_exists($recname)) {
      $lstr = "class=\"ntime\" ";
      if ($target > -1) $lstr = "class=\"ttime" . $target . "\" ";
      if ($target == 8) $lstr = "class=\"ntime\" ";
      if ($lead and $target == -1) $lstr = "class=\"ltime\" ";
      $str1 = "<a " . $lstr . "href=\"" . $recname . "\">";
      $str2 = "</a>";
      return $str1 . formatElmaTime($time, $emptyif0) . $str2;
    } else {
      $str1 = "";
      $str2 = "";
      if ($target > -1) {
        $str1 = "<span style=\"color: #" . $tcolors[$target] . "\">";
        $str2 = "</span>";
      }
      if ($target == 8) {
        $str1 = ""; $str2 = "";
      }
      return $str1 . formatElmaTime($time, $emptyif0) . $str2;
    }
  }

  function sttime($nick, $intnum, $time, $emptyif0 = false, $lead = false, $target = -1, $reclink = true) {
    $str1 = "";
    $str2 = "";
    $recname = "recs/" . $nick . "/" . str_pad($intnum, 2, "0", STR_PAD_LEFT) . $nick . formatRecTime($time) . ".rec";
    $tcolors = array("FF0000", "AA43DD", "FF66CC", "FF9C00", "FFF200", "00FF00", "0090FF", "F3F5CA");
    if (file_exists($recname) && $reclink) {
      $lstr = "class=\"ntime\" ";
      if ($target > -1) $lstr = "class=\"ttime" . $target . "\" ";
      if ($target == 8) $lstr = "class=\"ntime\" ";
      if ($lead and $target == -1) $lstr = "class=\"ltime\" ";
      $str1 = "<a " . $lstr . "href=\"" . $recname . "\">";
      $str2 = "</a>";
      return $str1 . formatElmaTime($time, $emptyif0) . $str2;
    } else {
      $str1 = "";
      $str2 = "";
      if ($target > -1) {
        $str1 = "<span style=\"color: #" . $tcolors[$target] . "\">";
        $str2 = "</span>";
      }
      if ($target == 8) {
        $str1 = ""; $str2 = "";
      }
      return $str1 . formatElmaTime($time, $emptyif0) . $str2;
    }
  }

  function coloredtttime($tt, $emptyif0 = false)
  {
    $tcolors = array("FF0000", "9932CC", "FF66CC", "FF9C00", "FFF200", "00FF00", "0090FF", "F3F5CA");

    $target = tttarget($tt);

    $str1 = "<span style=\"color: #" . $tcolors[$target] . "\">";
    $str2 = "</span>";

    return $str1 . formatElmaTime($tt, $emptyif0) . $str2;
  }

  function formatRecTime($i) {
    $min = 0;
    $sec = 0;
    while($i >= 6000) {
      $min += 1;
      $i -= 6000;
    }
    while($i >= 100) {
      $sec += 1;
      $i -= 100;
    }
    $mins = "";
    if ($min > 0) $mins = $min;
    return $mins . str_pad($sec, 2, "0", STR_PAD_LEFT) . str_pad($i, 2, "0", STR_PAD_LEFT);
  }

  function recInfo($filename) {
    if (!file_exists($filename)) return -1;
    $f = fopen($filename, "r");
    list(, $no) = unpack("V", fread($f, 4));
    for ($i = 0; $i < 12; $i++) fread($f, 1);
    $crc = bin2hex(fread($f, 4));
    $lev = fread($f, 12);
    for ($i = 0; $i < 27 * $no + 4; $i++) fread($f, 1);
    list(, $no) = unpack("V", fread($f, 4));
    for ($i = 0; $i < $no; $i++) {
      list(, $d) = unpack("d", fread($f, 8));
      list(, $j) = unpack("V", fread($f, 4));
      list(, $k) = unpack("V", fread($f, 4));
      if ($k == 0) $time = $d;
      if ($d != $time) $time = -1;
    }
    fclose($f);
    //$outtime = $time*62500/273+0.0000001; //php suks
    $outtime = $time*2.289377289377289681482352534658275544643402099609375*100.0;
    $cInfo["crc"] = $crc;
    $cInfo["lev"] = $lev;
    $cInfo["time"] = floor($outtime);
    return $cInfo;
  }

  function viewCounterAdd() {
		$fileIpAddresses = "statistiks/ips";
		$fileCounter = "statistiks/vis";
		//read amount of visitors
		if (filesize($fileCounter) > 0) {
			$fh = fopen($fileCounter, "r");
      $visitors = fread($fh, filesize($fileCounter));
      fclose($fh);
      //add to counter
      $visitors += 1;
      $fh = fopen($fileCounter, "w");
      fwrite($fh, $visitors);
      fclose($fh);
    }
    //get visitor ip
    $ip = getIp();
    if (filesize($fileIpAddresses) > 0) {
		  $fh = fopen($fileIpAddresses, "r");
      $ipAddresses = fread($fh, filesize($fileIpAddresses));
      fclose($fh);
      //if user hasn't visited before
      if (stripos($ipAddresses, $ip . ";") == 0) {
      //update ip list
      $fh = fopen($fileIpAddresses, "w");
      $ipAddresses = $ipAddresses . $ip . ";";
      fwrite($fh, $ipAddresses);
      fclose($fh);
    }
    }
	}

function addub($str, $player) {
  $gapStyle = (
    strpos($str, "bub.php") !== false &&
    strpos($str, "s=") !== false
  ) ? ' style="margin-top: 1px;"' : '';

  echo("URL: <input type=\"text\" size=\"19\" readonly=\"readonly\" value=\"https://stats.elma.online/" . $str . "\"/> " .
       "BBCODE: <input type=\"text\" size=\"19\" readonly=\"readonly\" value=\"[url=https://stats.elma.online/player.php?player=" . $player . "][img]https://stats.elma.online/" . $str . "[/img][/url]\"/> " .
       "<div class=\"ubwrap\"" . $gapStyle . "><img src=\"" . $str . "\" alt=\"TT\"/></div>" .
       "<div style=\"clear: both; height: 0; line-height: 0;\"></div>");
}

  function fileCount($path, $ext = "") {
    $files = 0;
    $dir = opendir($path);
    if (!$dir) return 0;
    while (($file = readdir($dir)) !== false) {
      if ($file[0] == '.') continue;
      if (is_dir($path . $file)){
        $files += fileCount($path . $file . DIRECTORY_SEPARATOR);
      }
      else {
        if ($ext != "") {
          if (endsWith($file, "." . $ext)) $files++;
        } else {
          $files++;
        }
      }
    }
    closedir($dir);
    return $files;
  }

  function writeLog($str) {
    $fh = fopen("statistiks/log05", "a");
    fwrite($fh, "[" . date("d/m/Y") . "] [" . date("H:i:s") . "] " . $str . "\n");
    fclose($fh);
  }

  function illegalChars($str, $print = false) {
    $chars = array("<", ">", "?", "&", ",", "\\", "/", "=", " ");
    if ($print) {
      $s = "";
      foreach ($chars as $char) $s .= $char;
      return $s . "and space";
    } else {
      foreach ($chars as $char) if (strpos($str, $char) !== false) return true;
      return false;
    }
  }

  function timeSince($ptime) {
    $etime = time() - $ptime;
    if ($etime < 1) return "0 seconds";
    $a = array(12 * 30 * 24 * 60 * 60  => "year",
               30 * 24 * 60 * 60       => "month",
               24 * 60 * 60            =>  "day",
               60 * 60                 => "hour",
               60                      => "minute",
               1                       => "second");
    foreach ($a as $secs => $str) {
        $d = $etime/$secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . " " . $str . ($r > 1 ? "s" : "");
        }
    }
  }

function addUbCounter() {
    $fileUbCounter = "statistiks/ubs";
    $ubs = 0;

    if (file_exists($fileUbCounter) && filesize($fileUbCounter) > 0) {
        $fh = fopen($fileUbCounter, "r");
        $ubs = (int)fread($fh, filesize($fileUbCounter));
        fclose($fh);
    }

    $ubs++;
    file_put_contents($fileUbCounter, (string)$ubs);
}

  function countries() {
    return array('AF'=>'Afghanistan', 'AL'=>'Albania', 'DZ'=>'Algeria', 'AS'=>'American Samoa', 'AD'=>'Andorra', 'AO'=>'Angola', 'AI'=>'Anguilla', 'AQ'=>'Antarctica', 'AG'=>'Antigua And Barbuda', 'AR'=>'Argentina', 'AM'=>'Armenia', 'AW'=>'Aruba', 'AU'=>'Australia', 'AT'=>'Austria', 'AZ'=>'Azerbaijan', 'BS'=>'Bahamas', 'BH'=>'Bahrain', 'BD'=>'Bangladesh', 'BB'=>'Barbados', 'BY'=>'Belarus', 'BE'=>'Belgium', 'BZ'=>'Belize', 'BJ'=>'Benin', 'BM'=>'Bermuda', 'BT'=>'Bhutan', 'BO'=>'Bolivia', 'BA'=>'Bosnia And Herzegovina', 'BW'=>'Botswana', 'BV'=>'Bouvet Island', 'BR'=>'Brazil', 'IO'=>'British Indian Ocean Territory', 'BN'=>'Brunei', 'BG'=>'Bulgaria', 'BF'=>'Burkina Faso', 'BI'=>'Burundi', 'KH'=>'Cambodia', 'CM'=>'Cameroon', 'CA'=>'Canada', 'CV'=>'Cape Verde', 'KY'=>'Cayman Islands', 'CF'=>'Central African Republic', 'TD'=>'Chad', 'CL'=>'Chile', 'CN'=>'China', 'CX'=>'Christmas Island', 'CC'=>'Cocos (Keeling) Islands', 'CO'=>'Columbia', 'CS'=>'Serbia', 'KM'=>'Comoros', 'CG'=>'Congo', 'CK'=>'Cook Islands', 'CR'=>'Costa Rica', 'CI'=>'Cote D\'Ivorie (Ivory Coast)', 'HR'=>'Croatia (Hrvatska)', 'CU'=>'Cuba', 'CY'=>'Cyprus', 'CZ'=>'Czech Republic', 'CD'=>'Democratic Republic Of Congo (Zaire)', 'DK'=>'Denmark', 'DJ'=>'Djibouti', 'DM'=>'Dominica', 'DO'=>'Dominican Republic', 'TP'=>'East Timor', 'EC'=>'Ecuador', 'EG'=>'Egypt', 'SV'=>'El Salvador', 'GQ'=>'Equatorial Guinea', 'ER'=>'Eritrea', 'EE'=>'Estonia', 'ET'=>'Ethiopia', 'FK'=>'Falkland Islands (Malvinas)', 'FO'=>'Faroe Islands', 'FJ'=>'Fiji', 'FI'=>'Finland', 'FR'=>'France', 'FX'=>'France, Metropolitan', 'GF'=>'French Guinea', 'PF'=>'French Polynesia', 'TF'=>'French Southern Territories', 'GA'=>'Gabon', 'GB' =>'Great Britain', 'GM'=>'Gambia', 'GE'=>'Georgia', 'DE'=>'Germany', 'GH'=>'Ghana', 'GI'=>'Gibraltar', 'GR'=>'Greece', 'GL'=>'Greenland', 'GD'=>'Grenada', 'GP'=>'Guadeloupe', 'GU'=>'Guam', 'GT'=>'Guatemala', 'GN'=>'Guinea', 'GW'=>'Guinea-Bissau', 'GY'=>'Guyana', 'HT'=>'Haiti', 'HM'=>'Heard And McDonald Islands', 'HN'=>'Honduras', 'HK'=>'Hong Kong', 'HU'=>'Hungary', 'IS'=>'Iceland', 'IN'=>'India', 'ID'=>'Indonesia', 'IR'=>'Iran', 'IQ'=>'Iraq', 'IE'=>'Ireland', 'IL'=>'Israel', 'IT'=>'Italy', 'JM'=>'Jamaica', 'JP'=>'Japan', 'JO'=>'Jordan', 'KZ'=>'Kazakhstan', 'KE'=>'Kenya', 'KI'=>'Kiribati', 'KW'=>'Kuwait', 'KG'=>'Kyrgyzstan', 'LA'=>'Laos', 'LV'=>'Latvia', 'LB'=>'Lebanon', 'LS'=>'Lesotho', 'LR'=>'Liberia', 'LY'=>'Libya', 'LI'=>'Liechtenstein', 'LT'=>'Lithuania', 'LU'=>'Luxembourg', 'MO'=>'Macau', 'MK'=>'Macedonia', 'MG'=>'Madagascar', 'MW'=>'Malawi', 'MY'=>'Malaysia', 'MV'=>'Maldives', 'ML'=>'Mali', 'MT'=>'Malta', 'MH'=>'Marshall Islands', 'MQ'=>'Martinique', 'MR'=>'Mauritania', 'MU'=>'Mauritius', 'YT'=>'Mayotte', 'MX'=>'Mexico', 'FM'=>'Micronesia', 'MD'=>'Moldova', 'MC'=>'Monaco', 'MN'=>'Mongolia', 'ME'=>'Montenegro', 'MS'=>'Montserrat', 'MA'=>'Morocco', 'MZ'=>'Mozambique', 'MM'=>'Myanmar (Burma)', 'NA'=>'Namibia', 'NR'=>'Nauru', 'NP'=>'Nepal', 'NL'=>'Netherlands', 'AN'=>'Netherlands Antilles', 'NC'=>'New Caledonia', 'NZ'=>'New Zealand', 'NI'=>'Nicaragua', 'NE'=>'Niger', 'NG'=>'Nigeria', 'NU'=>'Niue', 'NF'=>'Norfolk Island', 'KP'=>'North Korea', 'MP'=>'Northern Mariana Islands', 'NO'=>'Norway', 'OM'=>'Oman', 'PK'=>'Pakistan', 'PW'=>'Palau', 'PA'=>'Panama', 'PG'=>'Papua New Guinea', 'PY'=>'Paraguay', 'PE'=>'Peru', 'PH'=>'Philippines', 'PN'=>'Pitcairn', 'PL'=>'Poland', 'PT'=>'Portugal', 'PR'=>'Puerto Rico', 'QA'=>'Qatar', 'RE'=>'Reunion', 'RO'=>'Romania', 'RU'=>'Russia', 'RW'=>'Rwanda', 'SH'=>'Saint Helena', 'KN'=>'Saint Kitts And Nevis', 'LC'=>'Saint Lucia', 'PM'=>'Saint Pierre And Miquelon', 'VC'=>'Saint Vincent And The Grenadines', 'SM'=>'San Marino', 'ST'=>'Sao Tome And Principe', 'SA'=>'Saudi Arabia', 'SN'=>'Senegal', 'SC'=>'Seychelles', 'SL'=>'Sierra Leone', 'SG'=>'Singapore', 'SK'=>'Slovak Republic', 'SI'=>'Slovenia', 'SB'=>'Solomon Islands', 'SO'=>'Somalia', 'ZA'=>'South Africa', 'GS'=>'South Georgia And South Sandwich Islands', 'KR'=>'South Korea', 'ES'=>'Spain', 'LK'=>'Sri Lanka', 'SD'=>'Sudan', 'SR'=>'Suriname', 'SJ'=>'Svalbard And Jan Mayen', 'SZ'=>'Swaziland', 'SE'=>'Sweden', 'CH'=>'Switzerland', 'SY'=>'Syria', 'TW'=>'Taiwan', 'TJ'=>'Tajikistan', 'TZ'=>'Tanzania', 'TH'=>'Thailand', 'TG'=>'Togo', 'TK'=>'Tokelau', 'TO'=>'Tonga', 'TT'=>'Trinidad And Tobago', 'TN'=>'Tunisia', 'TR'=>'Turkey', 'TM'=>'Turkmenistan', 'TC'=>'Turks And Caicos Islands', 'TV'=>'Tuvalu', 'UG'=>'Uganda', 'UA'=>'Ukraine', 'AE'=>'United Arab Emirates', 'UK'=>'United Kingdom', 'US'=>'United States', 'UM'=>'United States Minor Outlying Islands', 'UY'=>'Uruguay', 'UZ'=>'Uzbekistan', 'VU'=>'Vanuatu', 'VA'=>'Vatican City (Holy See)', 'VE'=>'Venezuela', 'VN'=>'Vietnam', 'VG'=>'Virgin Islands (British)', 'VI'=>'Virgin Islands (US)', 'WF'=>'Wallis And Futuna Islands', 'EH'=>'Western Sahara', 'WS'=>'Western Samoa', 'YE'=>'Yemen', 'YU'=>'Yugoslavia', 'ZM'=>'Zambia', 'ZW'=>'Zimbabwe');
  }

  function fix($str) {
    $str = str_replace("&", "&amp;", $str);
    $str = str_replace(">", "&gt;", str_replace("<", "&lt;", $str));
    return $str;
  }

  function loadUser($file) {
    $lines = file("usersz/" . $file);
    $user = array();
    for ($x = 0;$x < count($lines);$x++) $lines[$x] = str_replace("\r\n", "\n", $lines[$x]);
    $i = 0;
    foreach ($lines as $line_num => $line) {
      $i++;
      switch ($i) {
        case 1: $nick = ld($line); $user["nick"] = $nick; break;
        case 2: $user["elmaname"] = ld($line); break;
        case 3: $user["country"] = ld($line); break;
        case 4: $user["team"] = ld($line); break;
        case 5: $user["pwd"] = ld($line); break;
        case 6: $user["registered"] = ld($line); break;
        case 7: $user["email"] = ld($line); break;
        case 8: $user["theme"] = ld($line); break;
        case 9: $user["timezone"] = ld($line); break;
        case 10: $user["timeformat"] = ld($line); break;
      }
    }
    return $user;
  }

  function target($time, $int) {
    global $targets;
    if (!$time) return 8;
    //if ($time <= $wrs[$int]) return 0;
    for ($x = 0;$x < 8;$x++) {
      if ($time <= $targets[$int][$x]) return $x;
    }
    return 8;
  }

  function tttarget($time) {
    // min * 60 + sec . hundra
    // 0 = pad cuz wr tt
    // 35:08:64 = godlike
    // 36:39:28 = legendary
    // 38:09:32 = world class
    // 39:48:08 = professional
    // 43:16:76 = good
    // 49:27:28 = oke
    // 64:04:10 = beginer

    $targets = array(0, 210864, 218928, 228932, 238808, 259676, 296728, 384410, 384411);

    for ($x = 0;$x < 8;$x++) {
      if ($time <= $targets[$x]) return $x;
    }

    return 8;
  }

  function fetchtargetinfo() {
    //Fetch the targets, declare variables and counters
    $AllTargets = loadTargets();
    $IntInfo = array();
    $utimes = array();
	 
    // Loop for each internal level
    for ($x = 0;$x < 54;$x++) {
      $c = 0; $t0 = 0; $t1 = 0; $t2 = 0; $t3 = 0; $t4 = 0; $t5 = 0; $t6 = 0; $t7 = 0; $t8 = 0;
		    
      //$users is declared in top.php. This component fetches all users
      foreach ($users as $cItem) {
	$utimes[] = stateTimes($cItem["nick"], $cItem["elmaname"]);
	$c++;
	}
		  
    // Loop each user and test their time to discover which target they achieved
    for ($y = 0;$y < $c;$y++) {
      //This statement will determine which target is achieved and then increment a counter for each target they already met.
      $achieved = target($utimes[$y][$x+1][1], $x+1);
      if($achieved <= 8) $t8++;
      if($achieved <= 7) $t7++;
      if($achieved <= 6) $t6++;
      if($achieved <= 5) $t5++;
      if($achieved <= 4) $t4++;
      if($achieved <= 3) $t3++;
      if($achieved <= 2) $t2++;
      if($achieved <= 1) $t1++;
      if($achieved == 0) $t0++;
    }  
    //Write all info to array for front-end
    $IntInfo[$x+1]["t0"] = $t0; 
    $IntInfo[$x+1]["t1"] = $t1; 
    $IntInfo[$x+1]["t2"] = $t2; 
    $IntInfo[$x+1]["t3"] = $t3; 
    $IntInfo[$x+1]["t4"] = $t4; 
    $IntInfo[$x+1]["t5"] = $t5; 
    $IntInfo[$x+1]["t6"] = $t6; 
    $IntInfo[$x+1]["t7"] = $t7;
    $IntInfo[$x+1]["t8"] = $t8;
    $IntInfo[$x+1]["0"] = $AllTargets[$x+1][0]; 
    $IntInfo[$x+1]["1"] = $AllTargets[$x+1][1]; 
    $IntInfo[$x+1]["2"] = $AllTargets[$x+1][2]; 
    $IntInfo[$x+1]["3"] = $AllTargets[$x+1][3]; 
    $IntInfo[$x+1]["4"] = $AllTargets[$x+1][4]; 
    $IntInfo[$x+1]["5"] = $AllTargets[$x+1][5]; 
    $IntInfo[$x+1]["6"] = $AllTargets[$x+1][6]; 
    $IntInfo[$x+1]["7"] = $AllTargets[$x+1][7];
  }
  return $IntInfo;
  }

  function cmp($a, $b) {
    if ($a["time"] == $b["time"]) return 0;
    return ($a["time"] < $b["time"]) ? -1 : 1;
  }

  function cmpavg($a, $b) {
    if ($a["avgtime"] == $b["avgtime"]) return 0;
    return ($a["avgtime"] < $b["avgtime"]) ? -1 : 1;
  }

  function cmpdiff($a, $b) {
    if ($a["timediff"] == $b["timediff"]) return 0;
    return ($a["timediff"] < $b["timediff"]) ? -1 : 1;
  }

  function cmpa($a, $b) {
    if (is_array($a) && is_array($b) && isset($a["nick"], $b["nick"])) {
      $s = strcmp(strtolower($a["nick"]), strtolower($b["nick"]));
    } else {
      $s = 0;
    }
    if ($s == 0 && strlen($a) > 1 && strlen($b) > 1) return cmpa(substr($a, 1), substr($b, 1));
    return ($s == 0 ? 0 : ($s < 0 ? -1 : 1));
  }

  function getLev($utimes) {
    global $targets;
    $p = 0;
    $points = array(2000, 1000, 750, 500, 250, 100, 50);
    for ($x = 0;$x < 54;$x++) {
      $time = $utimes[$x+1][1];
      if ($time == 0) $time = 60000;
      //if ($time <= $wrs[$x+1]) {
      //  $p += $points[0];
      //} else {
      for ($y = 0;$y < 7;$y++) {
        if ($time <= $targets[$x+1][$y]) {
          $p += $points[$y];
          break;
        }
      }
      //}
    }
    $pr = array();
    $pr["lev"] = floor($p/1000);
    $pr["exp"] = $p;
    return $pr;
  }

  function mtime() {
    list($usec, $sec) = explode(" ", microtime());
    return (float)$usec+(float)$sec;
  }

  function loadTargets() {
    $targets = array(array());
    $lines = file("targets.txt");
    $x = 0;
    foreach ($lines as $line_num => $line) {
      for ($y = 0;$y < 7;$y++) {
        $targets[$x+1][$y+1] = divTime(substr($line, 0, 8));
        $line = substr($line, 8);
        if ($y < 6) $line = substr($line, strpos($line, "0"));
      }
      $x++;
    }

    //Get wrs
    //$s = file_get_contents("http://www.moposite.com/records_elma_wrs.php");
    $s = file_get_contents("./db/wrtable");


    $s = substr($s, strpos($s, "<td class=\"wrth\" align='center' width=\"130\">Name</td>"));
    for ($x = 0;$x < 54;$x++) {
      //$s = substr($s, strpos($s, ">Thor"));
      $s = substr($s, strpos($s, " align='left'>")+strlen(" align='left'>"));
      $num = substr($s, 0, strpos($s, "."));
      $s = substr($s, strpos($s, " align='right'>")+strlen(" align='right'>"));
      $t = substr($s, 0, strpos($s, "</td>"));
      $s = substr($s, strpos($s, " align='left'>")+strlen(" align='left'>"));
      if (strpos($t, ":") > 0) {
        $min = substr($t, 0, strpos($t, ":"));
        $t = substr($t, strpos($t, ":")+1);
        $sec = substr($t, 0, strpos($t, ","));
        $msc = substr($t, strpos($t, ",")+1);
      } else {
        $min = 0;
        $sec = substr($t, 0, strpos($t, ","));
        $msc = substr($t, strpos($t, ",")+1);
      }
      $time = ($min*100*60)+($sec*100)+$msc;
      //$wrs[$num] = $time;
      $targets[$num][0] = $time;
    }
    return $targets;
  }

  function ttime($tajm) {
    global $timezone, $users;
    if (isset($_SESSION["nick"])) {
      return (int)$tajm+($timezone*3600)+($users[$_SESSION["nick"]]["timezone"]*3600);
    } else {
      return (int)$tajm+($timezone*3600);
    }
  }

  function formatElmaTime($i, $emptyif0 = false) {
    global $users;
    if ($i == 0 && $emptyif0) {
      return "";
    } else {
      $i = round((int)$i);
      $hrs = floor($i/360000);
      $min = floor($i/6000);
      $sec = floor($i/100);
      $i -= ($sec*100);
      $sec -= ($min*60);
      $min -= ($hrs*60);
      $hstr = "";
      if ($hrs > 0) $hstr = $hrs . ":";

      $r = "m:s:i";
      if (isset($_SESSION["nick"])) $r = $users[$_SESSION["nick"]]["timeformat"];
      $r = $hstr . str_replace("m", str_pad($min, 2, "0", STR_PAD_LEFT), $r);
      $r = str_replace("M", $min, $r);
      $r = str_replace("s", str_pad($sec, 2, "0", STR_PAD_LEFT), $r);
      $r = str_replace("S", $sec, $r);
      $r = str_replace("i", str_pad($i, 2, "0", STR_PAD_LEFT), $r);
      $r = str_replace("I", $i, $r);
      return fix($r);
      /*return $hstr . str_pad($min, 2, "0", STR_PAD_LEFT) . ":" .
             str_pad($sec, 2, "0", STR_PAD_LEFT) . ":" .
             str_pad($i, 2, "0", STR_PAD_LEFT);*/
    }
  }

  function divTime($time) {
    $i = 0;
    $i += substr($time, 0, 2)*6000;
    $i += substr($time, 3, 2)*100;
    $i += substr($time, 6, 2);
    return $i;
  }

  function getIp() {
		return $_SERVER["REMOTE_ADDR"];
	}

	function xrmdir($dirname) {
    if (is_dir($dirname)) $dir_handle = opendir($dirname);
    if (!$dir_handle) return false;
    while ($file = readdir($dir_handle)) {
      if ($file != "." && $file != "..") {
         if (!is_dir($dirname . "/" . $file)) unlink($dirname . "/" . $file);
         else xrmdir($dirname . '/' . $file);
      }
    }
    closedir($dir_handle);
    rmdir($dirname);
    return true;
  }

  function endsWith($str, $sub, $case = true) {
    if (!$case) {
      $str = strtolower($str);
      $sub = strtolower($sub);
    }
    return (substr($str, strlen($str) - strlen($sub)) == $sub);
  }

  function postr($num) {
    switch ($num) {
      case 1:
        $str = "st";break;
      case 2:
        $str = "nd";break;
      case 3:
        $str = "rd";break;
      default:
        $str = "th";break;
    }
    return $num . $str;
  }

  function internal($num, $number = false, $fullname = true, $spacing = true, $dot = true) {
    global $intnames;
    $space = ($spacing && $number ? ($num > 9 ? "" : "&nbsp;") : "");
    return "<a href=\"int.php?int=" . $num . "\">" . ($number ? $num . ($dot ? "." : "") . " " : "") . ($fullname ? $space . $intnames[$num] : "") . "</a>";
  }

  function error($str, $green = false) {
    echo("<span style=\"color: #" . ($green ? "00CC00" : "FF0000") . "\">" . $str . "</span>");
  }
?>
