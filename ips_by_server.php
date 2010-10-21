<?php

/**
* Get a list of all the public ips per server for each server in an account.
* This does not include portable ips which are instead routed to a vlan rather than a server
**/

require_once(dirname(__FILE__).'/common.php');

$ips = array();

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
		banner($server->hostname);
		$subnets = array();
		if (!empty($server->primaryIpAddress)) {
			echo $server->primaryIpAddress."\n";
		}

		foreach ($server->networkComponents as $components) {
			foreach ($components->subnets as $subnet) {
				foreach ($subnet->ipAddresses as $ipaddr) {
					echo $ipaddr->ipAddress."\n";
				}
			}
		}
	}

}
catch (Exception $e) {
	echo $e->getMessage()."\n";
}




