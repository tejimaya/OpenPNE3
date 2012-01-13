<?php

$rootPath = isset($_SERVER['AZURE_ROLE_ROOT']) ? $_SERVER['AZURE_ROLE_ROOT'].'/approot' : dirname(__FILE__).'/..';

require_once($rootPath.'/config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('api', 'prod', false);
sfContext::createInstance($configuration)->dispatch();
