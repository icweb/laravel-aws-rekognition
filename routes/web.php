<?php

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

Route::get('/', 'PhotosController@showForm');
Route::post('/', 'PhotosController@submitForm');

Route::get('/log/{id}', function($id){

    $log = \Illuminate\Support\Facades\DB::table('upload_logs')->where(['id' => $id])->get();

    if(count($log) && $log[0]->body)
    {
        echo '<img src="data:image/png;base64, ' . $log[0]->body . '">';
    }

});
