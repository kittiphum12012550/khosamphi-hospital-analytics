<?php

use App\Models\Department;
use App\Models\ServiceType;
use App\Models\VisitStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Khosamphi Hospital Analytics Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Future HIS / External API Integration endpoints
Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'hospital' => config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร'),
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::get('/departments', function () {
        return response()->json(Department::active()->get());
    });

    Route::get('/service-types', function () {
        return response()->json(ServiceType::active()->get());
    });
});

