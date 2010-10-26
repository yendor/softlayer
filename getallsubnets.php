<?php

require_once(dirname(__FILE__).'/common.php');

$client = SoftLayer_SoapClient::getClient('SoftLayer_Account', null, Config::USERNAME, Config::KEY);

try {
	$publicNetworks = $client->getPublicSubnets();

	foreach ($publicNetworks as $network) {
		$cidr = $network->networkIdentifier.'/'.$network->cidr;
		echo "$cidr\n";
	}
} catch (Exception $e) {
	die($e->getMessage());
}


