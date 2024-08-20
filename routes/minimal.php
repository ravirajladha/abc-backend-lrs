<?php

use Illuminate\Support\Facades\Route;

Route::prefix('minimal')->group(function () {
    Route::get('/subjects', \App\Http\Controllers\Api\Dropdown\SubjectController::class);
    Route::get('/subjects/{subjectId}/courses', \App\Http\Controllers\Api\Dropdown\CourseController::class);
    Route::get('/tests/subjects/{subjectId}/courses', \App\Http\Controllers\Api\Dropdown\CourseTestController::class);
    Route::get('/assessments', \App\Http\Controllers\Api\Dropdown\AssessmentController::class);
    Route::get('/elabs', \App\Http\Controllers\Api\Dropdown\ElabController::class);
    Route::get('/ebooks', \App\Http\Controllers\Api\Dropdown\EbookController::class);
    Route::get('/ebook-modules', \App\Http\Controllers\Api\Dropdown\EbookModuleController::class);
    Route::get('/ebook-sections', \App\Http\Controllers\Api\Dropdown\EbookSectionController::class);
    Route::get('/test', \App\Http\Controllers\Api\Dropdown\TestController::class);
    Route::get('/test-questions', \App\Http\Controllers\Api\Dropdown\TestQuestionController::class);
    Route::get('/test-questions-by-class-id', \App\Http\Controllers\Api\Dropdown\TestQuestionBySubjectIdController::class);
    Route::get('/get-assessment-questions-count', \App\Http\Controllers\Api\Dropdown\AssessmentQuestionController::class);
    Route::get('/project-reports', \App\Http\Controllers\Api\Dropdown\ProjectReportController::class);
    Route::get('/case-studies', \App\Http\Controllers\Api\Dropdown\CaseStudyController::class);
});
