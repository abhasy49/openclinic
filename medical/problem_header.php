<?php
/**
 * This file is part of OpenClinic
 *
 * Copyright (c) 2002-2004 jact
 * Licensed under the GNU GPL. For full terms see the file LICENSE.
 *
 * $Id: problem_header.php,v 1.1 2004/03/24 19:34:52 jact Exp $
 */

/**
 * problem_header.php
 ********************************************************************
 * Contains showProblemHeader function
 ********************************************************************
 * Author: jact <jachavar@terra.es>
 * Last modified: 24/03/04 20:34
 */

  if (str_replace("\\", "/", __FILE__) == $_SERVER['SCRIPT_FILENAME'])
  {
    header("Location: ../index.php");
    exit();
  }

  require_once("../classes/Problem_Query.php");
  require_once("../lib/misc_lib.php");

  /**
   * bool showProblemHeader(int $idProblem)
   ********************************************************************
   * Draws a header with medical problem information.
   ********************************************************************
   * @param int $idProblem key of medical problem to show header
   * @return boolean false if medical problem does not exist, true otherwise
   * @access public
   */
  function showProblemHeader($idProblem)
  {
    $problemQ = new Problem_Query();
    $problemQ->connect();
    if ($problemQ->errorOccurred())
    {
      showQueryError($problemQ);
    }

    $numRows = $problemQ->select($idProblem);
    if ($problemQ->errorOccurred())
    {
      $problemQ->close();
      showQueryError($problemQ);
    }

    if ( !$numRows )
    {
      return false;
    }

    $problem = $problemQ->fetchProblem();
    $problemQ->freeResult();
?>

    <table width="100%">
      <tr>
        <td><?php echo _("Wording") . ': ' . fieldPreview($problem->getWording()); ?></td>

        <td class="number"><?php echo _("Opening Date") . ': ' . $problem->getOpeningDate(); ?></td>

        <td class="number"><?php echo _("Last Update Date") . ': ' . $problem->getLastUpdateDate(); ?></td>
      </tr>
    </table>

<?php
    unset($problemQ);
    unset($problem);

    return true;
  }
?>