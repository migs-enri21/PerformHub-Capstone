<?php

return [
    'categories' => [
        'singer' => [
            'specialties' => ['Vocals', 'Guitar', 'Piano', 'Violin', 'Drums'],
            'genres' => ['Pop', 'Rock', 'Acoustic', 'OPM', 'R&B', 'Soul', 'Jazz', 'Blues', 'Classical', 'Gospel', 'Ballad'],
        ],
        'guitarist' => [
            'specialties' => ['Acoustic Guitar', 'Electric Guitar', 'Bass Guitar', 'Guitar'],
            'genres' => ['Rock', 'Pop', 'Acoustic', 'Blues', 'Jazz', 'Classical', 'Country', 'Folk', 'OPM'],
        ],
        'violinist' => [
            'specialties' => ['Violin', 'Classical Violin', 'Electric Violin'],
            'genres' => ['Classical', 'Pop', 'Rock', 'Jazz', 'Folk', 'Cultural / Traditional', 'Gospel'],
        ],
        'saxophonist' => [
            'specialties' => ['Alto Saxophone', 'Tenor Saxophone', 'Soprano Saxophone', 'Baritone Saxophone'],
            'genres' => ['Jazz', 'Blues', 'Pop', 'Rock', 'R&B', 'Soul', 'Classical'],
        ],
        'rapper' => [
            'specialties' => ['Rap Vocals', 'Freestyle Rap', 'Hip-Hop MC'],
            'genres' => ['Hip-Hop', 'Rap', 'R&B', 'Pop', 'K-Pop', 'OPM'],
        ],
        'dancers' => [
            'specialties' => ['Contemporary Dance', 'Hip-Hop Dance', 'Ballet', 'Modern Dance', 'Cultural Dance', 'Street Dance'],
            'genres' => ['Contemporary Dance', 'Hip-Hop Dance', 'Ballet', 'Modern Dance', 'Cultural / Traditional', 'Street Dance'],
        ],
    ],
    'fallback' => [
        'specialties' => ['Vocals', 'Guitar', 'Piano', 'Violin', 'Drums', 'DJ', 'Dance', 'Comedy', 'Magic', 'MC / Hosting'],
        'genres' => [],
    ],
    'specialties' => [
        'vocals' => ['Pop', 'Rock', 'Acoustic', 'OPM', 'R&B', 'Soul', 'Jazz', 'Blues', 'Classical', 'Gospel', 'Ballad'],
        'guitar' => ['Rock', 'Pop', 'Acoustic', 'Blues', 'Jazz', 'Classical', 'Country', 'Folk', 'OPM'],
        'acoustic guitar' => ['Rock', 'Pop', 'Acoustic', 'Blues', 'Jazz', 'Classical', 'Country', 'Folk', 'OPM'],
        'electric guitar' => ['Rock', 'Pop', 'Blues', 'Jazz', 'OPM'],
        'bass guitar' => ['Rock', 'Pop', 'Jazz', 'Blues', 'R&B', 'OPM'],
        'piano' => ['Pop', 'Classical', 'Jazz', 'Blues', 'Soul', 'R&B', 'Gospel', 'Ballad'],
        'violin' => ['Classical', 'Pop', 'Rock', 'Jazz', 'Folk', 'Cultural / Traditional', 'Gospel'],
        'drums' => ['Rock', 'Pop', 'Jazz', 'Blues', 'Hip-Hop', 'R&B', 'OPM'],
    ],
];