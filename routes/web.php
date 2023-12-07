<?php

use App\Http\Controllers\ChatGPTController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', [ChatGPTController::class, 'welcome']);
Route::post('getsqlstatement', [ChatGPTController::class, 'sqlQueryFromChatGpt'])->name('getsqlstatement');
Route::get('getcreatestatement', [ChatGPTController::class, 'sqlCreateStatementFromDb'])->name('getcreatestatement');