<?php

use Illuminate\Support\Facades\Route;

/**
 * This route is called when user must first time confirm secret
 */
Route::post('register', 'CarlosCGO\Google2fa\Google2fa@register');

/**
 * This route is called when user must first time confirm secret
 */
Route::post('confirm', 'CarlosCGO\Google2fa\Google2fa@confirm');

/**
 * This route is called to verify users secret
 */
Route::post('authenticate', 'CarlosCGO\Google2fa\Google2fa@authenticate');
