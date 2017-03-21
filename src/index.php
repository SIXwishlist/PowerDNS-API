<?php
/**
 * PowerDNS API.
 *
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license http://www.wtfpl.net/about/ The Do What The Fuck You Want To Public License (WTFPL)
 * @author CyanDark, Inc <support@cyandark.com>
 */
include 'config.php';
include 'PowerDNS.php';

header('Content-Type: text/plain');

$PowerDNS = new CyanDark\Api\PowerDNS($db['host'], $db['user'], $db['pass'], $db['name'], $db['port']);
$output   = json_encode(['status' => 'error', 'msg' => 'Unauthorized']);

// Verifiy API Key
if ($_GET['key'] == $api['key']) {
    // Default Output
    $output = json_encode(['status' => 'error', 'msg' => 'Action not defined.']);

    // Add Domain
    if ($_GET['action'] == 'add_domain') {
        if (!empty($_GET['domain'])) {
            if ($PowerDNS->isValidDomain($_GET['domain']) && count(explode('.', $_GET['domain'])) > 1) {
                $response = $PowerDNS->addDomain($_GET['domain'], $_GET['solusvm_cid']);
                $output   = json_encode($response);
            } else {
                $output = json_encode(['status' => 'error', 'msg' => 'Invalid Domain.']);
            }
        } else {
            $output = json_encode(['status' => 'error', 'msg' => 'Missing data for add_domain.']);
        }
    }

    // Delete Domain
    if ($_GET['action'] == 'delete_domain') {
        if (!empty($_GET['domain_id'])) {
            $response = $PowerDNS->deleteDomain($_GET['domain_id']);
            $output   = json_encode($response);
        } else {
            $output = json_encode(['status' => 'error', 'msg' => 'Missing data for delete_domain.']);
        }
    }

    // Get Domain ID
    if ($_GET['action'] == 'get_domain_id') {
        if (!empty($_GET['domain'])) {
            if ($PowerDNS->isValidDomain($_GET['domain'])) {
                $response = $PowerDNS->getDomainID($_GET['domain']);
                $output   = json_encode($response);
            } else {
                $output = json_encode(['status' => 'error', 'msg' => 'Invalid Domain.']);
            }
        } else {
            $output = json_encode(['status' => 'error', 'msg' => 'Missing data for get_domain_id.']);
        }
    }

    // Add Record
    if ($_GET['action'] == 'add_record') {
        if (!empty($_GET['domain_id']) && !empty($_GET['name']) && !empty($_GET['type']) && !empty($_GET['content']) && !empty($_GET['ttl'])) {
            $response = $PowerDNS->addRecord($_GET['domain_id'], $_GET['name'], $_GET['type'], $_GET['content'], $_GET['ttl'], $_GET['prio']);
            $output   = json_encode($response);
        } else {
            $output = json_encode(['status' => 'error', 'msg' => 'Missing data for add_record.']);
        }
    }

    // Delete Record
    if ($_GET['action'] == 'delete_record') {
        if (!empty($_GET['record_id'])) {
            $response = $PowerDNS->deleteRecord($_GET['record_id']);
            $output   = json_encode($response);
        } else {
            $output = json_encode(['status' => 'error', 'msg' => 'Missing data for delete_record.']);
        }
    }

    // Get Domains by SolusVM Container ID
    if ($_GET['action'] == 'get_domains_by_solusvmid') {
        if (!empty($_GET['solusvm_cid'])) {
            $response = $PowerDNS->getDomainsBySolusVMID($_GET['solusvm_cid']);
            $output   = json_encode($response);
        } else {
            $output = json_encode(['status' => 'error', 'msg' => 'Missing data for get_domains_by_solusvmid.']);
        }
    }

    // Get Records by Domain ID
    if ($_GET['action'] == 'get_records_by_domain_id') {
        if (!empty($_GET['domain_id'])) {
            $response = $PowerDNS->getRecordsByDomainID($_GET['domain_id']);
            $output   = json_encode($response);
        } else {
            $output = json_encode(['status' => 'error', 'msg' => 'Missing data for get_records_by_domain_id.']);
        }
    }

    // Create solusvm_cid Row
    if ($_GET['action'] == 'create_solus_vmid') {
        $response = $PowerDNS->createSolusVMID();
        $output   = json_encode($response);
    }
}

// Print Output
echo $output;
