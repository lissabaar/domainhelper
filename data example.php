<?php

$credentials = [
    // an array in case you have different accounts and/or projects for different purposes like i do
    [
        'token' => '', // can be created in your Cloudflare account
        'accountId' => '', // can be found in your Cloudflare account
        'name' => '', // something that'll show up in a dropdown menu
        'dnsType' => '' // needed if you have different DNS records for differents accounts
    ],
];

function getDnsRecords($domain, $dnsType)
// $dnsType is the param you get from the $credentials
{
    // there could be any amount of DNS records of any type
    switch ($dnsType) {
        case 'name1':
            return [
                [
                    'name' => $domain,
                    'content' => '',
                    'type' => '',
                    'proxied' => true,
                ],
            ];
            break;
    }
}
