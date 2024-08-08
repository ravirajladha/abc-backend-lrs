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

Route::prefix('school')->group(function () {
    Route::get('/dashboard', [SchoolController::class, 'getDashboard']);
    Route::get('/get-auth-details', [AuthController::class, 'getDetails']);
    Route::get('/details', [SchoolController::class, 'getSchoolDetails']);
    Route::put('/{schoolId}/update', [SchoolController::class, 'updateSchoolDetails']);
    Route::get('/results', [SchoolController::class, 'getAllStudentsResults']);

    // assessment results video wise
    Route::get('chapter/assessment-results', [AssessmentController::class, 'getAssessmentResults']);

    // Applications

    Route::prefix('applications')->group(function () {
        Route::get('/{status}', [ApplicationController::class, 'getApplications']);
        Route::post('/update-status', [ApplicationController::class, 'updateApplicationStatus']);
        Route::post('/update-whatsapp-status', [ApplicationController::class, 'updateApplicationWhatsappStatus']);
        Route::post('/store-application-remark', [ApplicationController::class, 'storeApplicationRemark']);
        Route::get('/get-application/{applicationId}', [ApplicationController::class, 'getApplicationById']);
        Route::post('/send-whatsapp-message/{contact}/{messageType}', [ApplicationController::class, 'sendWhatsappMessage']);
        Route::post('/send-bulk-whatsapp-message/{messageType}', [ApplicationController::class, 'sendBulkWhatsappMessages']);
    });
    // Old Applications
    Route::prefix('old-applications')->group(function () {
        Route::get('/{status}', [OldApplicationController::class, 'getApplications']);
        Route::post('/update-status', [OldApplicationController::class, 'updateApplicationStatus']);
        Route::post('/update-whatsapp-status', [OldApplicationController::class, 'updateApplicationWhatsappStatus']);
        Route::post('/store-application-remark', [OldApplicationController::class, 'storeApplicationRemark']);
        Route::get('/get-application/{applicationId}', [OldApplicationController::class, 'getApplicationById']);
        Route::post('/upload', [OldApplicationController::class, 'uploadOldApplication']);
        Route::post('/send-bulk-whatsapp-message/{messageType}', [OldApplicationController::class, 'sendBulkWhatsappMessages']);
    });

    //School Student Routes
    Route::prefix('students')->group(function () {
        Route::get('/get-public-students', [StudentController::class, 'getPublicStudentDetailsFromStudent']);
        Route::get('/get-private-students', [StudentController::class, 'getPrivateStudentDetailsFromStudent']);
        Route::get('/{studentId}/get-images', [StudentImageController::class, 'getStudentImages']);
        Route::delete('/{imageId}/delete', [StudentImageController::class, 'deleteImage']);

        Route::get('/', [StudentController::class, 'getStudentsList']);
        Route::get('/{studentId}', [StudentController::class, 'getStudentDetails']);
        Route::post('/store', [StudentController::class, 'storeStudentDetails']);
        Route::post('/add-student-images', [StudentImageController::class, 'storeStudentImages']);
        Route::put('/{studentId}/update', [StudentController::class, 'updateStudentDetails']);
        Route::delete('/{studentId}/delete', [StudentController::class, 'deleteStudentDetails']);
        Route::get('/get-student-details/{studentId}', [StudentController::class, 'getStudentDetailsFromStudent']);

        // Route::put('/{studentId}/status', [StudentController::class, 'updateStudentDetails']);
    });


    //School Teacher Routes
    Route::prefix('teachers')->group(function () {
        Route::get('/', [TeacherController::class, 'getTeachersList']);
        Route::get('/{teacherId}', [TeacherController::class, 'getTeacherDetails']);
        Route::post('/store', [TeacherController::class, 'storeTeacherDetails']);
        Route::get('/{teacherId}/assign', [TeacherController::class, 'getTeacherClassesAndSubjects']);
        Route::post('/{teacherId}/assign', [TeacherController::class, 'storeOrUpdateTeacherClassesAndSubjects']);
        Route::put('/{teacherId}/update', [TeacherController::class, 'updateTeacherDetails']);
        Route::delete('/{teacherId}/delete', [TeacherController::class, 'deleteTeacherDetails']);
    });

    //School Parent Routes
    Route::prefix('parents')->group(function () {
        Route::get('/', [ParentController::class, 'getParentList']);
        Route::get('/{parentId}', [ParentController::class, 'getParentDetails']);
        // Route::delete('/{parentId}/disable', [ParentController::class, 'disableParent']);
    });

    Route::get('/class/students/{classId?}/{sectionId?}', [StudentController::class, 'getStudentsByClassAndSection']);
});