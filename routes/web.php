<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/redirect', function (Request $request) {
//     $request->session()->put('state', $state = Str::random(40));
 
//     $query = http_build_query([
//         'client_id' => env('PASSPORT_CLIENT_ID'),
//         'redirect_uri' => 'http://127.0.0.1:8001/callback',
//         'response_type' => 'code',
//         'scope' => '',
//         'state' => $state,
//     ]);
 
//     return redirect('http://127.0.0.1:8000/oauth/authorize?'.$query);
// });

// Route::get('/callback', function (Request $request) {
//     $state = session()->get('state');
 
//     dd(session('state'));

//     throw_unless(
//         strlen($state) > 0 && $state === $request->state,
//         InvalidArgumentException::class
//     );
 
//     $response = Http::asForm()->post('http://127.0.0.1:8000/oauth/token', [
//         'grant_type' => 'authorization_code',
//         'client_id' => env('PASSPORT_CLIENT_ID'),
//         'client_secret' => env('PASSPORT_CLIENT_SECRET'),
//         'redirect_uri' => 'http://127.0.0.1:8001/callback',
//         'code' => $request->code,
//     ]);
 
//     return $response->json();
// });

Route::get('/redirect', function (Request $request) {
    // $request->session()->put('state', $state = Str::random(40));

    $state = Str::random(40);
    $code_verifier = Str::random(128);

    Cookie::queue(cookie('state', $state, 30));
    Cookie::queue(cookie('code_verifier', $code_verifier, 30));
 
    // $request->session()->put(
    //     'code_verifier', $code_verifier = Str::random(128)
    // );
 
    $codeChallenge = strtr(rtrim(
        base64_encode(hash('sha256', $code_verifier, true))
    , '='), '+/', '-_');
 
    $query = http_build_query([
        'client_id' => env('PASSPORT_CLIENT_ID'),
        'redirect_uri' => 'http://127.0.0.1:8888/callback',
        'response_type' => 'code',
        'scope' => '',
        'state' => $state,
        'code_challenge' => $codeChallenge,
        'code_challenge_method' => 'S256',
    ]);
 
    return redirect('http://127.0.0.1:8000/oauth/authorize?'.$query);
})->name('redirect');

Route::get('/callback', function (Request $request) {
    // $state = $request->session()->pull('state');

    // $codeVerifier = $request->session()->pull('code_verifier');

    $state = $request->cookie('state');
    $codeVerifier = $request->cookie('code_verifier');
 
    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class
    );
 
    $response = Http::asForm()->post('http://127.0.0.1:8000/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => env('PASSPORT_CLIENT_ID'),
        'redirect_uri' => 'http://127.0.0.1:8888/callback',
        'code_verifier' => $codeVerifier,
        'code' => $request->code,
    ]);

    $accessTokenResult = $response->json();

    Cookie::queue(cookie('access_token', $accessTokenResult['access_token'], 9999));

    // return $accessTokenResult;
    
    return redirect()->intended('/get-products-from-api');
})->name('callback');

Route::get('/get-products-from-api', function (Request $request) {
    $accessToken = $request->cookie('access_token');

    if ($accessToken === null) {
        return redirect()->route('redirect');
    }

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $accessToken
    ])->get('http://127.0.0.1:8000/api/products');
 
    return $response->json();
})->name('getProducts');

Route::get('/delete-cookies', function() {
    Cookie::queue(Cookie::forget('state'));
    Cookie::queue(Cookie::forget('code_verifier'));
    Cookie::queue(Cookie::forget('access_token'));

    return [
        'message' => 'Cookies deleted!'
    ];
});