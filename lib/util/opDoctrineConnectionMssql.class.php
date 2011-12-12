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
}
