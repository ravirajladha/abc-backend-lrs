<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\FeesController;

Route::prefix('parent')->group(function () {
  

    Route::post('/students/store', [StudentController::class, 'storeStudentDetails']);
    Route::get('/get-fee/{subjectId}', [FeesController::class, 'getFeeBySubject']);

    // for mobile
    Route::get('chapter/{chapterId}/results/{studentId}', [AssessmentController::class, 'getAssessmentResultsWithVideo']);
});