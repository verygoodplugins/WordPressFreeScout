<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\PmproFreescout\Http\Controllers'], function()
{
    Route::get('/', 'PmproFreescoutController@index');

    Route::post('/pmpro/ajax', ['uses' => 'PmproFreescoutController@ajax', 'laroute' => true])->name('pmpro.ajax');

    Route::get('/mailbox/pmprofreescout/{id}', ['uses' => 'PmproFreescoutController@mailboxSettings', 'middleware' => ['auth', 'roles'], 'roles' => ['admin']])->name('mailboxes.pmprofreescout');
    Route::post('/mailbox/pmprofreescout/{id}', ['uses' => 'PmproFreescoutController@mailboxSettingsSave', 'middleware' => ['auth', 'roles'], 'roles' => ['admin']])->name('mailboxes.pmprofreescout.save');

});
