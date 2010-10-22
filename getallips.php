<?php

/**
* Get a list of all the public ips in an account that are routable.
**/

require_once dirname(__FILE__).'/common.php';

$client = SoftLayer_SoapClient::getClient('SoftLayer_Account', null, Config::USERNAME, Config::KEY);

$objectMask = new SoftLayer_ObjectMask();
$objectMask->hardware;
$objectMask->hardware->networkComponents;
$objectMask->hardware->networkComponents->subnets;
$objectMask->hardware->networkComponents->subnets->ipAddresses;

try {
	$client->setObjectMask($objectMask);
	$data = $client->getObject();

	foreach ($data->hardware as $server) {
		foreach ($server->networkComponents as $components) {
			foreach ($components->subnets as $subnet) {
				foreach ($subnet->ipAddresses as $ipaddr) {
					echo $ipaddr->ipAddress."\n";
				}
			}
		}
	}

	$publicNetworks = $client->getPublicSubnets();

	foreach ($publicNetworks as $network) {
		$cidr = $network->networkIdentifier.'/'.$network->cidr;
		$ipEnum = new IpEnumerator($cidr);
		if ($network->subnetType != 'SECONDARY_ON_VLAN') {
			continue;
		}

		foreach ($ipEnum as $ip) {
			if (in_array($network->subnetType, array('PRIMARY', 'ADDITIONAL_PRIMARY', 'SECONDARY_ON_VLAN'))) {
				if ($ip == $network->networkIdentifier || $ip == $network->broadcastAddress || $ip == $network->gateway) {
					continue;
				}
			}
			echo $ip."\n";
		}
	}
} catch (Exception $e) {
	die($e->getMessage());
}
