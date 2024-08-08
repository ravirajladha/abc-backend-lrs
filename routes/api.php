<?php

use Illuminate\Support\Facades\Route;
//User Controllers
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\EbookController;
//Content Controllers
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\ClassesController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\CaseStudyController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RefreshController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\ProjectReportController;
use Illuminate\Support\Facades\Response;
/*
|
|------------------------------------------------------------------------
| API Routes
|------------------------------------------------------------------------
*/

Route::post('/login', [LoginController::class, 'login']);
Route::post('/verify-email-and-send-otp', [ForgotPasswordController::class, 'verifyEmailAndSendOtp']);
Route::post('/verify-phone-and-send-otp', [ForgotPasswordController::class, 'verifyPhoneAndSendOtp']);
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
Route::post('/signup', [RegisterController::class, 'registerParent'])->middleware('cors');
Route::get('/mobile/ebooks/{ebookId}/get-ebook-mobile', [EbookController::class, 'getEbook']);
Route::get('/mobile/project-reports/{ProjectReportId}/get-project-report-mobile', [ProjectReportController::class, 'getProjectReport']);
Route::get('/mobile/case-study/{caseStudyId}/get-case-study-mobile', [CaseStudyController::class, 'getCaseStudy']);

// Route::get('/video', 'AdminController@play');
Route::get('/video', [AdminController::class, 'play']);

Route::get('/video/key/{folder}/{key}', function ($folder, $key) {
    return Storage::disk('secrets')->download("videos/{$folder}/{$key}");
})->name('video.key');

Route::get('/video/ts/{folder}/{filename}', function ($folder, $filename) {
    return Storage::disk('public')->download("videos/{$folder}/{$filename}");
})->name('video.ts');

Route::get('/video/playlist/{folder}/{playlist}', function ($folder, $playlist) {
    return FFMpeg::dynamicHLSPlaylist()
        ->fromDisk('public')
        ->open("videos/{$folder}/{$playlist}")
        ->setKeyUrlResolver(function ($key) use ($folder) {
            return route('video.key', ['folder' => $folder, 'key' => $key]);
        })
        ->setMediaUrlResolver(function ($mediaFilename) use ($folder) {
            return route('video.ts', ['folder' => $folder, 'filename' => $mediaFilename]);
        })
        ->setPlaylistUrlResolver(function ($playlist) use ($folder) {
            return route('video.playlist', ['folder' => $folder, 'playlist' => $playlist]);
        });
})->name('video.playlist');

Route::get('/download-zip', function () {
    $filePath = public_path('uploads/zip/resources.zip');

    if (!file_exists($filePath)) {
        abort(404, 'File not found');
    }

    return Response::download($filePath, 'resources.zip');
});
Route::get('/download-certificate', function () {
    $filePath = public_path('uploads/zip/pass.zip');

    if (!file_exists($filePath)) {
        abort(404, 'File not found');
    }

    return Response::download($filePath, 'resources.zip');
});

Route::group(['middleware' => ['check-auth-token', 'check-auth-type']], function () {

    //General Routes
    Route::post('/refresh-token', [RefreshController::class, 'refreshToken']);
    Route::post('/logout', [LogoutController::class, 'logout']);

    Route::post('/update-payment-status/{studentId}', [StudentController::class, 'updatePaymentStatus']);

    require __DIR__ . '/minimal.php';
    require __DIR__ . '/admin.php';
    require __DIR__ . '/internship-admin.php';
    require __DIR__ . '/student.php';
    require __DIR__ . '/trainer.php';
    require __DIR__ . '/parent.php';
    require __DIR__ . '/recruiter.php';


    //Classes Routes
    Route::get('/classes', [ClassesController::class, 'getClassesList']);
    Route::get('/classes/{classId}', [ClassesController::class, 'getClassDetails']);
    Route::get('/classes/{classId}/results', [ClassesController::class, 'getClassResults']);

    //Section Routes
    Route::get('/sections', [SectionController::class, 'getSectionList']);

    //Subject Routes
    Route::get('/subjects', [SubjectController::class, 'getSubjectsList']);
    Route::get('/classes/{classId}/subjects', [SubjectController::class, 'getSubjectListByClassId']);
    Route::get('/subjects/{subjectId}', [SubjectController::class, 'getSubjectDetails']);
    Route::get('/subjects/{subjectId}/results', [SubjectController::class, 'getSubjectResults']);

    //Chapter Routes
    Route::get('/classes/{classId}/subjects/{subjectId}/chapters', [ChapterController::class, 'getChapterListByClass']);
    Route::get('/subjects/{subjectId}/chapters', [ChapterController::class, 'getChapterListBySubject']);
    Route::get('/chapters/{chapterId}', [ChapterController::class, 'getChapterDetails']);
});
