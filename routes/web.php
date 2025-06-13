<?php

use Illuminate\Support\Facades\Route;
use FetchMeditation\JFT;
use FetchMeditation\JFTSettings;
use FetchMeditation\JFTLanguage;
use FetchMeditation\SPAD;
use FetchMeditation\SPADSettings;
use FetchMeditation\SPADLanguage;

function formatMeditation($data, $returnJson = false)
{
    if ($returnJson || request()->wantsJson()) {
        return response($data->toJson())->header('Content-Type', 'application/json');
    }

    $parts = collect([
        $data->date ?? null,
        $data->title ?? null,
        $data->page ?? null,
        $data->quote ?? null,
        $data->source ?? null,
        ...collect($data->content ?? [])->map(fn($p) => strip_tags(html_entity_decode($p))),
        $data->thought ?? null,
        $data->copyright ?? null
    ])
        ->filter(fn($part) => !empty(trim($part)))
        ->values();
    return $parts->join('<br /><br />');
}

Route::get('/', function () {
    $availableBooks = [
        'jft' => [
            'name' => 'Just For Today',
            'languages' => [
                'english' => 'English',
                'spanish' => 'Spanish',
                'french' => 'French',
                'german' => 'German',
                'italian' => 'Italian',
                'japanese' => 'Japanese',
                'portuguese' => 'Portuguese',
                'russian' => 'Russian',
                'swedish' => 'Swedish',
            ],
            'base_url' => '/jft'
        ],
        'spad' => [
            'name' => 'Spiritual Principle A Day',
            'languages' => [
                'english' => 'English',
                'german' => 'German',
            ],
            'base_url' => '/spad'
        ]
    ];
    
    if (request()->wantsJson() || request()->query('json')) {
        return response()->json($availableBooks);
    }

    $html = '<h1>Available Meditation Books</h1>';
    foreach ($availableBooks as $key => $book) {
        $html .= "<h2>{$book['name']} ({$key})</h2>";
        $html .= "<p>Available languages:</p><ul>";
        foreach ($book['languages'] as $code => $name) {
            $html .= "<li><a href=\"{$book['base_url']}/{$code}\">{$name}</a></li>";
        }
        $html .= "</ul>";
    }
    
    return $html;
});

Route::get('/jft', function () {
    $settings = new JFTSettings(JFTLanguage::English);
    $jft = JFT::getInstance($settings);
    $data = $jft->fetch();
    return formatMeditation($data, request()->query('json'));
});

Route::get('/jft/{language}', function ($language) {
    $langMap = [
        'english' => JFTLanguage::English,
        'spanish' => JFTLanguage::Spanish,
        'french' => JFTLanguage::French,
        'german' => JFTLanguage::German,
        'italian' => JFTLanguage::Italian,
        'japanese' => JFTLanguage::Japanese,
        'portuguese' => JFTLanguage::Portuguese,
        'russian' => JFTLanguage::Russian,
        'swedish' => JFTLanguage::Swedish,
    ];

    $language = strtolower($language);
    if (!isset($langMap[$language])) {
        abort(404, 'Language not supported');
    }

    $settings = new JFTSettings($langMap[$language]);
    $jft = JFT::getInstance($settings);
    $data = $jft->fetch();
    
    return formatMeditation($data, request()->query('json'));
});

Route::get('/spad', function () {
    $settings = new SPADSettings(SPADLanguage::English);
    $spad = SPAD::getInstance($settings);
    $meditation = $spad->fetch();
    
    return formatMeditation($meditation, request()->query('json'));
});

Route::get('/spad/{language}', function ($language) {
    $langMap = [
        'english' => SPADLanguage::English,
        'german' => SPADLanguage::German,
    ];

    $language = strtolower($language);
    if (!isset($langMap[$language])) {
        abort(404, 'Language not supported');
    }

    $settings = new SPADSettings($langMap[$language]);
    $spad = SPAD::getInstance($settings);
    $meditation = $spad->fetch();
    
    return formatMeditation($meditation, request()->query('json'));
});
