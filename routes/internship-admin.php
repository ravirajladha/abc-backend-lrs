<?php

use Illuminate\Support\Facades\Route;

// Combine all controller imports
use App\Http\Controllers\Api\{
    AuthController,
    ParentController,
    SchoolController,
    StudentController,
    StudentImageController,
    TeacherController,
    AssessmentController,
    ApplicationController,
    OldApplicationController
};




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