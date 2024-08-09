<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\FeesController;

Route::prefix('parent')->group(function () {
    Route::get('/get-dashboard', [ParentController::class, 'getDashboard']);
    Route::get('/get-auth-details', [AuthController::class, 'getDetails']);

    Route::put('/update', [ParentController::class, 'updateParentSettings']);

    Route::get('/applications/{parentPhone}', [ParentController::class, 'getApplications']);
    Route::post('/application', [ParentController::class, 'storeChildrenApplication']);
    Route::post('/get-application/{applicationId}', [ParentController::class, 'getChildrenApplication']);

    Route::get('/get-children', [ParentController::class, 'getChildren']);
    Route::get('/get-student-info', [ParentController::class, 'getStudentDetails']);
    Route::get('/get-report-card', [CourseController::class, 'getStudentReportCard']);

    Route::get('/{parentId}', [ParentController::class, 'getParentDetails']);
    Route::put('/{parentId}/update', [ParentController::class, 'updateParentDetails']);

    // Route::get('student/{studentId}/results/{studentId}', [AssessmentController::class, 'getAssessmentResultsWithVideo']);

    Route::post('/students/store', [StudentController::class, 'storeStudentDetails']);
    Route::get('/get-fee/{classId}', [FeesController::class, 'getFeeByClass']);

    // for mobile
    Route::get('chapter/{chapterId}/results/{studentId}', [AssessmentController::class, 'getAssessmentResultsWithVideo']);
});