<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Zttp\Zttp;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Openweathermap API Key in Laravel backend for security
| Zttp for simple wrapper
| 
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/weather', function () {
    $apiKey = config('services.openweathermap.key');
    $lat = request('lat');
    $lon = request('lon');
    $location = request('location');

    $response = Zttp::get("https://api.openweathermap.org/data/2.5/onecall?lat=$lat&lon=$lon&exclude=minutely,hourly&units=metric&appid=$apiKey");

    return $response->json();
});
