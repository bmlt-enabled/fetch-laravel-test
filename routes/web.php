<?php

use Illuminate\Support\Facades\Route;
use FetchMeditation\JFT;
use FetchMeditation\JFTSettings;
use FetchMeditation\JFTLanguage;
use FetchMeditation\SPAD;
use FetchMeditation\SPADSettings;
use FetchMeditation\SPADLanguage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/jft', function () {
    $settings = new JFTSettings(JFTLanguage::English);
    $jft = JFT::getInstance($settings);
    $data = $jft->fetch();
    $parts = collect([
        $data->date,
        $data->title,
        $data->page,
        $data->quote,
        $data->source,
        ...collect($data->content)->map(fn($p) => strip_tags(html_entity_decode($p))),
        $data->thought,
        $data->copyright
    ])
        ->filter(fn($part) => !empty(trim($part)))
        ->values();
    return $parts->join('<br /><br />');
});

Route::get('/spad', function () {
    $settings = new SPADSettings(SPADLanguage::English);
    $spad = SPAD::getInstance($settings);
    $meditation = $spad->fetch();
    return response()->json($meditation);
});
