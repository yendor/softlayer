<?php

require_once dirname(__FILE__).'/common.php';

$client = SoftLayer_SoapClient::getClient('SoftLayer_Account', null, Config::USERNAME, Config::KEY);

$ips = array();

try {
    $servers = $client->getHardware();
	foreach ($servers as $server) {
		if (empty($server->primaryIpAddress)) {
			continue;
		}
		$cidr = $server->primaryIpAddress."/32";
		$ipEnum = new IpEnumerator($cidr);
		foreach ($ipEnum as $ip) {
			echo "$ip\n";
		}
	}

	$publicNetworks = $client->getPublicSubnets();

	foreach ($publicNetworks as $network) {
		$cidr = $network->networkIdentifier.'/'.$network->cidr;
		$ipEnum = new IpEnumerator($cidr);
		foreach ($ipEnum as $ip) {
			echo "$ip\n";
		}
	}
} catch (Exception $e) {
	die($e->getMessage());
}
