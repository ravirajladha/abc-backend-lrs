<?php

use Illuminate\Support\Facades\Route;

// Combine all controller imports
use App\Http\Controllers\Api\{
    JobController,
    JobTestController,
    LogController,
    QnaController,
    AuthController,
    NoteController,
    ForumController,
    VideoController,
    StudentController,
    CourseController,
    TermTestController,
    AssessmentController,
    TermTestResultController,
    ReadableCoursesController,
    ExternalStudentController,
    ElabController,
    MiniProjectController,
    InternshipController
};




Route::prefix('student')->group(function () {
    Route::post('/mini-project-tasks/complete-status-for-student', [MiniProjectController::class, 'completeStatusForStudent']);
    Route::post('/internship/generate-certificate', [InternshipController::class, 'generateCertificate']);

    Route::get('/subjects', [CourseController::class, 'getStudentSubjectsWithResults']);
    Route::get('/get-report-card', [CourseController::class, 'getStudentReportCard']);
    Route::get('/get-subject-results', [TermTestResultController::class, 'getStudentTestDetailsBySubjectId']);

    Route::get('/my-courses', [CourseController::class, 'getMyCourses']);
    Route::get('/course-preview/{subjectId}', [CourseController::class, 'getCoursePreview']);

    //Job Routes
    Route::prefix('jobs')->group(function () {
        Route::get('/', [JobController::class, 'getJobList']);
        Route::post('/', [JobController::class, 'applyJob']);
    });

    //Notes Routes
    Route::prefix('notes')->group(function () {
        Route::get('/', [NoteController::class, 'getNotesByVideo']);
        Route::post('/', [NoteController::class, 'storeNotesByVideo']);
    });

    //Qna Routes
    Route::prefix('qna')->group(function () {
        Route::get('/{studentId}/{teacherId}/{subjectId}', [QnaController::class, 'getQnaBySubject']);
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
    Route::prefix('term-tests')->group(function () {
        Route::get('/{testId}', [TermTestController::class, 'getTermTestDetails']);
        Route::get('/get-details-by-token/{token}/{testId}', [TermTestController::class, 'getTermTestDetailsByToken']);
        Route::post('/', [TermTestController::class, 'storeTermTestResponse']);
        Route::post('/token', [TermTestController::class, 'storeTermTestResponseWithToken']);
        Route::post('/start', [TermTestController::class, 'startTest']);
    });
    Route::prefix('job-tests')->group(function () {
        Route::get('/{jobId}', [JobController::class, 'getJobTestDetails']);
        Route::get('/get-details-by-token/{token}/{jobId}', [JobController::class, 'getJobTestDetailsByToken']);
        Route::post('/', [JobController::class, 'storeJobTestResponse']);
        Route::post('/token', [JobTestController::class, 'storeJobTestResponseWithToken']);
        Route::post('/withoutToken', [JobTestController::class, 'storeJobTestResponseWithoutToken']);
        Route::post('/start', [JobController::class, 'startTest']);
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

    Route::get('/get-auth-details', [AuthController::class, 'getDetails']);
    Route::get('/readable-courses', [ReadableCoursesController::class, 'getReadableCoursesByClass']);
    Route::get('/{studentId}', [StudentController::class, 'getStudentDetails']);
    Route::put('/{studentId}/update', [StudentController::class, 'updateStudentPassword']);

    Route::post("/video-log/store", [LogController::class, 'storeVideoLog']);
    Route::post("/connect-parent", [StudentController::class, 'updateParentDetails']);

    Route::get("/parent-details/{studentId}", [StudentController::class, 'getParentDetails']);

    //Content Page Routes
    Route::get('/subjects/{subjectId}/contents', [VideoController::class, 'getContents']);

    Route::get('/subjects/{subjectId}/external-student-contents', [ExternalStudentController::class, 'getContents']);
});