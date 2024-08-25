<?php

use Illuminate\Support\Facades\Route;

// Combine all controller imports
use App\Http\Controllers\Api\{
    AuthController,
    // ParentController,
    SchoolController,
    StudentController,
    StudentImageController,
    TrainerController,
    AssessmentController,
    ApplicationController,
    OldApplicationController
};




Route::prefix('internship-admin')->group(function () {
    Route::get('/dashboard', [SchoolController::class, 'getDashboard']);
    Route::get('/get-auth-details', [AuthController::class, 'getDetails']);
    Route::get('/details', [SchoolController::class, 'getSchoolDetails']);
    Route::put('/{schoolId}/update', [SchoolController::class, 'updateSchoolDetails']);
    Route::get('/results', [SchoolController::class, 'getAllStudentsResults']);

    // assessment results video wise
    Route::get('chapter/assessment-results', [AssessmentController::class, 'getAssessmentResults']);

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


    //School Trainer Routes
    Route::prefix('trainers')->group(function () {
        Route::get('/', [TrainerController::class, 'getTrainersList']);
        Route::get('/{trainerId}', [TrainerController::class, 'getTrainerDetails']);
        Route::post('/store', [TrainerController::class, 'storeTrainerDetails']);
        Route::get('/{trainerId}/assign', [TrainerController::class, 'getTrainerSubjectsAndCourses']);
        Route::post('/{trainerId}/assign', [TrainerController::class, 'storeOrUpdateTrainerSubjectsAndCourses']);
        Route::put('/{trainerId}/update', [TrainerController::class, 'updateTrainerDetails']);
        Route::delete('/{trainerId}/delete', [TrainerController::class, 'deleteTrainerDetails']);
    });

    //School Parent Routes
    // Route::prefix('parents')->group(function () {
    //     Route::get('/', [ParentController::class, 'getParentList']);
    //     Route::get('/{parentId}', [ParentController::class, 'getParentDetails']);
    //     // Route::delete('/{parentId}/disable', [ParentController::class, 'disableParent']);
    // });
//pending , needs to do with studentcontroller module
    Route::get('/subject/students/{classId?}/{sectionId?}', [StudentController::class, 'getStudentsByClassAndSection']);
});