<?php
header('Content-Type: application/json; charset=utf-8');

$targetUrl = 'https://recyclingpaperarts.com/';

function jsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$ch = curl_init($targetUrl);
if ($ch === false) {
    jsonResponse(500, [
        'success' => false,
        'error' => 'Failed to initialize cURL.'
    ]);
}

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 3,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_USERAGENT => 'COSCO-Fetcher/1.0 (+https://recyclingpaperarts.com/)'
]);

$html = curl_exec($ch);
$curlError = curl_error($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($html === false || $statusCode >= 400) {
    jsonResponse(502, [
        'success' => false,
        'error' => 'Unable to fetch remote page.',
        'details' => $curlError ?: ('HTTP ' . $statusCode)
    ]);
}

libxml_use_internal_errors(true);
$doc = new DOMDocument();
$loaded = $doc->loadHTML($html);
libxml_clear_errors();

if (!$loaded) {
    jsonResponse(500, [
        'success' => false,
        'error' => 'Failed to parse remote HTML.'
    ]);
}

$xpath = new DOMXPath($doc);

$title = trim($xpath->evaluate('string(//h1[1])'));
if ($title === '') {
    $title = trim($xpath->evaluate('string(//title[1])'));
}

$tagline = trim($xpath->evaluate('string(//h1[1]/following::p[1])'));
$welcomeTitle = trim($xpath->evaluate('string(//*[self::h2 or self::h3][contains(translate(normalize-space(.), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "welcome")][1])'));

$featureNodes = $xpath->query('//ul[1]/li');
$features = [];
if ($featureNodes instanceof DOMNodeList) {
    foreach ($featureNodes as $node) {
        $text = trim($node->textContent ?? '');
        if ($text !== '') {
            $features[] = $text;
        }
    }
}

$adminPortal = '';
$adminNode = $xpath->query('//a[contains(., "Admin Portal")][1]');
if ($adminNode instanceof DOMNodeList && $adminNode->length > 0) {
    $href = $adminNode->item(0)->attributes?->getNamedItem('href')?->nodeValue;
    if (is_string($href)) {
        $adminPortal = trim($href);
    }
}

jsonResponse(200, [
    'success' => true,
    'source' => $targetUrl,
    'fetched_at' => gmdate('c'),
    'data' => [
        'title' => $title,
        'tagline' => $tagline,
        'welcome' => $welcomeTitle,
        'features' => $features,
        'admin_portal_link' => $adminPortal
    ]
]);
