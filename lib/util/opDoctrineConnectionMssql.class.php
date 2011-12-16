<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opDoctrineConnectionMssql
 *
 * @package    OpenPNE
 * @subpackage util
 * @author     Kousuke Ebihara <ebihara@php.net>
 */
class opDoctrineConnectionMssql extends Doctrine_Connection_Mssql
{
  public function __construct(Doctrine_Manager $manager, $adapter)
  {
    parent::__construct($manager, $adapter);

    $this->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);
  }

  public function replaceNonAggregatedColumnsInSelectList($query, Doctrine_Query $queryOrigin = null)
  {
    if (!$queryOrigin)
    {
      return $query;
    }

    $groupBy = $queryOrigin->getSqlQueryPart('groupby');
    if (!$groupBy)
    {
        return $query;
    }

    $selectList = explode(',', implode(',', $queryOrigin->getSqlQueryPart('select')));
    foreach ($selectList as $key => $select)
    {
      // might be user-written selection item
      if (false === strpos($select, ' AS '))
      {
        continue;
      }

      // might be aggregate function or sub-query
      if (false !== strpos($select, '('))
      {
        continue;
      }

      $foundInGroupBy = false;
      $parts = explode(' AS ', $select, 2);
      foreach ($groupBy as $value)
      {
        if (false !== stripos($value, trim($parts[0])))
        {
          $foundInGroupBy = true;

          break;
        }
      }

      if (!$foundInGroupBy)
      {
        $parts[0] = 'MAX('.trim($parts[0]).')';
      }

      $pos = strpos($query, $select);
      $query = substr($query, 0, $pos).$parts[0].' AS '.$parts[1].substr($query, $pos + strlen($select));
    }

    return $query;
  }

  /**
   * Adds an adapter-specific LIMIT clause to the SELECT statement.
   *
   * This driver choices using ROW_NUMBER() approach instead of super class' one because old approach is too buggy.
   * The target version of SQL Server in OpenPNE is 2008+ so we can free to use ROW_NUMBER() function.
   *
   * @param string $query
   * @param mixed $limit
   * @param mixed $offset
   * @return string
   */
  public function modifyLimitQuery($query, $limit = false, $offset = false, $isManip = false, $isSubQuery = false, Doctrine_Query $queryOrigin = null)
  {
      // NOTE: This method call is not related with a purpose of this method
      $query = $this->replaceNonAggregatedColumnsInSelectList(trim($query), $queryOrigin);

      if ($limit === false || !($limit > 0)) {
          return $query; 
      }

      if (0 !== strpos($query, 'SELECT ')) {
          throw new Doctrine_Connection_Exception("modifyLimitQuery() must handles only SELECT query");
      }

      $orderBy = $queryOrigin->getSqlQueryPart('orderby');
      if ($offset !== false && !$orderBy) {
          throw new Doctrine_Connection_Exception("OFFSET cannot be used in MSSQL without ORDER BY due to emulation reasons.");
      }
      
      $count = intval($limit);
      $offset = intval($offset);

      if ($offset < 0) {
          throw new Doctrine_Connection_Exception("LIMIT argument offset=$offset is not valid");
      }

      if ($orderBy) {
        $orderBySql = ' ORDER BY ' . implode(', ', $orderBy);
        $over = $orderBySql;

        $query = str_replace($orderBySql, '', $query);
      } else {
         // OVER is mandatry so we need to specify dummy query (http://stackoverflow.com/questions/4810627/sql-server-2005-row-number-without-over)
        $over = 'ORDER BY (SELECT 1)';
      }

      $selectionList = preg_split('/,?\s*([^\s]+)\s+AS/', implode(', ', $queryOrigin->getSqlQueryPart('select')), -1, PREG_SPLIT_NO_EMPTY);
      $selectionList = implode(', ', $selectionList);

      $query = substr($query, strlen('SELECT '));
      $query = 'SELECT '.$selectionList.' FROM (SELECT ROW_NUMBER() OVER ('.$over.') AS [op_row_number], '.$query.') AS [op_tmp_row_number_tbl]'
             . ' WHERE [op_row_number] BETWEEN '.($offset + 1).' AND '.($offset + $limit);

      return $query;
  }

    public function quoteIdentifier($str, $checkOption = true)
    {
      if (
        // most-used in Doctrine
        'id' === $str || 'created_at' === $str || 'updated_at' === $str || 'lft' === $str ||
        'rgt' === $str || 'tree_key' === $str || 'level' === $str
        // most-used in OpenPNE
        || 'public_flag' === $str || 'is_active' === $str || 'body' === $str || 'title' === $str
        || 'name' === $str || 'value' === $str
        // won't be identifiers
        || 1 === strlen($str)
        || strpos($str, '__') || strpos($str, '_id'))
      {
        return $str;
      }

      return parent::quoteIdentifier($str, $checkOption);
    }
}
