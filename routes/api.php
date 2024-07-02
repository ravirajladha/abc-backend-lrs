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

/*
|
|
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
|
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


Route::group(['middleware' => ['check-auth-token', 'check-auth-type']], function () {

    //General Routes
    Route::post('/refresh-token', [RefreshController::class, 'refreshToken']);
    Route::post('/logout', [LogoutController::class, 'logout']);

    Route::post('/update-payment-status/{studentId}', [StudentController::class, 'updatePaymentStatus']);



    //Drop down routes
    Route::prefix('minimal')->group(function () {
        Route::get('/classes', \App\Http\Controllers\Api\Dropdown\ClassesController::class);
        Route::get('/classes/{classId}/subjects', \App\Http\Controllers\Api\Dropdown\SubjectController::class);
        Route::get('/tests/classes/{classId}/subjects', \App\Http\Controllers\Api\Dropdown\SubjectTestController::class);
        Route::get('/assessments', \App\Http\Controllers\Api\Dropdown\AssessmentController::class);
        Route::get('/elabs', \App\Http\Controllers\Api\Dropdown\ElabController::class);
        Route::get('/ebooks', \App\Http\Controllers\Api\Dropdown\EbookController::class);
        Route::get('/ebook-modules', \App\Http\Controllers\Api\Dropdown\EbookModuleController::class);
        Route::get('/ebook-sections', \App\Http\Controllers\Api\Dropdown\EbookSectionController::class);
        Route::get('/test', \App\Http\Controllers\Api\Dropdown\TermTestController::class);
        Route::get('/term-test-questions', \App\Http\Controllers\Api\Dropdown\TermTestQuestionController::class);
        Route::get('/term-test-questions-by-class-id', \App\Http\Controllers\Api\Dropdown\TermTestQuestionByClassIdController::class);
        Route::get('/get-assessment-questions-count', \App\Http\Controllers\Api\Dropdown\AssessmentQuestionController::class);
        Route::get('/project-reports', \App\Http\Controllers\Api\Dropdown\ProjectReportController::class);
        Route::get('/case-studies', \App\Http\Controllers\Api\Dropdown\CaseStudyController::class);
    });

    //Admin Login Routes
    Route::prefix('admin')->group(function () {
        Route::put('/update', [AdminController::class, 'updateDetails']);
        Route::get('/get-auth-details', [AuthController::class, 'getDetails']);
        Route::get('/dashboard', [AdminController::class, 'getDashboard']);


        Route::get('/get-dinacharya-logs', [DinacharyaController::class, 'getDinacharyaLogs']);
        Route::get('/send-dinacharya-messages', [DinacharyaController::class, 'sendDinacharyaMessages']);
        //School Routes
        Route::get('/schools', [AdminController::class, 'getSchoolsList']);
        Route::get('/public-schools', [AdminController::class, 'getPublicSchoolsList']);
        Route::get('/private-schools', [AdminController::class, 'getPrivateSchoolsList']);
        Route::get('/schools/{schoolId}', [SchoolController::class, 'getSchoolDetailsBySchoolId']);
        Route::get('/schools/{schoolId}/teachers', [SchoolController::class, 'getSchoolTeachersBySchoolId']);
        Route::get('/schools/{schoolId}/students', [SchoolController::class, 'getSchoolStudentsBySchoolId']);
        Route::get('/schools/{schoolId}/applications', [SchoolController::class, 'getSchoolApplicationsBySchoolId']);
        Route::post('/schools/store', [AdminController::class, 'storeSchoolDetails']);
        Route::put('/schools/{schoolId}/update', [SchoolController::class, 'updateSchoolDetails']);

        //Classes Routes
        Route::post('/class/store', [ClassesController::class, 'storeClassDetails']);
        Route::put('/class/{classId}/update', [ClassesController::class, 'updateClassDetails']);
        Route::delete('/class/{classId}/delete', [ClassesController::class, 'deleteClassDetails']);

        //Subject Routes
        Route::post('/subjects/store', [SubjectController::class, 'storeSubjectDetails']);
        Route::put('/subjects/{subjectId}/update', [SubjectController::class, 'updateSubjectDetails']);
        Route::delete('/subjects/{subjectId}/delete', [SubjectController::class, 'deleteSubjectDetails']);
        Route::get('/super-subjects', [SubjectController::class, 'getSuperSubjects']);

        //Subject Routes
        Route::post('/chapters/store', [ChapterController::class, 'storeChapterDetails']);
        Route::put('/chapters/{chapterId}/update', [ChapterController::class, 'updateChapterDetails']);
        Route::delete('/chapters/{chapterId}/delete', [ChapterController::class, 'deleteChapterDetails']);

        //eBook Routes
        Route::prefix('ebooks')->group(function () {
            Route::get('/', [EbookController::class, 'getEbookList']);
            Route::post('/store', [EbookController::class, 'storeEbookDetails']);
            Route::post('/{ebookId}/update', [EbookController::class, 'updateEbookDetails']);
            Route::delete('/{ebookId}/delete', [EbookController::class, 'deleteEbookDetails']);
            Route::get('/{ebookId}', [EbookController::class, 'getEbookDetails']);
            Route::get('/{ebookId}/getEbook', [EbookController::class, 'getEbook']);
        });

        Route::prefix('ebooks-modules')->group(function () {
            Route::get('/{ebookId}', [EbookModuleController::class, 'getEbookModuleList']);
            Route::post('/{ebookId}/store', [EbookModuleController::class, 'storeEbookModuleDetails']);
            Route::get('/{ebookModuleId}/detail', [EbookModuleController::class, 'getEbookModuleDetails']);
            Route::post('/{ebookModuleId}/update', [EbookModuleController::class, 'updateEbookModuleDetails']);
            Route::delete('/{ebookModuleId}', [EbookModuleController::class, 'deleteEbookModuleDetails']);
        });

        Route::prefix('ebooks-sections')->group(function () {
            Route::get('/{ebookSectionId}/detail', [EbookSectionController::class, 'getEbookSectionDetails']);
            Route::post('/{ebookSectionId}/update', [EbookSectionController::class, 'updateEbookSectionDetails']);
            Route::get('/{ebookId}/{ebookModuleId}', [EbookSectionController::class, 'getEbookSectionList']);
            Route::post('/{ebookId}/{ebookModuleId}/store', [EbookSectionController::class, 'storeEbookSectionDetails']);
            // Route::delete('/{ebookModuleId}', [EbookSectionController::class, 'deleteEbookSectionDetails']);
        });

        Route::prefix('ebooks-elements')->group(function () {
            Route::get('/{ebookSectionId }', [EbookElementController::class, 'getElementsByEbookSectionId']);

            Route::post('/{ebookSectionId}/store', [EbookElementController::class, 'storeEbookElementDetails']);

            Route::post('/update/{ebookElementId?}', [EbookElementController::class, 'updateEbookElementDetails']);
            Route::delete('/{ebookElementId}/delete', [EbookElementController::class, 'deleteElementDetails']);
            Route::get('/types', [EbookElementController::class, 'getElementTypeList']);
            Route::get('/get-element/{ebookElementId}', [EbookElementController::class, 'getElementDetailsById']);
            Route::post('/store-or-update/{ebookElementId?}', [EbookElementController::class, 'storeOrUpdateElement']);
            Route::delete('/{ebookElementId}/delete', [EbookElementController::class, 'deleteElement']);
            Route::get('/types', [EbookElementController::class, 'getElementTypeList']);
            Route::get('/get-element/{ebookElementId}', [EbookElementController::class, 'getElementById']);
        });

        //Project Report Routes
        Route::prefix('project-reports')->group(function () {
            Route::get('/', [ProjectReportController::class, 'getProjectReportList']);
            Route::post('/store', [ProjectReportController::class, 'storeProjectReportDetails']);
            Route::post('/{projectReportId}/update', [ProjectReportController::class, 'updateProjectReportDetails']);
            Route::delete('/{projectReportId}/delete', [ProjectReportController::class, 'deleteProjectReportDetails']);
            Route::get('/{projectReportId}', [ProjectReportController::class, 'getProjectReportDetails']);
            Route::get('/{projectReportId}/get-project-report', [ProjectReportController::class, 'getProjectReport']);
        });

        Route::prefix('project-report-modules')->group(function () {
            Route::get('/{projectReportId}', [ProjectReportModuleController::class, 'getProjectReportModuleList']);
            Route::post('/{projectReportId}/store', [ProjectReportModuleController::class, 'storeProjectReportModuleDetails']);
            Route::get('/{projectReportModuleId}/detail', [ProjectReportModuleController::class, 'getProjectReportModuleDetails']);
            Route::post('/{projectReportModuleId}/update', [ProjectReportModuleController::class, 'updateProjectReportModuleDetails']);
            // Route::delete('/{ebookModuleId}', [EbookModuleController::class, 'deleteEbookModuleDetails']);
        });

        Route::prefix('project-report-sections')->group(function () {
            Route::get('/{projectReportSectionId}/detail', [ProjectReportSectionController::class, 'getProjectReportSectionDetails']);
            Route::post('/{projectReportSectionId}/update', [ProjectReportSectionController::class, 'updateProjectReportSectionDetails']);
            Route::get('/{projectReportId}/{projectReportModuleId}', [ProjectReportSectionController::class, 'getProjectReportSectionList']);
            Route::post('/{projectReportId}/{projectReportModuleId}/store', [ProjectReportSectionController::class, 'storeProjectReportSectionDetails']);
            // Route::delete('/{ebookModuleId}', [EbookSectionController::class, 'deleteEbookSectionDetails']);
        });

        Route::prefix('project-report-elements')->group(function () {
            Route::post('store-or-update/{projectReportElementId?}', [ProjectReportElementController::class, 'storeOrUpdateElement']);
            Route::delete('/{projectReportElementId}/delete', [ProjectReportElementController::class, 'deleteElement']);
            Route::get('/get-element/{projectReportElementId}', [ProjectReportElementController::class, 'getElementById']);
        });

        //Case Study Routes
        Route::prefix('case-study')->group(function () {
            Route::get('/', [CaseStudyController::class, 'getCaseStudyList']);
            Route::post('/store', [CaseStudyController::class, 'storeCaseStudyDetails']);
            Route::post('/{caseStudyId}/update', [CaseStudyController::class, 'updateCaseStudyDetails']);
            Route::delete('/{caseStudyId}/delete', [CaseStudyController::class, 'deleteCaseStudyDetails']);
            Route::get('/{caseStudyId}', [CaseStudyController::class, 'getCaseStudyDetails']);
            Route::get('/{caseStudyId}/get-case-study', [CaseStudyController::class, 'getCaseStudy']);
        });

        Route::prefix('case-study-modules')->group(function () {
            Route::get('/{caseStudyId}', [CaseStudyModuleController::class, 'getCaseStudyModuleList']);
            Route::post('/{caseStudyId}/store', [CaseStudyModuleController::class, 'storeCaseStudyModuleDetails']);
            Route::get('/{caseStudyModuleId}/detail', [CaseStudyModuleController::class, 'getCaseStudyModuleDetails']);
            Route::post('/{caseStudyModuleId}/update', [CaseStudyModuleController::class, 'updateCaseStudyModuleDetails']);
            // Route::delete('/{ebookModuleId}', [EbookModuleController::class, 'deleteEbookModuleDetails']);
        });

        Route::prefix('case-study-sections')->group(function () {
            Route::get('/{caseStudySectionId}/detail', [CaseStudySectionController::class, 'getCaseStudySectionDetails']);
            Route::post('/{caseStudySectionId}/update', [CaseStudySectionController::class, 'updateCaseStudySectionDetails']);
            Route::get('/{caseStudyId}/{caseStudyModuleId}', [CaseStudySectionController::class, 'getCaseStudySectionList']);
            Route::post('/{caseStudyId}/{caseStudyModuleId}/store', [CaseStudySectionController::class, 'storeProjectReportSectionDetails']);
            // Route::delete('/{ebookModuleId}', [EbookSectionController::class, 'deleteEbookSectionDetails']);
        });

        Route::prefix('case-study-elements')->group(function () {
            Route::post('store-or-update/{caseStudyElementId?}', [CaseStudyElementController::class, 'storeOrUpdateElement']);
            Route::delete('/{projectReportElementId}/delete', [CaseStudyElementController::class, 'deleteElement']);
            Route::get('/get-element/{projectReportElementId}', [CaseStudyElementController::class, 'getElementById']);
        });
        Route::prefix('readable-courses')->group(function () {
            Route::get('/', [ReadableCoursesController::class, 'getAllReadableCourses']);
            Route::post('/store', [ReadableCoursesController::class, 'storeReadableCourse']);
        });
        //eLab Routes
        Route::prefix('elabs')->group(function () {
            Route::get('/get-elab-participant/{elabId}', [ElabController::class, 'getElabParticipants']);
            Route::get('/get-elab-submitted-code/{id}', [ElabController::class, 'getElabSubmittedCodeById']);
            Route::delete('/delete-elab-participant-codebase/{id}/delete', [ElabController::class, 'deleteElabParticipantCodebase']);
            Route::get('/submission/{userId}/{elabId}', [ElabController::class, 'getElabSubmissionByStudent']);
            Route::get('/get-active-elabs', [ElabController::class, 'getActiveElabs']);
            Route::get('/get-selected-active-elabs/{classId}/{subjectId?}', [ElabController::class, 'fetchSelectedActiveElabs']);
            Route::get('/get-selected-active-elabs-without-subject/{classId}/{subjectId?}', [ElabController::class, 'fetchSelectedActiveElabs']);
            Route::get('/', [ElabController::class, 'getElabList']);
            Route::post('/store', [ElabController::class, 'storeElabDetails']);
            Route::get('/{elabId}/{studentId?}', [ElabController::class, 'getElabDetailsByElabId']);
            Route::put('/{elabId}/update-status', [ElabController::class, 'updateElabStatus']);
            Route::put('/{elabId}/update', [ElabController::class, 'updateElabDetails']);
            Route::delete('/{elabId}/delete', [ElabController::class, 'deleteElabDetails']);
        });

        //Video Routes
        Route::prefix('videos')->group(function () {
            Route::get('/', [VideoController::class, 'getAllVideos']);
            Route::post('/store', [VideoController::class, 'storeVideoDetails']);
            Route::get('/{videoId}', [VideoController::class, 'getVideoDetails']);
            Route::put('/{videoId}/update', [VideoController::class, 'updateVideoDetails']);
            Route::delete('/{videoId}/delete', [VideoController::class, 'deleteVideoDetails']);
        });

        //Term Test Routes
        Route::prefix('term-tests')->group(function () {

            Route::get('/', [TermTestController::class, 'getAllTermTests']);
            Route::get('/{testId}', [TermTestController::class, 'getTermTestDetails']);
            Route::post('/store', [TermTestController::class, 'storeTermTestDetails']);
            Route::get('/{testId}/results', [TermTestController::class, 'showTermTestResults']);
            Route::put('/{testId}/update', [TermTestController::class, 'updateTermTestDetails']);
            Route::delete('/{testId}/delete', [TermTestController::class, 'destroyTermTestDetails']);

            Route::get('/availability/{subjectId}', [TermTestController::class, 'checkTermAvailability']);
        });

        //Assessment Questions Routes
        Route::prefix('tests-questions')->group(function () {
            Route::get('/', [TermTestQuestionController::class, 'getAllTermTestQuestions']);
            Route::post('/store', [TermTestQuestionController::class, 'store']);
            Route::get('/{termTestQuestionId}/show', [TermTestQuestionController::class, 'getTermTestQuestionDetails']);
            Route::put('/{termTestQuestionId}/update', [TermTestQuestionController::class, 'update']);
            Route::delete('/{termTestQuestionId}/delete', [TermTestQuestionController::class, 'delete']);
        });

        //Assessment Questions Routes
        Route::prefix('assessment-questions')->group(function () {
            Route::get('/', [AssessmentQuestionController::class, 'getAllAssessmentQuestions']);
            Route::post('/store', [AssessmentQuestionController::class, 'storeAssessmentQuestionDetails']);
            Route::get('/{assessmentQuestionId}/show', [AssessmentQuestionController::class, 'getAssessmentQuestionById']);
            Route::put('/{assessmentQuestionId}/update', [AssessmentQuestionController::class, 'updateAssessmentQuestionDetails']);
            Route::delete('/{assessmentQuestionId}/delete', [AssessmentQuestionController::class, 'deleteAssessmentQuestionDetails']);
        });

        //Assessment Routes
        Route::prefix('assessments')->group(function () {
            Route::get('/', [AssessmentController::class, 'getAllAssessments']);
            Route::get('/{assessmentId}', [AssessmentController::class, 'getAssessmentDetails']);
            Route::get('/{assessmentId}/results', [AssessmentController::class, 'showAssessmentsResults']);
            Route::get('/{assessmentId}/show', [AssessmentController::class, 'getAssessmentDetailsWithQuestions']);
            Route::post('/store', [AssessmentController::class, 'storeAssessmentDetails']);
            Route::put('/{assessmentId}/update', [AssessmentController::class, 'updateAssessmentDetails']);
            Route::delete('/{assessmentId}/delete', [AssessmentController::class, 'deleteAssessmentDetails']);
        });

        //Mini Project Routes
        Route::prefix('mini-projects')->group(function () {
            Route::get('/', [MiniProjectController::class, 'getMiniProjects']);
            Route::delete('/delete-mini-project-participant/{miniProjectStudentId}/delete', [MiniProjectController::class, 'deleteMiniProjectParticipant']);
            Route::delete('/delete-mini-project/{miniProjectId}/delete', [MiniProjectController::class, 'deleteMiniProject']);
            Route::delete('/delete-mini-project-task/{miniProjectTaskId}/delete', [MiniProjectController::class, 'deleteMiniProjectTask']);
            Route::get('/{miniProjectId}', [MiniProjectController::class, 'getMiniProjectDetails']);
            Route::get('/participants/{miniProjectId}', [MiniProjectController::class, 'getMiniProjectParticipants']);
            Route::post('/{miniProjectId}/update', [MiniProjectController::class, 'update']);
            Route::post('store', [MiniProjectController::class, 'storeMiniProjectDetails']);
        });

        //Mini Project Task Routes
        Route::prefix('mini-project-tasks')->group(function () {
            Route::post('store', [MiniProjectController::class, 'storeMiniProjectTaskDetails']);
            Route::get('/all/{miniProjectId}', [MiniProjectController::class, 'getAllMiniProjectTasksByProjectId']);
            Route::get('/{projectId}/{studentId}', [MiniProjectController::class, 'getMiniProjectTasksByProjectId']);
            Route::get('/{miniProjectTaskId}', [MiniProjectController::class, 'getMiniProjectTasksById']);
            Route::post('/{miniProjectTaskId}/update', [MiniProjectController::class, 'updateTask']);
        });

        Route::prefix('mini-project-task-processes')->group(function () {
            Route::post('start-mini-project', [MiniProjectController::class, 'startStudentMiniProject']);
        });

        //Internship
        Route::prefix('internships')->group(function () {
            Route::get('/', [InternshipController::class, 'getInternships']);
            Route::delete('/delete-internship-participant/{internshipStudentId}/delete', [InternshipController::class, 'deleteInternshipParticipant']);
            Route::delete('/delete-internship/{internshipId}/delete', [InternshipController::class, 'deleteInternship']);
            Route::delete('/delete-internship-task/{internshipTaskId}/delete', [InternshipController::class, 'deleteInternshipTask']);
            Route::get('/{internshipId}', [InternshipController::class, 'getInternshipDetails']);
            Route::get('/participants/{internshipId}', [InternshipController::class, 'getInternshipParticipants']);
            Route::post('/{internshipId}/update', [InternshipController::class, 'update']);
            Route::post('store', [InternshipController::class, 'storeInternshipDetails']);
        });
        Route::prefix('internship-tasks')->group(function () {

            Route::post('store', [InternshipController::class, 'storeInternshipTaskDetails']);

            Route::get('/all/{internshipId}', [InternshipController::class, 'getAllInternshipTasksByProjectId']);
            Route::get('/{internshipId}/{studentId}', [InternshipController::class, 'getInternshipTasksByProjectId']);
            Route::get('/{internshipTaskId}', [InternshipController::class, 'getInternshipTasksById']);
            Route::post('/{internshipTaskId}/update', [InternshipController::class, 'updateTask']);
        });

        Route::prefix('internship-task-processes')->group(function () {
            Route::post('start-internship', [InternshipController::class, 'startStudentInternship']);
        });

        //Job Routes
        Route::prefix('jobs')->group(function () {
            Route::get('/', [JobController::class, 'getJobList']);
            Route::post('/', [JobController::class, 'storeJobDetails']);
            Route::get('/get-job-test-results', [JobController::class, 'getStudentJobTestDetailsByJobApplicationId']);
            Route::get('/{jobId}', [JobController::class, 'getJobDetails']);
            Route::post('/{jobId}/update', [JobController::class, 'updateJobDetails']);
            Route::delete('/{jobId}', [JobController::class, 'deleteJobDetails']);
            Route::get('/{jobId}/applications', [JobController::class, 'getStudentJobApplications']);
            // Route::get('/{jobId}/applications', [JobController::class, 'getStudentJobApplications']);

            Route::get('/get-job-test-results', [JobController::class, 'getStudentJobTestDetailsByJobApplicationId']);
        });

        Route::post('/fee/validate-referral-name', [FeesController::class, 'validateReferralName']);
        Route::post('/fees/store', [FeesController::class, 'storeFeeDetails']);
        Route::get('/fee', [FeesController::class, 'getFee']);
        Route::get('/fees', [FeesController::class, 'getFeesList']);
        Route::post('/fees/{feeId}/update', [FeesController::class, 'updateFeeDetails']);
        Route::post('/fees/update', [FeesController::class, 'updateFee']);
        Route::get('/fees/{feeId}', [FeesController::class, 'getFeeDetailsById']);

        Route::prefix('recruiters')->group(function () {
            Route::get('/', [RecruiterController::class, 'getRecruitersList']);
            Route::get('/{recruiterId}', [RecruiterController::class, 'getRecruiterDetails']);
            Route::post('/store', [RecruiterController::class, 'storeRecruiterDetails']);
            Route::get('/{recruiterId}/assign', [RecruiterController::class, 'getTeacherClassesAndSubjects']);
            Route::post('/{recruiterId}/assign', [RecruiterController::class, 'storeOrUpdateTeacherClassesAndSubjects']);
            Route::put('/{recruiterId}/update', [RecruiterController::class, 'updateRecruiterDetails']);
            Route::delete('/{recruiterId}/delete', [RecruiterController::class, 'deleteTeacherDetails']);
        });
        Route::prefix('quotes')->group(function () {
            Route::get('/', [QuoteController::class, 'getQuoteList']);
            Route::get('/{quoteId}', [QuoteController::class, 'getQuoteDetails']);
            Route::post('/store', [QuoteController::class, 'storeQuote']);
            Route::post('/bulk-store', [QuoteController::class, 'bulkStoreQuote']);
            Route::put('/{quoteId}/update', [QuoteController::class, 'updateQuote']);
            Route::delete('/{recruiterId}/delete', [QuoteController::class, 'deleteQuote']);
        });

        Route::prefix('zoom-calls')->group(function () {
            Route::get('/', [ZoomCallController::class, 'getZoomCallList']);
            Route::get('/{zoomCallId}', [ZoomCallController::class, 'getZoomCallById']);
            Route::post('/store', [ZoomCallController::class, 'storeZoomCall']);
            Route::post('/{zoomCallId}/edit', [ZoomCallController::class, 'updateZoomCall']);
        });

        Route::prefix('forums')->group(function () {
            Route::get('/questions', [ForumController::class, 'getForumQuestionsList']);
            Route::get('/questions/{questionId}/answers', [ForumController::class, 'getForumQuestionAnswers']);
        });
        Route::get('/transactions', [FeesController::class, 'getTransactions']);
    });

    //School Login Routes
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

    //Student Login Routes
    Route::prefix('student')->group(function () {
        Route::post('/mini-project-tasks/complete-status-for-student', [MiniProjectController::class, 'completeStatusForStudent']);
        Route::post('/internship/generate-certificate', [InternshipController::class, 'generateCertificate']);

        Route::get('/subjects', [SubjectController::class, 'getStudentSubjectsWithResults']);
        Route::get('/get-report-card', [SubjectController::class, 'getStudentReportCard']);
        Route::get('/get-subject-results', [TermTestResultController::class, 'getStudentTestDetailsBySubjectId']);

        Route::get('/my-courses', [SubjectController::class, 'getMyCourses']);

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

    //Teacher Login Routes
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

    //Parent Login Routes
    Route::prefix('parent')->group(function () {
        Route::get('/get-dashboard', [ParentController::class, 'getDashboard']);
        Route::get('/get-auth-details', [AuthController::class, 'getDetails']);

        Route::put('/update', [ParentController::class, 'updateParentSettings']);

        Route::get('/applications/{parentPhone}', [ParentController::class, 'getApplications']);
        Route::post('/application', [ParentController::class, 'storeChildrenApplication']);
        Route::post('/get-application/{applicationId}', [ParentController::class, 'getChildrenApplication']);

        Route::get('/get-children', [ParentController::class, 'getChildren']);
        Route::get('/get-student-info', [ParentController::class, 'getStudentDetails']);
        Route::get('/get-report-card', [SubjectController::class, 'getStudentReportCard']);

        Route::get('/{parentId}', [ParentController::class, 'getParentDetails']);
        Route::put('/{parentId}/update', [ParentController::class, 'updateParentDetails']);

        // Route::get('student/{studentId}/results/{studentId}', [AssessmentController::class, 'getAssessmentResultsWithVideo']);

        Route::post('/students/store', [StudentController::class, 'storeStudentDetails']);
        Route::get('/get-fee/{classId}', [FeesController::class, 'getFeeByClass']);

        // for mobile
        Route::get('chapter/{chapterId}/results/{studentId}', [AssessmentController::class, 'getAssessmentResultsWithVideo']);
    });


    Route::prefix('recruiter')->group(function () {
        Route::get('/dashboard', [RecruiterController::class, 'getDashboard']);
        Route::put('/{recruiterId}/update', [RecruiterController::class, 'updateRecruiterPassword']);

        Route::prefix('job-tests')->group(function () {

            Route::get('/', [JobTestController::class, 'getAllTermTests']);
            Route::get('/{testId}', [JobTestController::class, 'getTermTestDetails']);
            Route::post('/store', [JobTestController::class, 'storeTermTestDetails']);
            Route::get('/{testId}/results', [JobTestController::class, 'showTermTestResults']);
            Route::put('/{testId}/update', [JobTestController::class, 'updateTermTestDetails']);
            Route::delete('/{testId}/delete', [JobTestController::class, 'destroyTermTestDetails']);
            Route::get('/availability/{subjectId}', [JobTestController::class, 'checkTermAvailability']);
        });
        Route::prefix('jobs')->group(function () {

            Route::get('/{recruiterId?}', [JobController::class, 'getJobsByRecruiterId']);
        });

        //Assessment Questions Routes
        Route::prefix('job-tests-questions')->group(function () {
            Route::get('/', [JobTestQuestionController::class, 'getAllTermTestQuestions']);
            Route::post('/store', [JobTestQuestionController::class, 'store']);
            Route::get('/{termTestQuestionId}/show', [JobTestQuestionController::class, 'getTermTestQuestionDetails']);
            Route::put('/{termTestQuestionId}/update', [JobTestQuestionController::class, 'update']);
            Route::delete('/{termTestQuestionId}/delete', [JobTestQuestionController::class, 'delete']);
        });
    });


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
