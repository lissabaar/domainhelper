<?php

include 'data.php'; // all the credential info stored here (not pushed to the repo for security purpose)

function responseHandler($response)
{
    $responseJson = json_decode($response);
    if ($responseJson->success === false) {
        header("Location: /?error=" . $responseJson->errors[0]->message);
        var_dump($responseJson->errors[0]->message);
    } else {
        header("Location: /?success=1");
    };
}

function addDomainsToCloudflare($headers, $domain, $cfAccount, $dnsType)
{
    // adding a domain and getting its id and NS

    $ch = curl_init();

    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POST => 1,
        CURLOPT_URL => 'https://api.cloudflare.com/client/v4/zones/',
        CURLOPT_POSTFIELDS => json_encode([
            "account" => [
                "id" => $cfAccount
            ],
            "name" => $domain,
            "jump_start" => true
        ]),
    );
    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    responseHandler($response);

    $nameServers = json_decode($response)->result->name_servers;
    $domainId = json_decode($response)->result->id;

    curl_close($ch);

    // changing domain's settings

    //create the multiple cURL handle
    $mh = curl_multi_init();

    $chArr = [];

    //getting DNS records array
    $dnsRecords = getDnsRecords($domain, $dnsType);

    // adding DNS records
    for ($i = 0; $i < count($dnsRecords); $i++) {
        $chArr[$i] = curl_init();
        $options = array(
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_URL => 'https://api.cloudflare.com/client/v4/zones/' . $domainId . '/dns_records',
            CURLOPT_POSTFIELDS => json_encode($dnsRecords[$i])
        );
        curl_setopt_array($chArr[$i], $options);
        curl_multi_add_handle($mh, $chArr[$i]);
    }

    // other settings

    $patchRequests = array(
        [
            'endpoint' => 'settings/ssl',
            'value' => 'flexible'
        ],
        [
            'endpoint' => 'settings/security_level',
            'value' => 'low'
        ],
        [
            'endpoint' => 'settings/browser_check', // browser integrity
            'value' => 'off'
        ],
        [
            'endpoint' => 'settings/automatic_https_rewrites',
            'value' => 'off'
        ],
        [
            'endpoint' => 'settings/always_use_https',
            'value' => 'on'
        ],
        [
            'endpoint' => 'settings/brotli',
            'value' => 'off'
        ],
        [
            'endpoint' => 'settings/email_obfuscation',
            'value' => 'off'
        ],

    );

    foreach ($patchRequests as $request) {
        $chArr[$request['endpoint']] = curl_init();
        $options = array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => 'https://api.cloudflare.com/client/v4/zones/' . $domainId . '/' . $request['endpoint'],
            CURLOPT_POSTFIELDS => json_encode(["value" => $request['value']])
        );
        curl_setopt_array($chArr[$request['endpoint']], $options);
        curl_multi_add_handle($mh, $chArr[$request['endpoint']]);
    }

    //execute the multi handle
    do {
        $status = curl_multi_exec($mh, $active);
        if ($active) {
            curl_multi_select($mh);
        }
    } while ($active && $status == CURLM_OK);

    // get content and remove handles
    foreach ($chArr as $ch) {
        $response = curl_multi_getcontent($ch);
        responseHandler($response);
        curl_multi_remove_handle($mh, $ch);
    }

    // close multi handle
    curl_multi_close($mh);

    return $nameServers;
}

if (isset($_POST['domains']) && $_POST['domains']) {
    $nameServers = [];

    $domains = preg_split("/[\s,]+/", $_POST['domains']);

    $cfApiToken = $credentials[$_POST['cf_account']]['token'];
    $cfAccount = $credentials[$_POST['cf_account']]['accountId'];
    $dnsType = $credentials[$_POST['cf_account']]['dnsType'];

    $headers = [
        'Authorization: Bearer ' . $cfApiToken,
        'Content-Type: application/json'
    ];

    for ($i = 0; $i < count($domains); $i++) {
        $nameServers[$domains[$i]] = addDomainsToCloudflare($headers, $domains[$i], $cfAccount, $dnsType);
        // executing the main function addDomainsToCloudflare for each domain and returning NS
    }

    // TODO: instead of a file, show NSs on the helper page
    $file = 'name_servers.txt';
    file_put_contents($file, print_r($nameServers, true));
} else {
    header("Location: /?error=No%20domains");
}
