<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PolyclinicController;
use App\Http\Controllers\DoctorController;

Route::get('/polyclinics', [PolyclinicController::class, 'index']);
Route::get('/doctors', [DoctorController::class, 'index']);