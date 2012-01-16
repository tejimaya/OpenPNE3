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

      $count = intval($limit);
      $offset = intval($offset);

      if ($offset < 0) {
          throw new Doctrine_Connection_Exception("LIMIT argument offset=$offset is not valid");
      }

      if ($queryOrigin)
      {
        $select = implode(', ', $queryOrigin->getSqlQueryPart('select'));
        $orderBy = $queryOrigin->getSqlQueryPart('orderby');
      }
      else
      {
        $select = $this->extractSelect(stristr($query, 'SELECT'));
        $orderBy = $this->extractOrderBy(stristr($query, 'ORDER BY'));
      }

      if ($orderBy) {
        $orderBySql = ' ORDER BY ' . implode(', ', $orderBy);
        $over = $orderBySql;

        $query = str_replace($orderBySql, '', $query);
      } else {
         // OVER is mandatry so we need to specify dummy query (http://stackoverflow.com/questions/4810627/sql-server-2005-row-number-without-over)
        $over = 'ORDER BY (SELECT 1)';
      }

      $selectionList = preg_split('/,?\s*([^\s]+)\s+AS/', $select, -1, PREG_SPLIT_NO_EMPTY);
      $selectionList = implode(', ', $selectionList);

      // replace items of selection list if the item doesn't have alias
      $selectionList = preg_replace('/[^ ]+\.([^ ]),?/', ' [op_tmp_row_number_tbl].$1', $selectionList);

      $query = substr($query, strlen('SELECT '));
      if (0 === strpos(trim($query), 'DISTINCT'))
      {
        $query = substr(trim($query), strlen('DISTINCT'));
        if (false === strpos($selectionList, 'DISTINCT'))
        {
          $selectionList = ' DISTINCT '.$selectionList;
        }
      }
      $query = 'SELECT '.$selectionList.' FROM (SELECT ROW_NUMBER() OVER ('.$over.') AS [op_row_number], '.$query.') AS [op_tmp_row_number_tbl]'
             . ' WHERE [op_row_number] BETWEEN '.($offset + 1).' AND '.($offset + $limit);

      return $query;
  }

  // copied from Doctrine_Connection_Mssql::parseOrderBy() (private method)
  protected function extractOrderBy($orderby)
  {
    $matches = array();
    $chunks  = array();
    $tokens  = array();
    $parsed  = str_ireplace('ORDER BY', '', $orderby);

    preg_match_all('/(\w+\(.+?\)\s+(ASC|DESC)),?/', $orderby, $matches);
    
    $matchesWithExpressions = $matches[1];

    foreach ($matchesWithExpressions as $match) {
        $chunks[] = $match;
        $parsed = str_replace($match, '##' . (count($chunks) - 1) . '##', $parsed);
    }
    
    $tokens = preg_split('/,/', $parsed);
    
    for ($i = 0, $iMax = count($tokens); $i < $iMax; $i++) {
        $token = trim(preg_replace('/##(\d+)##/e', "\$chunks[\\1]", $tokens[$i]));
        if ('' === $token) {
            unset($tokens[$i]);
        } else {
            $tokens[$i] = $token;
        }
    }

    return $tokens;
  }

  protected function extractSelect($sql)
  {
    if (!preg_match('/SELECT(.*)FROM/', $sql, $matches))
    {
      throw new RuntimeException('Non-SELECT query is not supported');
    }

    return $matches[1];
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

  public function convertPhpValueByDbType($value, $type)
  {
    if ('blob' === $type)
    {
      // TODO: Don't use raw file handler because OpenPNE can't close this in suitable timing
      $fp = fopen('php://temp', 'rb+');
      fwrite($fp, $value);
      rewind($fp);

      return $fp;
    }

    return $value;
  }

  /**
   * Replaces bound parameters and their placeholders with explicit values.
   *
   * Workaround for http://bugs.php.net/36561
   *
   * @param string $query
   * @param array $params
   */
  protected function replaceBoundParamsWithInlineValuesInQuery($query, array $params)
  {
    foreach($params as $key => $value)
    {
      $re = '/(?<=WHERE|VALUES|SET|JOIN)(.*?)(\?)/';
      $query = preg_replace($re, "\\1##{$key}##", $query, 1);
    }

    $replacement = 'is_null($value) ? \'NULL\' : $this->quote($params[\\1])';
    $conn = $this;
    $query = preg_replace_callback('/##(\d+)##/', function ($matches) use ($value, $params, $conn) {
      // This might be a mistake of the original code
      if (is_null($value))
      {
        return 'NULL';
      }

      $_value = $params[$matches[1]];

      if (is_resource($_value))
      {
        $result = '0x'.bin2hex(stream_get_contents($_value));

        fclose($_value);

        return $result;
      }

      if (is_object($_value) && is_callable(array($_value, '__toString')))
      {
        $_value = (string)$_value;
      }

      $result = $conn->quote($_value);
      if (is_string($_value) && !ctype_digit($_value))
      {
        $result = 'N'.$result;
      }

      return $result;
    }, $query);

    return $query;
  }
}
