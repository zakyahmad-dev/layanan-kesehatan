<?php

namespace App\Http\Controllers;

use App\Models\Polyclinic;
use Illuminate\Http\JsonResponse;

class PolyclinicController extends Controller
{
    public function index(): JsonResponse
    {
        $polyclinics = Polyclinic::all();

        return response()->json([
            'status' => 'success',
            'data'   => $polyclinics
        ]);
    }
}