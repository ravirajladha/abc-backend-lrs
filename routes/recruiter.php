<?php

use Illuminate\Support\Facades\Route;

// Combine all controller imports
use App\Http\Controllers\Api\{
    PlacementController,
    PlacementTestController,
    PlacementTestQuestionController,
    RecruiterController
};




Route::prefix('recruiter')->group(function () {
        Route::get('/dashboard', [RecruiterController::class, 'getDashboard']);
        Route::put('/{recruiterId}/update', [RecruiterController::class, 'updateRecruiterPassword']);

        Route::prefix('job-tests')->group(function () {
            Route::get('/', [PlacementTestController::class, 'getAllTests']);
            Route::get('/{testId}', [PlacementTestController::class, 'getTestDetails']);
            Route::post('/store', [PlacementTestController::class, 'storeTestDetails']);
            Route::get('/{testId}/results', [PlacementTestController::class, 'showTestResults']);
            Route::put('/{testId}/update', [PlacementTestController::class, 'updateTestDetails']);
            Route::delete('/{testId}/delete', [PlacementTestController::class, 'destroyTestDetails']);
            Route::get('/availability/{subjectId}', [PlacementTestController::class, 'checkAvailability']);
        });

        Route::prefix('jobs')->group(function () {
            Route::get('/{recruiterId?}', [PlacementController::class, 'getJobsByRecruiterId']);
        });

        //Assessment Questions Routes
        Route::prefix('job-tests-questions')->group(function () {
            Route::get('/', [PlacementTestQuestionController::class, 'getAllTestQuestions']);
            Route::post('/store', [PlacementTestQuestionController::class, 'store']);
            Route::get('/{testQuestionId}/show', [PlacementTestQuestionController::class, 'getTestQuestionDetails']);
            Route::put('/{testQuestionId}/update', [PlacementTestQuestionController::class, 'update']);
            Route::delete('/{testQuestionId}/delete', [PlacementTestQuestionController::class, 'delete']);
        });
    });