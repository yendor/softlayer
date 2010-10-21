<?php

require dirname(__FILE__).'/softlayer-api-php-client/Softlayer/SoapClient.class.php';
require_once dirname(__FILE__).'/ipenumerator/ipenumerator.php';
require_once dirname(__FILE__).'/config.php';

ini_set('memory_limit', '256M');

date_default_timezone_set(Config::TIMEZONE);

function banner($msg)
{
	echo str_repeat('=', 79)."\n";
	echo $msg."\n";
	echo str_repeat('=', 79)."\n";
}

