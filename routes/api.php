<?php

use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OfficeImageController;
use App\Http\Controllers\UserReservationController;
use App\Http\Controllers\TagController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/tags', TagController::class );
//Office ...
Route::get('/offices', [OfficeController::class , 'index'] );
Route::get('/offices/{office}', [OfficeController::class , 'show'] );
Route::post('/offices', [OfficeController::class , 'create'] )->middleware('auth:sanctum' , 'verified');
Route::put('/offices/{office}', [OfficeController::class , 'update'] )->middleware('auth:sanctum' , 'verified');
Route::delete('/offices/{office}', [OfficeController::class , 'delete'] )->middleware('auth:sanctum' , 'verified');

// Offices With Images
Route::post('/offices/{office}/images', [OfficeImageController::class , 'store'] );
Route::delete('/offices/{office}/images/{image}', [OfficeImageController::class , 'delete'] );


// Reservations
Route::get('/reservations', [UserReservationController::class , 'index'] )->middleware('auth:sanctum' , 'verified');
Route::get('/reservations/{user}', [UserReservationController::class , 'show'] )->middleware('auth:sanctum' , 'verified');
Route::post('/reservations', [UserReservationController::class , 'create'] )->middleware('auth:sanctum' , 'verified');











