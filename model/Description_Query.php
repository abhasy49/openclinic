<?php
/**
 * This file is part of OpenClinic
 *
 * Copyright (c) 2002-2004 jact
 * Licensed under the GNU GPL. For full terms see the file LICENSE.
 *
 * $Id: Description_Query.php,v 1.1 2004/01/29 14:25:30 jact Exp $
 */

/**
 * Description_Query.php
 ********************************************************************
 * Contains the class Description_Query
 ********************************************************************
 * Author: jact <jachavar@terra.es>
 * Last modified: 29/01/04 15:25
 */

require_once("../classes/Query.php");
require_once("../classes/Description.php");

/**
 * Description_Query data access component for domain tables
 ********************************************************************
 * @author jact <jachavar@terra.es>
 * @version 0.3
 * @access public
 ********************************************************************
 * Methods:
 *  bool select(string $tableName, string $fieldCode, string $fieldDescription = "", string $keyValue = "")
 *  mixed fetchDescription(void)
 *  array fetchRows(string $col = "")
 *  bool update(string $tableName, Description $des)
 */
class Description_Query extends Query
{
  /**
   * bool select(string $tableName, string $fieldCode, string $fieldDescription = "", string $keyValue = "")
   ********************************************************************
   * Executes a query
   ********************************************************************
   * @param string $tableName table name to query
   * @param string $fieldCode code of row to fetch
   * @param string $fieldDescription (optional) description of row to fetch
   * @param string $keyValue (optional) value of fieldCode to fetch
   * @return boolean returns false, if error occurs
   * @access public
   */
  function select($tableName, $fieldCode, $fieldDescription = "", $keyValue = "")
  {
    $sql = "SELECT " . $fieldCode;
    if ($fieldDescription != "")
    {
      $sql .= "," . $fieldDescription;
    }
    $sql .= " FROM ".$tableName." ";
    if ($keyValue != "")
    {
      $sql .= " WHERE " . $fieldCode . "='" . urlencode($keyValue) . "'";
    }
    $sql .= " ORDER BY " . ($fieldDescription != "" ? $fieldDescription : $fieldCode);

    $result = $this->exec($sql);
    if ($result == false)
    {
      $this->_error = "Error accessing the " . $tableName . " table.";
    }

    return $result;
  }

  /**
   * mixed fetchDescription(void)
   ********************************************************************
   * Fetches a row from the query result and populates the Description object.
   ********************************************************************
   * @return List returns list object or false if no more list rows to fetch
   * @access public
   */
  function fetchDescription()
  {
    $array = $this->fetchRow(MYSQL_NUM);
    if ($array == false)
    {
      return false;
    }

    $des = new Description();
    $des->setCode($array[0]);
    if (isset($array[1]))
    {
      $des->setDescription(urldecode($array[1]));
    }

    return $des;
  }

  /**
   * array fetchRows(string $col = "")
   ********************************************************************
   * Fetches all rows from the query result.
   ********************************************************************
   * @return assocArray returns associative array containing domain codes and values.
   * @access public
   */
  function fetchRows($col = "")
  {
    if ($col == "")
    {
      $col = "description";
    }

    while ($result = $this->fetchRow())
    {
      $assoc[$result["code"]] = $result[$col];
    }

    return $assoc;
  }

  /**
   * bool update(string $tableName, Description $des)
   ********************************************************************
   * Update a row in a domain table.
   ********************************************************************
   * @param string $tableName table name of domain table to query
   * @param Description $des row to update
   * @return boolean returns false, if error occurs
   * @access public
   */
  function update($tableName, $des)
  {
    $sql = "UPDATE " . $tableName . " SET ";
    $sql .= "description='" . urlencode($des->getDescription()) . "' ";
    if ($tableName == "profile_tbl")
    {
      $sql .= "WHERE id_profile=" . $des->getCode();
    }

    $result = $this->exec($sql);
    if ($result == false)
    {
      $this->_error = "Error updating " . $table;
    }

    return $result;
  }
} // end class
?>