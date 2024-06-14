<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TopikController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\KonsultasiController;
use App\Http\Controllers\Api\QuizController;

/**
 * route "/register"
 * @method "POST"
 */
Route::post('/register', App\Http\Controllers\Api\RegisterController::class)->name('register');

Route::post('/registerPsikolog', App\Http\Controllers\Api\RegisterPsikologController::class)->name('registerPsikolog');
/**
 * route "/login"
 * @method "POST"
 */
Route::post('/login', App\Http\Controllers\Api\LoginController::class)->name('login');

/**
 * route "/user"
 * @method "GET"
 */
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
/**
 * route "/logout"
 * @method "POST"
 */
Route::post('/logout', App\Http\Controllers\Api\LogoutController::class)->name('logout');

//posts
Route::apiResource('/posts', App\Http\Controllers\Api\PostController::class);
//topik
Route::apiResource('/topiks', App\Http\Controllers\Api\TopikController::class);

Route::apiResource('/psikolog', App\Http\Controllers\Api\UserController::class);

Route::apiResource('/bantuans', App\Http\Controllers\Api\BantuanController::class);

Route::apiResource('/konsultasi', App\Http\Controllers\Api\KonsultasiController::class);

Route::apiResource('/quiz', App\Http\Controllers\Api\QuizController::class);
Route::apiResource('/attemptquiz', App\Http\Controllers\Api\QuizAttemptController::class);

Route::get('/topiks/{topik}', 'App\Http\Controllers\Api\TopikController@show');

Route::get('/psikolog', 'App\Http\Controllers\Api\UserController@show');

Route::apiResource('/konsultasi/reply', App\Http\Controllers\Api\KonsultasiMessageController::class);

Route::get('/konsulbyuser', 'App\Http\Controllers\Api\KonsultasiController@konsulByUser');

Route::get('/quizbypost/{post}', 'App\Http\Controllers\Api\PostController@getQuizByPostId');

Route::get('/riwayatquiz', 'App\Http\Controllers\Api\QuizAttemptController@getRiwayatQuizUser');

Route::get('/topicwithprogress', 'App\Http\Controllers\Api\TopikController@topicWithProgress');


