<?php
/**
 * This file is part of OpenClinic
 *
 * Copyright (c) 2002-2005 jact
 * Licensed under the GNU GPL. For full terms see the file LICENSE.
 *
 * $Id: File.php,v 1.1 2005/07/21 17:41:44 jact Exp $
 */

/**
 * File.php
 *
 * Contains the class File
 *
 * Author: jact <jachavar@gmail.com>
 */

/**
 * File set of functions connected to files management
 *
 * @author jact <jachavar@gmail.com>
 * @access public
 * @since 0.8
 *
 * Methods:
 *  array getDirContent(string $dir, bool $subDir = false, array $allowedExtensions = null)
 *  bool upload(array &$file, string $destinationDir = "", string $destinationName = "", bool $secure = true)
 *  mixed sendMail(string $from, string $fromName, string $to, string $toName, string $subject, string $text, string $html, array $attachFiles = null)
 */
class File
{
  /*
   * array getDirContent(string $dir, bool $subDir = false, array $allowedExtensions = null)
   *
   * Returns an array with directory files
   *
   * @param string $dir
   * @param boolean $subDir (optional) indicates if returns subdirectories too
   * @param array $allowedExtensions (optional)
   * @return array associative (in alphabetic order)
   * @access public
   */
  function getDirContent($dir, $subDir = false, $allowedExtensions = null)
  {
    $handle = opendir($dir);
    $arrayFiles = null;
    $arrayDirs = null;
    while (($file = readdir($handle)) !== false)
    {
      if ($file == 'CVS' || $file == '.' || $file == 'index.php') // $file == '..' ||
      {
        continue;
      }
      elseif (is_file($dir . '/' . $file))
      {
        if ($allowedExtensions == null)
        {
          $arrayFiles["$file"] = $file;
        }
        else
        {
          foreach ($allowedExtensions as $value)
          {
            if (ereg($value . "$", $file))
            {
              $arrayFiles["$file"] = $file;
              break;
            }
          }
        }
      }
      elseif (is_dir($dir . '/' . $file))
      {
        if ($subDir)
        {
          $arrayDirs["$file"] = $file;
        }
      }
    }
    closedir($handle);

    if ($arrayFiles != null)
    {
      asort($arrayFiles);
    }

    if ($arrayDirs)
    {
      asort($arrayDirs);
    }

    return array_merge($arrayDirs, $arrayFiles);
  }

  /*
   * bool upload(array &$file, string $destinationDir = "", string $destinationName = "", bool $secure = true)
   *
   * Upload a file to the server
   *
   * @param array_reference &$file part from $_FILES array
   * @param string $destinationDir (optional) destination directory
   * @param string $destinationName (optional) destination filename
   * @param boolean $secure (optional) to remove execution permissions to file if it is possible
   * @return boolean true if ok, false otherwise
   * @access public
   */
  function upload(&$file, $destinationDir = "", $destinationName = "", $secure = true)
  {
    $ret = false;

    if (isset($file['tmp_name']) && isset($file['name']))
    {
      if ($destinationName == '')
      {
        $destinationName = $file['name'];
      }
      $destinationFile = $destinationDir . '/' . $destinationName;

      if (move_uploaded_file($file['tmp_name'], $destinationFile))
      {
        if ($secure)
        {
          chmod($destinationFile, 0644); // without execution permissions if it is possible
        }
        $ret = true;
      }
    }

    return $ret;
  }

  /**
   * mixed sendMail(string $from, string $fromName, string $to, string $toName, string $subject, string $text, string $html, array $attachFiles = null)
   *
   * Sends a mail in text and html format with attached files
   *
   * @param string $from sender mail address like "my@address.com"
   * @param string $fromName sender name like "My Name"
   * @param string $to recipient mail address like "your@address.com"
   * @param string $toName recipients name like "Your Name"
   * @param string $subject subject of the mail like "This is my first testmail"
   * @param string $text text version of the mail
   * @param string $html html version of the mail
   * @param array $attachFiles (optional) array containing the filenames to attach like array("file1", "file2")
   * @return mixed boolean or string if a necessary parameter is missing
   * @access public
   */
  function sendMail($from, $fromName, $to, $toName, $subject, $text, $html, $attachFiles = null)
  {
    $html = $html ? $html : preg_replace("/\n/", "<br />", $text) or die("neither text nor html part present.");
    $text = $text ? $text : "Sorry, but you need an html mailer to read this mail.";
    $from or die("sender address missing");
    $to or die("recipient address missing");

    $outerBoundary = "----=_OuterBoundary_000";
    $innerBoundary = "----=_InnerBoundery_001";

    $headers = "MIME-Version: 1.0\r\n"; // maybe cause problems
    $headers .= "From: " . $fromName . " <" . $from . ">\n";
    $headers .= "To: " . $toName . " <" . $to . ">\n";
    $headers .= "Reply-To: " . $fromName . " <" . $from. ">\n";
    $headers .= "X-Priority: 1\n";
    $headers .= "X-MSMail-Priority: High\n";
    $headers .= "X-Mailer: My PHP Mailer\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $outerBoundary . "\"\n";

    //Messages start with text/html alternatives in OB
    $msg = "This is a multi-part message in MIME format.\n";
    $msg .= "\n--".$outerBoundary."\n";
    $msg .= "Content-Type: multipart/alternative; boundary=\"" . $innerBoundary . "\"\n\n";

    //plaintext section
    $msg .= "\n--" . $innerBoundary . "\n";
    $msg .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
    $msg .= "Content-Transfer-Encoding: quoted-printable\n\n";
    // plaintext goes here
    $msg .= $text . "\n\n";

    // html section
    $msg .= "\n--" . $innerBoundary . "\n";
    $msg .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
    $msg .= "Content-Transfer-Encoding: base64\n\n";
    // html goes here
    $msg .= chunk_split(base64_encode($html)) . "\n\n";

    // end of IB
    $msg .= "\n--" . $innerBoundary . "--\n";

    // attachments
    if ($attachFiles)
    {
      foreach ($attachFiles as $file)
      {
        $pathArray = explode("/", $file);
        $fileName = $pathArray[count($pathArray) - 1];
        $msg .= "\n--" . $outerBoundary . "\n";
        $msg .= "Content-Type: application/octet-stream; name=\"" . $fileName . "\"\n";
        $msg .= "Content-Disposition: attachment; filename=\"" . $fileName . "\"\n";
        $msg .= "Content-Transfer-Encoding: base64\n\n";

        //file goes here
        $fd = fopen($file, "r");
        $fileContent = fread($fd, filesize($file));
        fclose($fd);
        $fileContent = chunk_split(base64_encode($fileContent));
        $msg .= $fileContent;
        $msg .= "\n\n";
      }
    }

    //message ends
    $msg .= "\n--" . $outerBoundary . "--\n";

    return mail($to, $subject, $msg, $headers);
  } // end function
} // end class
?>