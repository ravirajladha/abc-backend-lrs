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

            Route::get('/', [JobTestController::class, 'getAllTermTests']);
            Route::get('/{testId}', [JobTestController::class, 'getTermTestDetails']);
            Route::post('/store', [JobTestController::class, 'storeTermTestDetails']);
            Route::get('/{testId}/results', [JobTestController::class, 'showTermTestResults']);
            Route::put('/{testId}/update', [JobTestController::class, 'updateTermTestDetails']);
            Route::delete('/{testId}/delete', [JobTestController::class, 'destroyTermTestDetails']);
            Route::get('/availability/{subjectId}', [JobTestController::class, 'checkTermAvailability']);
        });
        Route::prefix('jobs')->group(function () {

            Route::get('/{recruiterId?}', [JobController::class, 'getJobsByRecruiterId']);
        });

        //Assessment Questions Routes
        Route::prefix('job-tests-questions')->group(function () {
            Route::get('/', [JobTestQuestionController::class, 'getAllTermTestQuestions']);
            Route::post('/store', [JobTestQuestionController::class, 'store']);
            Route::get('/{termTestQuestionId}/show', [JobTestQuestionController::class, 'getTermTestQuestionDetails']);
            Route::put('/{termTestQuestionId}/update', [JobTestQuestionController::class, 'update']);
            Route::delete('/{termTestQuestionId}/delete', [JobTestQuestionController::class, 'delete']);
        });
    });