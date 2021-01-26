<?php

$summary = [
    'title' => 'Covid-19 pandemijos statistika',
    'updated' => 'Atnaujinta',
    'country' => 'Šalis',
    'empty' => 'Duomenų nėra',
    'all' => 'Visi',
    'no_data' => 'Duomenų nėra',
    'population' => 'Populiacija',
    'area' => 'Plotas',
    'capital' => 'Sostinė',
    'not_available' => '[negalima]',
    'search' => 'Paieška'
];

$types = [
    'confirmed' => 'atvejai',
    'deaths' => 'mirtys',
    'recovered' => 'pasveikusieji'
];

$per_capita = '100 tūkst gyv.';

foreach ($types as $index => $type) {
    $new = 'Nauj';
    if (substr($type, -1) !== 's') {
        $new .= 'i';
    } else {
        $new .= 'os';
    }


    $summary['new_' . $index] = $new . ' ' . $type;
    $summary['new_' . $index . '_per_capita'] = $new . ' ' . $type . ' ' . $per_capita;
    $summary['total_' . $index] = $type;
    $summary['total_' . $index . '_per_capita'] = $type . ' ' . $per_capita;
}

return $summary;
