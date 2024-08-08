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

Route::prefix('teacher')->group(function () {

    Route::prefix('qna')->group(function () {
        Route::get('/{teacherId}/{studentId}', [QnaController::class, 'getQnaBySubject']);
        Route::post('/', [QnaController::class, 'storeTeacherQnaResponse']);
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