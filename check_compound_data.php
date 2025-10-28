<?php
$url = "https://www.nawy.com/compound/55-palm-hills-katameya-(pk1)";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0',
    CURLOPT_TIMEOUT => 30,
]);
$html = curl_exec($ch);
curl_close($ch);

if (preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches)) {
    $data = json_decode($matches[1], true);
    $compound = $data['props']['pageProps']['compound'];
    
    echo "═══════════════════════════════════════════════════════════\n";
    echo "Compound: " . $compound['name'] . "\n";
    echo "Developer: " . $compound['developerName'] . "\n";
    echo "Location: " . $compound['areaName'] . "\n";
    echo "Coordinates: " . $compound['lat'] . ", " . $compound['long'] . "\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "\n📸 Images from Nawy.com: " . count($compound['images']) . " images\n";
    echo "───────────────────────────────────────────────────────────\n";
    foreach($compound['images'] as $i => $img) {
        echo "  [" . ($i+1) . "] " . basename($img) . "\n";
    }
    echo "\n📐 Master Plan: " . (!empty($compound['masterPlan']) ? 'YES' : 'NO') . "\n";
    if (!empty($compound['masterPlan'])) {
        echo "    " . basename($compound['masterPlan']) . "\n";
    }
    echo "\n═══════════════════════════════════════════════════════════\n";
} else {
    echo "Failed to extract data\n";
}
