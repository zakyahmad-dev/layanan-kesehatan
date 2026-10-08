<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Doctor::with('polyclinic');

        if ($request->has('polyclinic_id')) {
            $query->where('polyclinic_id', $request->polyclinic_id);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $query->get()
        ]);
    }
}