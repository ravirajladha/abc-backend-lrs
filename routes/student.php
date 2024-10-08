<?php

use Illuminate\Support\Facades\Route;

// Combine all controller imports
use App\Http\Controllers\Api\{
    PlacementController,
    PlacementTestController,
    LogController,
    QnaController,
    AuthController,
    NoteController,
    ForumController,
    VideoController,
    StudentController,
    CourseController,
    TestController,
    AssessmentController,
    TestResultController,
    ReadableCoursesController,
    ExternalStudentController,
    ElabController,
    MiniProjectController,
    InternshipController,
    RatingReviewController
};

Route::prefix('student')->group(function () {
    Route::post('/mini-project-tasks/complete-status-for-student', [MiniProjectController::class, 'completeStatusForStudent']);
    Route::post('/internship/generate-certificate', [InternshipController::class, 'generateCertificate']);

    Route::get('/courses', [CourseController::class, 'getStudentCoursesWithResults']);
    Route::get('/get-report-card', [CourseController::class, 'getStudentReportCard']);
    Route::get('/get-course-results', [TestResultController::class, 'getStudentTestDetailsByCourseId']);

    Route::get('/my-courses', [CourseController::class, 'getMyCourses']);
    Route::get('/course-preview/{courseId}', [CourseController::class, 'getCoursePreview']);

    //Job Routes
    Route::prefix('jobs')->group(function () {
        Route::get('/', [PlacementController::class, 'getJobList']);
        Route::post('/', [PlacementController::class, 'applyJob']);
    });

    //Notes Routes
    Route::prefix('notes')->group(function () {
        Route::get('/', [NoteController::class, 'getNotesByVideo']);
        Route::post('/', [NoteController::class, 'storeNotesByVideo']);
    });

    //Qna Routes
    Route::prefix('qna')->group(function () {
        Route::get('/{studentId}/{trainerId}/{courseId}', [QnaController::class, 'getQnaByCourse']);
        Route::post('/', [QnaController::class, 'storeQnaBySubject']);
        Route::get('/search/{question?}', [QnaController::class, 'searchQuestionByKeyword']);
    });

    //Forum Routes
    Route::prefix('forums')->group(function () {
        Route::get('/', [ForumController::class, 'getStudentForumList']);
        Route::get('{forumId}', [ForumController::class, 'getForumQuestionDetails']);
        Route::post('/', [ForumController::class, 'storeForumQuestion']);
        Route::get('/search/{question}', [ForumController::class, 'searchForumQuestion']);
        Route::post('/answer', [ForumController::class, 'storeForumAnswer']);
        Route::post('/answer-vote', [ForumController::class, 'storeVote']);
    });
    //elabs for student
    Route::prefix('elabs')->group(function () {
        Route::get('/{id}', [ElabController::class, 'getElabDetail']);
        //submit elabs code ino submission table
        Route::post('/elab-submission', [ElabController::class, 'elabSubmission']);
    });

    //Term Test Routes
    Route::prefix('tests')->group(function () {
        Route::get('/{testId}', [TestController::class, 'getTestDetails']);
        Route::get('/get-details-by-token/{token}/{testId}', [TestController::class, 'getTestDetailsByToken']);
        Route::post('/', [TestController::class, 'storeTestResponse']);
        Route::post('/token', [TestController::class, 'storeTestResponseWithToken']);
        Route::post('/start', [TestController::class, 'startTest']);
    });

    Route::prefix('job-tests')->group(function () {
        Route::get('/{jobId}', [PlacementController::class, 'getJobTestDetails']);
        Route::get('/get-details-by-token/{token}/{jobId}', [PlacementController::class, 'getJobTestDetailsByToken']);
        Route::post('/', [PlacementController::class, 'storeJobTestResponse']);
        Route::post('/token', [PlacementTestController::class, 'storeJobTestResponseWithToken']);
        Route::post('/withoutToken', [PlacementTestController::class, 'storeJobTestResponseWithoutToken']);
        Route::post('/start', [PlacementController::class, 'startTest']);
    });

    //Assessments Routes
    Route::prefix('assessments')->group(function () {
        Route::get('/{assessmentId}', [AssessmentController::class, 'getAssessmentDetailsWithQuestions']);
        Route::post('/', [AssessmentController::class, 'storeAssessmentResponse']);
    });

    Route::prefix('assessments')->group(function () {
        Route::get('/{assessmentId}', [AssessmentController::class, 'getAssessmentDetailsWithQuestions']);
        Route::post('/', [AssessmentController::class, 'storeAssessmentResponse']);
    });

    Route::prefix('internships')->group(function () {
        Route::get('/', [InternshipController::class, 'getInternshipsForStudent']);
    });

    Route::get('/dashboard', [StudentController::class, 'getDashboard']);
    Route::get('/wallet-details/{studentAuthId?}', [StudentController::class, 'getWalletDetailsAndLogs']);

    Route::get("/get-fee-and-payment-status", [StudentController::class, 'getFeeAndStatus']);

    Route::get('/get-auth-details', [AuthController::class, 'getDetails']);
    Route::get('/readable-courses', [ReadableCoursesController::class, 'getReadableCoursesByClass']);
    Route::get('/{studentId}', [StudentController::class, 'getStudentDetails']);
    Route::put('/{studentId}/update', [StudentController::class, 'updateStudentPassword']);

    Route::post("/video-log/store", [LogController::class, 'storeVideoLog']);
    Route::post("/connect-parent", [StudentController::class, 'updateParentDetails']);

    Route::get("/parent-details/{studentId}", [StudentController::class, 'getParentDetails']);


    //Content Page Routes, video controller is pending
    Route::get('/subjects/{subjectId}/contents', [VideoController::class, 'getContents']);

    Route::get('/courses/{courseId}/external-student-contents', [ExternalStudentController::class, 'getContents']);

    Route::post('/courses/rating-review', [RatingReviewController::class, 'storeRatingReview']);
    Route::get('/courses/{courseId}/ratings-reviews', [RatingReviewController::class, 'getCourseRatingsAndReviews']);
});
