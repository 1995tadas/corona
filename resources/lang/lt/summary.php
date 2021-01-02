<?php

$summary = [
    'title' => 'Covid-19 pandemijos statistika',
    'updated' => 'Atnaujinta',
    'country' => 'Šalis',
    'empty' => 'Duomenų nėra'
];

$types = [
    'confirmed' => 'atvejai',
    'deaths' => 'mirtys',
    'recovered' => 'pasveikimai'
];

foreach ($types as $index => $type) {
    $new = 'Nauj';
    if (substr($type, -1) !== 's') {
        $new .= 'i';
    } else {
        $new .= 'os';
    }

    $summary['new_' . $index] = $new . ' ' . $type;
    $summary['total_' . $index] = $type;
}

return $summary;
