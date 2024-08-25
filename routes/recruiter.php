<?php

use Illuminate\Support\Facades\Route;

// Combine all controller imports
use App\Http\Controllers\Api\{
    JobController,
    JobTestController,
    JobTestQuestionController,
    RecruiterController
};




Route::prefix('recruiter')->group(function () {
        Route::get('/dashboard', [RecruiterController::class, 'getDashboard']);
        Route::put('/{recruiterId}/update', [RecruiterController::class, 'updateRecruiterPassword']);

        Route::prefix('job-tests')->group(function () {
            Route::get('/', [JobTestController::class, 'getAllTests']);
            Route::get('/{testId}', [JobTestController::class, 'getTestDetails']);
            Route::post('/store', [JobTestController::class, 'storeTestDetails']);
            Route::get('/{testId}/results', [JobTestController::class, 'showTestResults']);
            Route::put('/{testId}/update', [JobTestController::class, 'updateTestDetails']);
            Route::delete('/{testId}/delete', [JobTestController::class, 'destroyTestDetails']);
            Route::get('/availability/{subjectId}', [JobTestController::class, 'checkAvailability']);
        });

        Route::prefix('jobs')->group(function () {
            Route::get('/{recruiterId?}', [JobController::class, 'getJobsByRecruiterId']);
        });

        //Assessment Questions Routes
        Route::prefix('job-tests-questions')->group(function () {
            Route::get('/', [JobTestQuestionController::class, 'getAllTestQuestions']);
            Route::post('/store', [JobTestQuestionController::class, 'store']);
            Route::get('/{testQuestionId}/show', [JobTestQuestionController::class, 'getTestQuestionDetails']);
            Route::put('/{testQuestionId}/update', [JobTestQuestionController::class, 'update']);
            Route::delete('/{testQuestionId}/delete', [JobTestQuestionController::class, 'delete']);
        });
    });