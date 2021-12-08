<?php

use Illuminate\Support\Facades\Route;

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
    $db = new PDO("pgsql:dbname=tracken;host=127.0.0.1", "postgres", "showdebola");

    if ($db) {
        echo 'conectado';
    } else {
        echo 'não conectado';
    }
});
