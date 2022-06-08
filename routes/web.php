<?php

use App\Http\Controllers\UserController;
use App\Models\PassportAccessToken;
use App\Models\PassportClient;
use App\Models\PassportVerifier;
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

Route::controller(UserController::class)->group(function() {
    Route::middleware('guest')->group(function() {
        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'authenticate')->name('authenticate');
        Route::get('/register', 'register')->name('register');
        Route::post('/register', 'store')->name('store');
    });

    Route::middleware('auth')->group(function() {
        Route::get('/logout', 'logout')->name('logout');
    });
});

Route::middleware('auth')->group(function() {
    Route::get('/redirect', function (Request $request) {
        $user = auth()->user();
        $state = Str::random(40);
        $codeVerifier = Str::random(128);
        
        PassportVerifier::create([
            'user_id' => $user->id,
            'state' => $state,
            'code_verifier' => $codeVerifier
        ]);
    
        // $request->session()->put('state', $state = Str::random(40));
        // $request->session()->put(
        //     'code_verifier', $code_verifier = Str::random(128)
        // );
     
        $codeChallenge = strtr(rtrim(
            base64_encode(hash('sha256', $codeVerifier, true))
        , '='), '+/', '-_');
     
        $query = http_build_query([
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'redirect_uri' => 'http://localhost:8888/callback',
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);
    
        return redirect('http://localhost:8000/oauth/authorize?'.$query);
    })->name('redirect');
    
    Route::get('/callback', function (Request $request) {
        $user = auth()->user();
        $verifier = PassportVerifier::where('user_id', $user->id)->first();
        PassportVerifier::truncate();
        $state = $verifier->state;
        $codeVerifier = $verifier->code_verifier;

        // $state = $request->session()->pull('state');
        // $codeVerifier = $request->session()->pull('code_verifier');
    
        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class
        );
     
        $response = Http::asForm()->post('http://localhost:8000/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'redirect_uri' => 'http://localhost:8888/callback',
            'code_verifier' => $codeVerifier,
            'code' => $request->code,
        ]);
    
        $accessTokenResult = $response->json();

        PassportAccessToken::create([
            'user_id' => $user->id,
            'access_token' => $accessTokenResult['access_token']
        ]);
    
        // return $accessTokenResult['access_token'];
        
        return redirect()->intended('/get-products-from-api');
    });

    Route::get('/get-products-from-api', function (Request $request) {
        $accessToken = PassportAccessToken::where('user_id', auth()->user()->id)->first()->access_token;
    
        if ($accessToken === null) {
            return redirect()->route('redirect');
        }
    
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken
        ])->get('http://localhost:8000/api/products');
     
        return $response->json();
    });

    Route::get('/delete-tokens', function() {
        $user = auth()->user();

        $tokens = PassportAccessToken::where('user_id', $user->id)->get();

        if ($tokens === null) {
            return false;
        }

        foreach ($tokens as $token) {
            $token->delete();
        }

        return true;
    });
});

