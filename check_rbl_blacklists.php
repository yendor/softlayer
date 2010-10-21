<?php

require_once(dirname(__FILE__).'/common.php');

$client = SoftLayer_SoapClient::getClient('SoftLayer_Account', null, Config::USERNAME, Config::KEY);

$cidrs = array();

try {
    $servers = $client->getHardware();
	foreach ($servers as $server) {
		if (empty($server->primaryIpAddress)) {
			continue;
		}
		$cidrs[] = $server->primaryIpAddress."/32";
	}

	$publicNetworks = $client->getPublicSubnets();

	foreach ($publicNetworks as $network) {
		$cidrs[] = $network->networkIdentifier.'/'.$network->cidr;
	}
} catch (Exception $e) {
	die($e->getMessage());
}

$blacklist_servers = file(dirname(__FILE__).'/dnsbl.txt');

if(!function_exists('dns_get_record')) {
	die("ERROR: The dns_get_record function is required for this script");
}

$blacklist_count = 0;
$total_count = 0;

$ip_count = 0;

fwrite(STDOUT, 'DNSBL Report for '.date('Y-m-d')."\n");
fwrite(STDOUT, str_repeat('-', strlen($banner))."\n");
foreach ($cidrs as $cidr) {
	$ipEnum = new IpEnumerator($cidr);
	foreach ($ipEnum as $ip) {
		++$ip_count;
		foreach ($blacklist_servers as $blacklist_server) {
			$blacklist_server = trim($blacklist_server);

			if (!$blacklist_server) {
				continue;
			}

			++$total_count;
			$result = check_blacklist($blacklist_server, $ip);
			$report = '';

			if($result !== false) {
				$report .= "$ip is listed on $blacklist_server\n";
				if($result) {
				 	$report .= "\tReason: ".$result."\n";
				}
				else {
					$report .= "\tReason: No reason\n";
				}
				fwrite(STDOUT, $report);
				++$blacklist_count;
			}
		}
	}
}

fwrite(STDOUT, "Total checked: ".$ip_count." IP addresses over ".count($blacklist_servers)." DNSBL servers\n");
fwrite(STDOUT, "Blacklist count: $blacklist_count blacklist records exist\n");

if($blacklist_count == 0) {
	exit;
}

fwrite(STDOUT, "\n");
fwrite(STDOUT, $report);

function check_blacklist($server, $ip)
{
	$reverse_ip = implode('.', array_reverse(explode('.', $ip)));

	$a_record = @dns_get_record($reverse_ip.'.'.$server.'.', DNS_A);
	if(!is_array($a_record) || !isset($a_record[0]['host'])) {
		return false;
	}

	// Attempt to grab the reason from the TXT record
	$txt_record = dns_get_record($reverse_ip.'.'.$server.'.', DNS_TXT);
	if(!is_array($txt_record)) {
		return true;
	}
	else {
		return $txt_record[0]['txt'];
	}
}
