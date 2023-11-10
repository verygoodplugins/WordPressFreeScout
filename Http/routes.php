<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\WordPressFreeScout\Http\Controllers'], function()
{
    Route::get('/', 'WordPressFreeScoutController@index');

    Route::post('/wordpress/ajax', ['uses' => 'WordPressFreeScoutController@ajax', 'laroute' => true])->name('wordpress.ajax');

    Route::get('/mailbox/wordpressfreescout/{id}', ['uses' => 'WordPressFreeScoutController@mailboxSettings', 'middleware' => ['auth', 'roles'], 'roles' => ['admin']])->name('mailboxes.wordpressfreescout');
    Route::post('/mailbox/wordpressfreescout/{id}', ['uses' => 'WordPressFreeScoutController@mailboxSettingsSave', 'middleware' => ['auth', 'roles'], 'roles' => ['admin']])->name('mailboxes.wordpressfreescout.save');

});
