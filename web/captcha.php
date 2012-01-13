<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

$rootPath = isset($_SERVER['AZURE_ROLE_ROOT']) ? $_SERVER['AZURE_ROLE_ROOT'].'/approot' : dirname(__FILE__).'/..';

require_once($rootPath.'/config/ProjectConfiguration.class.php');

// we can use session after loading factories (don't need to dispatch controller)
$configuration = ProjectConfiguration::getApplicationConfiguration('pc_frontend', 'prod', false);
sfContext::createInstance($configuration);

require_once(sfConfig::get('sf_data_dir').'/kcaptcha/kcaptcha.php');
$captcha = new KCAPTCHA();
$_SESSION['captcha_keystring'] = $captcha->getKeyString();
