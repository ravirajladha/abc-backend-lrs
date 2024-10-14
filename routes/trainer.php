<?php

use Illuminate\Support\Facades\Route;

// Combine all controller imports
use App\Http\Controllers\Api\{
    QnaController,
    AuthController,
    ChapterController,
    TrainerController,
    AssessmentController,
    RatingReviewController,
    FaqController
};


Route::prefix('trainer')->group(function () {

    Route::prefix('qna')->group(function () {
        Route::get('/{trainerId}/{studentId}', [QnaController::class, 'getQnaByCourse']);
        Route::post('/', [QnaController::class, 'storeTrainerQnaResponse']);
        Route::get('/get-unreplied-count', [TrainerController::class, 'countUnrepliedQnAsForTrainer']);
    });

    // Route::prefix('results')->group(function () {
    //     Route::get('/test', [TrainerController::class, 'getAllResults']);
    //     Route::get('/assessments', [TrainerController::class, 'getAllAssessmentResults']);
    // });

    Route::get('/get-auth-details', [AuthController::class, 'getDetails']);
    Route::get('/dashboard', [TrainerController::class, 'getDashboard']);

    Route::get('/get-subjects', [TrainerController::class, 'getTrainerSubjects']);
    Route::get('/get-courses/{subjectId}', [TrainerController::class, 'getTrainerCoursesBySubject']);
    // Route::get('/get-chapters', [TrainerController::class, 'getAllTrainerChaptersByCourse']);
    Route::get('/get-students', [TrainerController::class, 'getAllStudentsByCourses']);

    Route::get('/{trainerId}', [TrainerController::class, 'getTrainerDetails']);
    Route::put('/{trainerId}/update', [TrainerController::class, 'updateTrainerPassword']);

    Route::get('chapter/assessment-results', [AssessmentController::class, 'getAssessmentResultsByStudentId']);

    Route::get('/chapter/{chapterId}/update-lock-status', [ChapterController::class, 'updateChapterLockStatus']);

    Route::post('/courses/reply-review', [RatingReviewController::class, 'storeReviewReply']);
    Route::put('/courses/update-review-status', [RatingReviewController::class, 'updateReviewStatus']);

    Route::get('/courses/{courseId}/faq', [FaqController::class, 'getCourseFaqs']);
    Route::post('/courses/faq', [FaqController::class, 'storeFaq']);
    Route::delete('/courses/faq/{faqId}', [FaqController::class, 'deleteFaq']);
    Route::get('/courses/faq/{faqId}', [FaqController::class, 'getFaqById']);
    Route::put('/courses/faq/{faqId}', [FaqController::class, 'editFaq']);

});
