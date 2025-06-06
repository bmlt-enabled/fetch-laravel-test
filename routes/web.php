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
    $meditation = $jft->fetch();
    return response()->json($meditation);
});

Route::get('/spad', function () {
    $settings = new SPADSettings(SPADLanguage::English);
    $spad = SPAD::getInstance($settings);
    $meditation = $spad->fetch();
    return response()->json($meditation);
});
