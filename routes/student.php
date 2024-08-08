<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Controller;

//Auth Controllers
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\JobTestController;
use App\Http\Controllers\Api\JobTestQuestionController;
use App\Http\Controllers\Api\RecruiterController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\QnaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NoteController;

//User Controllers
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\EbookController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\ParentController;

//Content Controllers
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\ClassesController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentImageController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TermTestController;
use App\Http\Controllers\Api\CaseStudyController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\ApplicationController;

//Test Controllers

use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\EbookModuleController;
use App\Http\Controllers\Api\Auth\RefreshController;

// Application Controller
use App\Http\Controllers\Api\EbookElementController;
use App\Http\Controllers\Api\EbookSectionController;

// Project Report
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\ProjectReportController;
use App\Http\Controllers\Api\OldApplicationController;
use App\Http\Controllers\Api\TermTestResultController;

// Case Study
use App\Http\Controllers\Api\CaseStudyModuleController;
use App\Http\Controllers\Api\ReadableCoursesController;
use App\Http\Controllers\Api\CaseStudyElementController;
use App\Http\Controllers\Api\CaseStudySectionController;
use App\Http\Controllers\Api\TermTestQuestionController;
use App\Http\Controllers\Api\AssessmentQuestionController;
//Dincharya
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\DinacharyaController;

// Readable Courses
use App\Http\Controllers\Api\ProjectReportModuleController;

use App\Http\Controllers\Api\ProjectReportElementController;

use App\Http\Controllers\Api\FeesController;
use App\Http\Controllers\Api\ExternalStudentController;
use App\Http\Controllers\Api\ZoomCallController;

//Elab Controller
use App\Http\Controllers\Api\ProjectReportSectionController;
use App\Http\Controllers\Api\{ElabController, MiniProjectController, InternshipController};

use Illuminate\Support\Facades\Response;

Route::prefix('student')->group(function () {
    Route::post('/mini-project-tasks/complete-status-for-student', [MiniProjectController::class, 'completeStatusForStudent']);
    Route::post('/internship/generate-certificate', [InternshipController::class, 'generateCertificate']);

    Route::get('/subjects', [SubjectController::class, 'getStudentSubjectsWithResults']);
    Route::get('/get-report-card', [SubjectController::class, 'getStudentReportCard']);
    Route::get('/get-subject-results', [TermTestResultController::class, 'getStudentTestDetailsBySubjectId']);

    Route::get('/my-courses', [SubjectController::class, 'getMyCourses']);
    Route::get('/course-preview/{subjectId}', [SubjectController::class, 'getCoursePreview']);

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