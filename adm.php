<?php ob_start(); include("top.php"); ?>
<?php
    if (isset($_SESSION["nick"]) && in_array($_SESSION["nick"], $admins, true)) {
    $admnick = $_GET["nick"] ?? "";
    $admnuser = $users[$admnick] ?? null;
    if (isset($_GET["recover_state"]) && $_GET["recover_state"] === "1") {
    if (!isset($admnuser)) {
      ob_clean();
      http_response_code(404);
      exit("User not found.");
    }

    $statePath = "statefilesz/" . $admnick . ".dat";

    if (!is_file($statePath)) {
      ob_clean();
      http_response_code(404);
      exit("State.dat not found.");
    }

      ob_clean();
      header("Content-Type: application/octet-stream");
      header("Content-Disposition: attachment; filename=\"" . basename($admnick) . ".dat\"");
      header("Content-Length: " . filesize($statePath));
      readfile($statePath);
    exit;
}
    $deleteMessage = "";

    if (isset($_POST["delete_account"])) {
      $deleteNick = $_POST["delete_nick"] ?? "";

    if ($deleteNick !== $admnick || !isset($users[$deleteNick])) {
        $deleteMessage = "Invalid account selection.";
    } elseif (in_array($deleteNick, $admins, true)) {
        $deleteMessage = "Admin accounts cannot be deleted.";
    } elseif (basename($deleteNick) !== $deleteNick) {
        $deleteMessage = "Invalid account name.";
    } else {
        $stateFile = "statefilesz/" . $deleteNick . ".dat";
        $userFile = "usersz/" . $deleteNick;
        $recDir = "recs/" . $deleteNick;

        if (file_exists($stateFile)) unlink($stateFile);
        if (file_exists($userFile)) unlink($userFile);
        if (is_dir($recDir)) xrmdir($recDir);

        unset($users[$deleteNick]);
        $deleteMessage = "Account \"" . $deleteNick . "\" deleted.";

        // After delete, return to admin user.
        $admnick = $_SESSION["nick"];
        $admnuser = $users[$admnick] ?? null;
    }
}
    $uploadMessage = "";

    if (isset($admnuser) && isset($_FILES["uploadedfile"])
        && $_FILES["uploadedfile"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES["uploadedfile"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        $uploadMessage = "Upload failed.";
    } elseif (strtolower(pathinfo($file["name"], PATHINFO_EXTENSION)) !== "dat") {
        $uploadMessage = "Wrong filetype!";
    } elseif ($file["size"] !== 67910) {
        $uploadMessage = "Wrong file size.";
    } else {
        $path = "statefilesz/" . $admnick . ".dat";
        $oldPath = "statefilesz/" . $admnick . "_old.dat";
        $hadOld = file_exists($path);

    if ($hadOld && !copy($path, $oldPath)) {
        $uploadMessage = "Could not back up the old stats file.";
    } elseif (move_uploaded_file($file["tmp_name"], $path)) {
        chmod($path, 0644);

    if ($hadOld) {
        $oldTimes = stateTimes($admnick . "_old", $admnuser["elmaname"]);
        $newTimes = stateTimes($admnick, $admnuser["elmaname"]);
        $oldtt = 0;
        $newtt = 0;
        $improvementLog = "";

    for ($x = 0; $x < 54; $x++) {
        $oldTime = (int)($oldTimes[$x + 1][1] ?? 0);
        $newTime = (int)($newTimes[$x + 1][1] ?? 0);

    if ($oldTime > 0 && $newTime > 0 && $newTime < $oldTime) {
        $improvementLog .= $admnick . "|" . ($x + 1) . "|"
            . $oldTime . "|" . $newTime . "|" . time() . "\n";
    }
}

    if ($improvementLog !== "") {
        file_put_contents("newtajms2", $improvementLog, FILE_APPEND | LOCK_EX);
}
    for ($x = 0; $x < 54; $x++) {
        $oldTime = (int)($oldTimes[$x + 1][1] ?? 0);
        $newTime = (int)($newTimes[$x + 1][1] ?? 0);
        $oldtt += ($oldTime > 0) ? $oldTime : 60000;
        $newtt += ($newTime > 0) ? $newTime : 60000;
    }
    if (floor($newtt / 6000) < floor($oldtt / 6000)) {
        file_put_contents("newttz", $admnick . "|" . floor($newtt / 6000) . "|" . time() . "\n", FILE_APPEND | LOCK_EX);
    }
    if ($newtt < $oldtt && file_exists("statefileszh/" . $admnick)) {
        $n = 0;
        while (file_exists("historyz/" . $admnick . "_" . $n)) {
            $n++;
    }

    if (copy("statefileszh/" . $admnick, "historyz/" . $admnick . "_" . $n)) {
        file_put_contents("historyz/" . $admnick . "_" . $n, time() . "\n", FILE_APPEND);
        }
    }

    if (file_exists($oldPath)) {
        unlink($oldPath);
    }
}

        $uploadMessage = "Stats uploaded for " . $admnick . ".";
    } else {
        $uploadMessage = "Error uploading the file!";
    }
    }
}
?>
<?php
    if (isset($admnuser)) {
?>
    <div class="lefty">
    <b>Upload stats</b><br/>
    <form enctype="multipart/form-data"
      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, "UTF-8"); ?>?nick=<?php echo rawurlencode($admnick); ?>"
      method="post">
    <input type="hidden" name="MAX_FILE_SIZE" value="3000000"/>
    <table border="0" cellpadding="0" cellspacing="2">
    <tr>
      <td>State file (*.dat):&nbsp;</td>
      <td><input name="uploadedfile" type="file" accept=".dat"/></td>
      <td style="text-align: left; padding-left: 0;">
    <input type="submit" value="Upload" style="position: relative; left: -28px;"/>
    </td>
    </tr>
    </table>
    </form>
    <br/><br/>
<?php if ($uploadMessage !== "") echo("<br/>" . htmlspecialchars($uploadMessage, ENT_QUOTES, "UTF-8") . "<br/>"); ?>
    <br/>
    <table border="0" cellpadding="0" cellspacing="0">
    <tr>
    <td valign="top">
    <b>Admin - Account settings: <?php echo $admnick; ?></b><br/><br/>
    <form enctype="multipart/form-data" action="<?php echo($_SERVER["PHP_SELF"]); ?>?nick=<?php echo($admnick); ?>" method="post" autocomplete="off">
    <input type="hidden" name="OGnick" value="<?php echo($admnick); ?>" />
    <table border="0" cellpadding="0" cellspacing="2">
    <tr>
      <td>Nick:&nbsp;</td>
    <td>
    <div style="display: flex; gap: 4px; width: 178px;">
    <input type="text"
      style="width: 87px; box-sizing: border-box;"
      value="<?php echo htmlspecialchars($admnick, ENT_QUOTES, 'UTF-8'); ?>"
      disabled="disabled"/>

    <select style="width: 87px; box-sizing: border-box;"
      onchange="if (this.value) location.href='adm.php?nick=' + encodeURIComponent(this.value)">
<?php
    $adminNicks = array();
    foreach ($users as $user) {
    if (!empty($user["nick"])) {
        $adminNicks[] = $user["nick"];
    }
    }
    natcasesort($adminNicks);
    foreach ($adminNicks as $nick) {
        $safeNick = htmlspecialchars($nick, ENT_QUOTES, "UTF-8");
        $selected = ($nick === $admnick) ? ' selected="selected"' : '';
        echo('<option value="' . $safeNick . '"' . $selected . '>' . $safeNick . '</option>');
    }
?>
    </select>
    </div>
    </td>
    </tr>
    <tr>
      <td>State.dat name:&nbsp;</td>
      <td><input type="text" name="elmaname" style="width: 170px" value="<?php echo($admnuser["elmaname"]); ?>"/></td>
    </tr>
    <tr>
      <td>Pwd:&nbsp;</td> 
      <td><input type="password" name="pwd" style="width: 170px" autocomplete="new-password"/></td>
    </tr>
    <tr>
      <td>Repeat pwd:&nbsp;</td> 
      <td><input type="password" name="pwd2" style="width: 170px" autocomplete="new-password"/></td>
    </tr>
    <tr>
      <td>Nationality:&nbsp;</td>
      <td><select name='country' style="width: 178px"><option value='NULL'>Select..</option><option value='AF'>Afghanistan</option><option value='AL'>Albania</option><option value='DZ'>Algeria</option><option value='AS'>American Samoa</option><option value='AD'>Andorra</option><option value='AO'>Angola</option><option value='AI'>Anguilla</option><option value='AQ'>Antarctica</option><option value='AG'>Antigua and Barbuda</option><option value='AR'>Argentina</option><option value='AM'>Armenia</option><option value='AW'>Aruba</option><option value='AU'>Australia</option><option value='AT'>Austria</option><option value='AZ'>Azerbaijan</option><option value='BS'>Bahamas</option><option value='BH'>Bahrain</option><option value='BD'>Bangladesh</option><option value='BB'>Barbados</option><option value='BY'>Belarus</option><option value='BE'>Belgium</option><option value='BZ'>Belize</option><option value='BJ'>Benin</option><option value='BM'>Bermuda</option><option value='BT'>Bhutan</option><option value='BO'>Bolivia</option><option value='BA'>Bosnia and Herzegovina</option><option value='BW'>Botswana</option><option value='BV'>Bouvet Island</option><option value='BR'>Brazil</option><option value='IO'>British Indian Ocean...</option><option value='BN'>Brunei Darussalam</option><option value='BG'>Bulgaria</option><option value='BF'>Burkina Faso</option><option value='BI'>Burundi</option><option value='KH'>Cambodia</option><option value='CM'>Cameroon</option><option value='CA'>Canada</option><option value='CV'>Cape Verde</option><option value='KY'>Cayman Islands</option><option value='CF'>Central African Republic</option><option value='TD'>Chad</option><option value='CL'>Chile</option><option value='CN'>China</option><option value='CX'>Christmas Island</option><option value='CC'>Cocos (Keeling) Islands</option><option value='CO'>Colombia</option><option value='KM'>Comoros</option><option value='CG'>Congo</option><option value='CD'>Congo, the Democrati...</option><option value='CK'>Cook Islands</option><option value='CR'>Costa Rica</option><option value='CI'>Cote D'Ivoire</option><option value='HR'>Croatia</option><option value='CU'>Cuba</option><option value='CY'>Cyprus</option><option value='CZ'>Czech Republic</option><option value='DK'>Denmark</option><option value='DJ'>Djibouti</option><option value='DM'>Dominica</option><option value='DO'>Dominican Republic</option><option value='EC'>Ecuador</option><option value='EG'>Egypt</option><option value='SV'>El Salvador</option><option value='GQ'>Equatorial Guinea</option><option value='ER'>Eritrea</option><option value='EE'>Estonia</option><option value='ET'>Ethiopia</option><option value='FK'>Falkland Islands (Ma...</option><option value='FO'>Faroe Islands</option><option value='FJ'>Fiji</option><option value='FI'>Finland</option><option value='FR'>France</option><option value='GF'>French Guiana</option><option value='PF'>French Polynesia</option><option value='TF'>French Southern Terr...</option><option value='GA'>Gabon</option><option value='GM'>Gambia</option><option value='GE'>Georgia</option><option value='DE'>Germany</option><option value='GH'>Ghana</option><option value='GI'>Gibraltar</option><option value='GR'>Greece</option><option value='GL'>Greenland</option><option value='GD'>Grenada</option><option value='GP'>Guadeloupe</option><option value='GU'>Guam</option><option value='GT'>Guatemala</option><option value='GN'>Guinea</option><option value='GW'>Guinea-Bissau</option><option value='GY'>Guyana</option><option value='HT'>Haiti</option><option value='HM'>Heard Island and Mcd...</option><option value='VA'>Holy See (Vatican Ci...</option><option value='HN'>Honduras</option><option value='HK'>Hong Kong</option><option value='HU'>Hungary</option><option value='IS'>Iceland</option><option value='IN'>India</option><option value='ID'>Indonesia</option><option value='IR'>Iran, Islamic Republic of</option><option value='IQ'>Iraq</option><option value='IE'>Ireland</option><option value='IL'>Israel</option><option value='IT'>Italy</option><option value='JM'>Jamaica</option><option value='JP'>Japan</option><option value='JO'>Jordan</option><option value='KZ'>Kazakhstan</option><option value='KE'>Kenya</option><option value='KI'>Kiribati</option><option value='KP'>Korea, Democratic Pe...</option><option value='KR'>Korea, Republic of</option><option value='KW'>Kuwait</option><option value='KG'>Kyrgyzstan</option><option value='LA'>Lao People's Democra...</option><option value='LV'>Latvia</option><option value='LB'>Lebanon</option><option value='LS'>Lesotho</option><option value='LR'>Liberia</option><option value='LY'>Libyan Arab Jamahiriya</option><option value='LI'>Liechtenstein</option><option value='LT'>Lithuania</option><option value='LU'>Luxembourg</option><option value='MO'>Macao</option><option value='MK'>Macedonia, the Forme...</option><option value='MG'>Madagascar</option><option value='MW'>Malawi</option><option value='MY'>Malaysia</option><option value='MV'>Maldives</option><option value='ML'>Mali</option><option value='MT'>Malta</option><option value='MH'>Marshall Islands</option><option value='MQ'>Martinique</option><option value='MR'>Mauritania</option><option value='MU'>Mauritius</option><option value='YT'>Mayotte</option><option value='MX'>Mexico</option><option value='FM'>Micronesia, Federate...</option><option value='MD'>Moldova, Republic of</option><option value='MC'>Monaco</option><option value='MN'>Mongolia</option><option value='MS'>Montserrat</option><option value='MA'>Morocco</option><option value='MZ'>Mozambique</option><option value='MM'>Myanmar</option><option value='NA'>Namibia</option><option value='NR'>Nauru</option><option value='NP'>Nepal</option><option value='NL'>Netherlands</option><option value='AN'>Netherlands Antilles</option><option value='NC'>New Caledonia</option><option value='NZ'>New Zealand</option><option value='NI'>Nicaragua</option><option value='NE'>Niger</option><option value='NG'>Nigeria</option><option value='NU'>Niue</option><option value='NF'>Norfolk Island</option><option value='MP'>Northern Mariana Islands</option><option value='NO'>Norway</option><option value='OM'>Oman</option><option value='PK'>Pakistan</option><option value='PW'>Palau</option><option value='PS'>Palestinian Territor...</option><option value='PA'>Panama</option><option value='PG'>Papua New Guinea</option><option value='PY'>Paraguay</option><option value='PE'>Peru</option><option value='PH'>Philippines</option><option value='PN'>Pitcairn</option><option value='PL'>Poland</option><option value='PT'>Portugal</option><option value='PR'>Puerto Rico</option><option value='QA'>Qatar</option><option value='RE'>Reunion</option><option value='RO'>Romania</option><option value='RU'>Russian Federation</option><option value='RW'>Rwanda</option><option value='SH'>Saint Helena</option><option value='KN'>Saint Kitts and Nevis</option><option value='LC'>Saint Lucia</option><option value='PM'>Saint Pierre and Miquelon</option><option value='VC'>Saint Vincent and th...</option><option value='WS'>Samoa</option><option value='SM'>San Marino</option><option value='ST'>Sao Tome and Principe</option><option value='SA'>Saudi Arabia</option><option value='SN'>Senegal</option><option value='CS'>Serbia and Montenegro</option><option value='SC'>Seychelles</option><option value='SL'>Sierra Leone</option><option value='SG'>Singapore</option><option value='SK'>Slovakia</option><option value='SI'>Slovenia</option><option value='SB'>Solomon Islands</option><option value='SO'>Somalia</option><option value='ZA'>South Africa</option><option value='GS'>South Georgia and th...</option><option value='ES'>Spain</option><option value='LK'>Sri Lanka</option><option value='SD'>Sudan</option><option value='SR'>Suriname</option><option value='SJ'>Svalbard and Jan Mayen</option><option value='SZ'>Swaziland</option><option value='SE'>Sweden</option><option value='CH'>Switzerland</option><option value='SY'>Syrian Arab Republic</option><option value='TW'>Taiwan, Province of China</option><option value='TJ'>Tajikistan</option><option value='TZ'>Tanzania, United Rep...</option><option value='TH'>Thailand</option><option value='TL'>Timor-Leste</option><option value='TG'>Togo</option><option value='TK'>Tokelau</option><option value='TO'>Tonga</option><option value='TT'>Trinidad and Tobago</option><option value='TN'>Tunisia</option><option value='TR'>Turkey</option><option value='TM'>Turkmenistan</option><option value='TC'>Turks and Caicos Islands</option><option value='TV'>Tuvalu</option><option value='UG'>Uganda</option><option value='UA'>Ukraine</option><option value='AE'>United Arab Emirates</option><option value='GB'>United Kingdom</option><option value='US'>United States</option><option value='UM'>United States Minor ...</option><option value='UY'>Uruguay</option><option value='UZ'>Uzbekistan</option><option value='VU'>Vanuatu</option><option value='VE'>Venezuela</option><option value='VN'>Viet Nam</option><option value='VG'>Virgin Islands, British</option><option value='VI'>Virgin Islands, U.s.</option><option value='WF'>Wallis and Futuna</option><option value='EH'>Western Sahara</option><option value='YE'>Yemen</option><option value='ZM'>Zambia</option><option value='ZW'>Zimbabwe</option></select></td>
    </tr>
    <tr>
      <td>Team:&nbsp;</td>
      <td><input type="text" name="team" style="width: 170px" value="<?php echo($admnuser["team"]); ?>"/></td>
    </tr>
    <tr>
      <td>Email address:&nbsp;</td>
      <td><input type="text" name="email" style="width: 170px" value="<?php echo($admnuser["email"]); ?>"/></td>
    </tr>
    <tr>
      <td>Site theme:&nbsp;</td>
      <td><select name="theme" style="width: 178px">
      <?php
        $themes = array("Default", "Klassik", "Mopo");
        foreach ($themes as $theme) {
          $sel = "";
          if ($admnuser["theme"] == $theme) $sel = " selected=\"selected\"";
          echo("<option value=\"" . $theme . "\"" . $sel . ">" . $theme . "</option>");
        }
      ?>
      </select></td>
    </tr>
    <tr>
      <td>Timezone:&nbsp;</td>
      <td><select name="timezone" style="width: 178px">
      <?php
        for ($x = -12;$x < 15;$x++)
          echo("<option value=\"" . $x . "\"". ($admnuser["timezone"] == $x ? " selected=\"selected\"" : "") . 
               ">" . "GMT " . ($x > 0 ? "+" : "") . $x . ":00</option>");
      ?>
      </select></td>
    </tr>
    <tr>
      <td>Time format (<a href="tfhelp.php">?</a>):&nbsp;</td>
      <td><input type="text" name="timeformat" style="width: 170px" value="<?php echo($admnuser["timeformat"]); ?>"/></td>
    </tr>
    <tr><th colspan="2">&nbsp;</th></tr>
    <tr>
      <td colspan="2"><center><input type="submit" value="Update"/></center></td>
    </tr>
    <tr><td colspan="2">&nbsp;</td></tr>

<?php if ($deleteMessage === "") { ?>
        <tr><td colspan="2">&nbsp;</td></tr>
<?php } else { ?>
    <tr>
      <td colspan="2" style="text-align: center;">
    <span style="position: relative; left: 200px;">
      <?php echo htmlspecialchars($deleteMessage, ENT_QUOTES, "UTF-8"); ?>
    </span>
      </td>
    </tr>
<?php } ?>
    </table>
    </form>
    </td>
    <td valign="top" width="220" style="padding-left: 70px;">
      <b>Other stuff</b><br/><br/>
    <form method="post"
      onsubmit="return confirm('Are u sure? This will delete the user.');">
    <input type="hidden" name="delete_nick"
      value="<?php echo htmlspecialchars($admnick, ENT_QUOTES, 'UTF-8'); ?>">
    <button type="submit" name="delete_account"
      style="color: #3675c5; background: none; border: 0; padding: 0; cursor: pointer; font: inherit;">
  Delete account
    </button>
    </form>
    <a href="adm.php?nick=<?php echo rawurlencode($admnick); ?>&recover_state=1"
      style="color: #3675c5;">Recover state.dat</a><br/>
    </td>
    </tr>
    </table>
<?php
    echo($adm_acc_status);
?>
</div>
<?php
    } else {
      echo("<span style=\"color: #FF0000\">User don't exist!</span><br/>");
    }
    } else {
      echo("<span style=\"color: #FF0000\">You are nat admin!</span><br/>");
    }
?>
<?php include("tpo.php"); ?>
