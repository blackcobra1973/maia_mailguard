<?php
    /*
     * $Id$
     *
     * MAIA MAILGUARD LICENSE v.1.0
     *
     * Copyright 2004 by Robert LeBlanc <rjl@renaissoft.com>
     *               and David Morton   <mortonda@dgrmm.net>
     * All rights reserved.
     *
     * PREAMBLE
     *
     * This License is designed for users of Maia Mailguard
     * ("the Software") who wish to support the Maia Mailguard project by
     * leaving "Maia Mailguard" branding information in the HTML output
     * of the pages generated by the Software, and providing links back
     * to the Maia Mailguard home page.  Users who wish to remove this
     * branding information should contact the copyright owner to obtain
     * a Rebranding License.
     *
     * DEFINITION OF TERMS
     *
     * The "Software" refers to Maia Mailguard, including all of the
     * associated PHP, Perl, and SQL scripts, documentation files, graphic
     * icons and logo images.
     *
     * GRANT OF LICENSE
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions
     * are met:
     *
     * 1. Redistributions of source code must retain the above copyright
     *    notice, this list of conditions and the following disclaimer.
     *
     * 2. Redistributions in binary form must reproduce the above copyright
     *    notice, this list of conditions and the following disclaimer in the
     *    documentation and/or other materials provided with the distribution.
     *
     * 3. The end-user documentation included with the redistribution, if
     *    any, must include the following acknowledgment:
     *
     *    "This product includes software developed by Robert LeBlanc
     *    <rjl@renaissoft.com>."
     *
     *    Alternately, this acknowledgment may appear in the software itself,
     *    if and wherever such third-party acknowledgments normally appear.
     *
     * 4. At least one of the following branding conventions must be used:
     *
     *    a. The Maia Mailguard logo appears in the page-top banner of
     *       all HTML output pages in an unmodified form, and links
     *       directly to the Maia Mailguard home page; or
     *
     *    b. The "Powered by Maia Mailguard" graphic appears in the HTML
     *       output of all gateway pages that lead to this software,
     *       linking directly to the Maia Mailguard home page; or
     *
     *    c. A separate Rebranding License is obtained from the copyright
     *       owner, exempting the Licensee from 4(a) and 4(b), subject to
     *       the additional conditions laid out in that license document.
     *
     * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
     * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
     * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
     * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
     * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
     * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
     * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
     * OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
     * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
     * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
     * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     *
     */
    require_once ("core.php");
    session_start();

    // This script is called to confirm all items before a given timestamp, as
    // reviewed in the email digest.

    // Set up and authenticate session based on token.  If the values are 
    // provided, force session to be the owner of the token, regardless of 
    // previous session information.
    if (array_key_exists('token', $_GET) && 
        array_key_exists('id', $_GET)    && 
        array_key_exists('type', $_GET)) {
      if (!isset($_SESSION['uid'])  ||
          !isset($_SESSION['euid']) ||
          $_GET["id"] != $_SESSION['uid'] || $_GET["euid"] != $_SESSION['euid'] ||
          time() > $_SESSION["timeout"]) {//if session is timed out, re-authenticate the session.
        header("Location: xlogin.php?action=rescue.php&" . $_SERVER["QUERY_STRING"]);
        exit();
      } 
      $token = trim($_GET["token"]);
      $type = trim($_GET["type"]);
    } else {
       header("Location: login.php");
       exit;
    }

    require_once ("maia_db.php");
    require_once ("display.php");
    require_once ("authcheck.php");
    require_once ("smtp.php");
    require_once ("mailtools.php");
    require_once ("encrypt.php");
    $display_language = get_display_language($euid);
    require_once ("./locale/$display_language/smtp.php");
    require_once ("./locale/$display_language/db.php");
    require_once ("./locale/$display_language/display.php");
    require_once ("./locale/$display_language/quarantine.php");
    require_once ("./locale/$display_language/reportspam.php");
    require_once ("./locale/$display_language/wblist.php");

    	$message = "";
    switch($type) {
      case "ham":  //Ok, this isn't really "releasing", but the logic is the same.
        $reported = 0;
        $select = "SELECT maia_mail.id, maia_mail.sender_email " .
                  "FROM maia_mail, maia_mail_recipients " .
                  "WHERE maia_mail.id = maia_mail_recipients.mail_id " .
                  "AND maia_mail_recipients.type = 'H' " .
                  "AND maia_mail_recipients.token = ? " .
                  "AND  SUBSTRING(maia_mail_recipients.token FROM 1 FOR 7) <> 'expired' " .
                  "AND maia_mail_recipients.recipient_id = ?";
        $sth = $dbh->prepare($select);
        if (PEAR::isError($sth)) {
            die($sth->getMessage() . ": " . $dbh->last_query . " [" . $token . "] [" . $euid . "]");
        }
	$res = $sth->execute(array($token, $euid));
        if (PEAR::isError($res)) {
            die($res->getMessage() . ": " . $dbh->last_query . " [" . $token . "] [" . $euid . "]");
        }
        while ($row = $res->fetchRow())
        {
            $mail_id = $row["id"];
            $sender  = $row["sender_email"];
            if (array_key_exists('wblist', $_GET)) {
              $message .= $lang[add_address_to_wb_list($euid, $sender, "B")];
              $message .= "<br>";
            }
            report_spam($euid, $mail_id);
            $reported++;
        }
        $sth->free();
        update_mail_stats($euid, "suspected_ham");
        if ($reported > 0) {
            $message .= sprintf($lang['text_spam_reported'], $reported) . ".<br>";
        }
        break;
      case "spam":
    	$rescued = 0;
        $select = "SELECT maia_mail.id, maia_mail.sender_email " .
                  "FROM maia_mail, maia_mail_recipients " .
                  "WHERE maia_mail.id = maia_mail_recipients.mail_id " .
                  "AND maia_mail_recipients.type IN ('S','P') " .
                  "AND maia_mail_recipients.token = ? " .
                  "AND  SUBSTRING(maia_mail_recipients.token FROM 1 FOR 7) <> 'expired' " .
                  "AND maia_mail_recipients.recipient_id = ?";
        $sth = $dbh->prepare($select);
        if (PEAR::isError($sth)) {
            die($sth->getMessage() . ": " . $dbh->last_query . " [" . $token . "] [" . $euid . "]");
        }
	$res = $sth->execute(array($token, $euid));
        if (PEAR::isError($res)) {
            die($res->getMessage() . ": " . $dbh->last_query . " [" . $token . "] [" . $euid . "]");
        }
        while ($row = $res->fetchRow())
        {
            $mail_id = $row["id"];
            $sender  = $row["sender_email"];
            if (array_key_exists('wblist', $_GET)) {
              $message .= $lang[add_address_to_wb_list($euid, $sender, "W")];
              $message .= "<br>";
            }
            rescue_item($euid, $mail_id);
            $rescued++;
        }
        $sth->free();
        update_mail_stats($euid, "suspected_spam");
        if ($rescued > 0) {
            $message .= sprintf($lang['text_spam_rescued'], $rescued) . ".<br>";
        }
        break;
     case "virus":
    	$rescued = 0;
        $select = "SELECT maia_mail.id, maia_mail.sender_email " .
                  "FROM maia_mail, maia_mail_recipients " .
                  "WHERE maia_mail.id = maia_mail_recipients.mail_id " .
                  "AND maia_mail_recipients.type = 'V' " .
                  "AND maia_mail_recipients.token = ? " .
                  "AND  SUBSTRING(maia_mail_recipients.token FROM 1 FOR 7) <> 'expired' " .
                  "AND maia_mail_recipients.recipient_id = ?";
        $sth = $dbh->prepare($select);
        if (PEAR::isError($sth)) {
            die($sth->getMessage() . ": " . $dbh->last_query . " [" . $token . "] [" . $euid . "]");
        }
	$res = $sth->execute(array($token, $euid));
        if (PEAR::isError($res)) {
            die($res->getMessage() . ": " . $dbh->last_query . " [" . $token . "] [" . $euid . "]");
        }
        while ($row = $res->fetchRow())
        {
            $mail_id = $row["id"];
            $sender  = $row["sender_email"];
            if (array_key_exists('wblist', $_GET)) {
              $message .= $lang[add_address_to_wb_list($euid, $sender, "W")];
              $message .= "<br>";
            }
            rescue_item($euid, $mail_id);
            $rescued++;
        }
        $sth->free();
        if ($rescued > 0) {
            $message .= sprintf($lang['text_viruses_rescued'], $rescued) . ".<br>";
        }
        break;

     case "attachment":
    	$rescued = 0;
        $select = "SELECT maia_mail.id, maia_mail.sender_email " .
                  "FROM maia_mail, maia_mail_recipients " .
                  "WHERE maia_mail.id = maia_mail_recipients.mail_id " .
                  "AND maia_mail_recipients.type = 'F' " .
                  "AND maia_mail_recipients.token = ? " .
                  "AND  SUBSTRING(maia_mail_recipients.token FROM 1 FOR 7) <> 'expired' " .
                  "AND maia_mail_recipients.recipient_id = ?";
        $sth = $dbh->prepare($select);
        if (PEAR::isError($sth)) {
            die($sth->getMessage() . ": " . $dbh->last_query . " [" . $token . "] [" . $euid . "]");
        }
	$res = $sth->execute(array($token, $euid));
        if (PEAR::isError($res)) {
            die($res->getMessage() . ": " . $dbh->last_query . " [" . $token . "] [" . $euid . "]");
        }
        while ($row = $res->fetchRow())
        {
            $mail_id = $row["id"];
            $sender  = $row["sender_email"];
            if (array_key_exists('wblist', $_GET)) {
              $message .= $lang[add_address_to_wb_list($euid, $sender, "W")];
              $message .= "<br>";
            }
            rescue_item($euid, $mail_id);
            $rescued++;
        }
        $sth->free();
        if ($rescued > 0) {
            $message .= sprintf($lang['text_attachments_rescued'], $rescued) . ".<br>";
        }
        break;
     case "header":
    	$rescued = 0;
        $select = "SELECT maia_mail.id, maia_mail.sender_email " .
                  "FROM maia_mail, maia_mail_recipients " .
                  "WHERE maia_mail.id = maia_mail_recipients.mail_id " .
                  "AND maia_mail_recipients.type = 'B' " .
                  "AND maia_mail_recipients.token = ? " .
                  "AND  SUBSTRING(maia_mail_recipients.token FROM 1 FOR 7) <> 'expired' " .
                  "AND maia_mail_recipients.recipient_id = ?";
        $sth = $dbh->prepare($select);
        if (PEAR::isError($sth)) {
            die($sth->getMessage() . ": " . $dbh->last_query . " [" . $token . "] [" . $euid . "]");
        }
	$res = $sth->execute(array($token, $euid));
        if (PEAR::isError($res)) {
            die($res->getMessage() . ": " . $dbh->last_query . " [" . $token . "] [" . $euid . "]");
        }
        while ($row = $res->fetchRow())
        {
            $mail_id = $row["id"];
            $sender  = $row["sender_email"];
            if (array_key_exists('wblist', $_GET)) {
              $message .= $lang[add_address_to_wb_list($euid, $sender, "W")];
              $message .= "<br>";
            }
            rescue_item($euid, $mail_id);
            $rescued++;
        }
        $sth->free();
        if ($rescued > 0) {
            $message .= sprintf($lang['text_headers_rescued'], $rescued) . ".<br>";
        }
    }
    $_SESSION["message"] = $message;
    header("Location: welcome.php");
?>
