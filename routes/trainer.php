<?php

use Illuminate\Support\Facades\Route;

// Combine all controller imports
use App\Http\Controllers\Api\{
    QnaController,
    AuthController,
    ChapterController,
    TeacherController,
    AssessmentController
};


Route::prefix('teacher')->group(function () {

    Route::prefix('qna')->group(function () {
        Route::get('/{trainerId}/{studentId}', [QnaController::class, 'getQnaBySubject']);
        Route::post('/', [QnaController::class, 'storeTrainerQnaResponse']);
        Route::get('/get-unreplied-count', [TeacherController::class, 'countUnrepliedQnAsForTeacher']);
    });

    Route::prefix('results')->group(function () {
        Route::get('/term-test', [TeacherController::class, 'getAllTermResults']);
        Route::get('/assessments', [TeacherController::class, 'getAllAssessmentResults']);
    });

    Route::get('/get-auth-details', [AuthController::class, 'getDetails']);
    Route::get('/dashboard', [TeacherController::class, 'getDashboard']);

    Route::get('/get-classes', [TeacherController::class, 'getTeacherClasses']);
    Route::get('/get-subjects/{classId}', [TeacherController::class, 'getTeacherSubjectsByClass']);
    Route::get('/get-chapters', [TeacherController::class, 'getAllTeacherChaptersBySubject']);
    Route::get('/get-students', [TeacherController::class, 'getAllStudentsBySubjects']);

    Route::get('/{teacherId}', [TeacherController::class, 'getTeacherDetails']);
    Route::put('/{teacherId}/update', [TeacherController::class, 'updateTeacherPassword']);

    Route::get('chapter/assessment-results', [AssessmentController::class, 'getAssessmentResultsByStudentId']);

    Route::get('/chapter/{chapterId}/update-lock-status', [ChapterController::class, 'updatechapterLockStatus']);
});