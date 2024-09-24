<?php

use Illuminate\Support\Facades\Route;

// Combine all controller imports
use App\Http\Controllers\Api\{
    PlacementController,
    RecruiterController,
    AuthController,
    AdminController,
    EbookController,
    ForumController,
    VideoController,
    SchoolController,
    ChapterController,
    SubjectController,
    StudentController,
    CourseController,
    TestController,
    CaseStudyController,
    AssessmentController,
    EbookModuleController,
    EbookElementController,
    EbookSectionController,
    ProjectReportController,
    CaseStudyModuleController,
    ReadableCoursesController,
    CaseStudyElementController,
    CaseStudySectionController,
    TestQuestionController,
    AssessmentQuestionController,
    ProjectReportModuleController,
    ProjectReportElementController,
    FeesController,
    ZoomCallController,
    ProjectReportSectionController,
    ElabController,
    MiniProjectController,
    InternshipController
};

Route::prefix('admin')->group(function () {
    Route::put('/update', [AdminController::class, 'updateDetails']);
    Route::get('/get-auth-details', [AuthController::class, 'getDetails']);
    Route::get('/dashboard', [AdminController::class, 'getDashboard']);

    //School Routes, is pending currently
    Route::get('/internshipAdmins', [AdminController::class, 'getInternshipAdminsList']);
    Route::get('/public-internshipAdmins', [AdminController::class, 'getPublicSchoolsList']);
    Route::get('/private-internshipAdmins', [AdminController::class, 'getPrivateSchoolsList']);
    Route::get('/internshipAdmins/{internshipAdminId}', [SchoolController::class, 'getInternshipAdminDetailsByInternshipAdminId']);
    Route::get('/internshipAdmins/{internshipAdminId}/trainers', [SchoolController::class, 'getSchoolTrainersBySchoolId']);
    Route::get('/internshipAdmins/{internshipAdminId}/students', [SchoolController::class, 'getSchoolStudentsByInternshipAdminId']);
    Route::post('/internshipAdmins/store', [AdminController::class, 'storeInternshipAdminDetails']);
    Route::put('/internshipAdmins/{internshipAdminId}/update', [SchoolController::class, 'updateSchoolDetails']);

    // Retrieve a list of subjects
    Route::get('/subjects', [SubjectController::class, 'getSubjectsList']);

    // Retrieve details of a specific subject
    Route::get('/subjects/{subjectId}', [SubjectController::class, 'getSubjectDetails']);

    // Retrieve results associated with a specific subject
    Route::get('/subjects/{subjectId}/results', [SubjectController::class, 'getSubjectResults']);

    // Create a new subject
    Route::post('/subject/store', [SubjectController::class, 'storeSubjectDetails']);

    // Update details of an existing subject
    Route::put('/subject/{subjectId}/update', [SubjectController::class, 'updateSubjectDetails']);

    // Delete a subject
    Route::delete('/subject/{subjectId}/delete', [SubjectController::class, 'deleteSubjectDetails']);

    // Retrieve a list of courses associated with a specific subject
    Route::get('/subjects/{subjectId}/courses', [CourseController::class, 'getCourseListBySubjectId']);

    // Retrieve details of a specific course
    Route::get('/courses/{courseId}', [CourseController::class, 'getCourseDetails']);

    // Create a new course
    Route::post('/courses/store', [CourseController::class, 'storeCourseDetails']);

    // Update details of an existing course
    Route::put('/courses/{courseId}/update', [CourseController::class, 'updateCourseDetails']);

    // Delete a course
    Route::delete('/courses/{courseId}/delete', [CourseController::class, 'deleteCourseDetails']);

    // Retrieve a list of chapters associated with a specific course (and subject)
    Route::get('/subjects/{subjectId}/courses/{courseId}/chapters', [ChapterController::class, 'getChapterListBySubject']);

    // Retrieve a list of chapters associated with a specific subject (without specifying course)
    Route::get('/subjects/{subjectId}/chapters', [ChapterController::class, 'getChapterListBySubject']);

    // Retrieve details of a specific chapter
    Route::get('/chapters/{chapterId}', [ChapterController::class, 'getChapterDetails']);

    // Create a new chapter
    Route::post('/chapters/store', [ChapterController::class, 'storeChapterDetails']);

    // Update details of an existing chapter
    Route::put('/chapters/{chapterId}/update', [ChapterController::class, 'updateChapterDetails']);

    // Delete a chapter
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
        Route::get('/get-selected-active-elabs/{subjectId}/{courseId?}', [ElabController::class, 'fetchActiveElabsForSubject']);
        Route::get('/get-active-elabs-for-subject/{subjectId}', [ElabController::class, 'fetchActiveElabsForSubject']);

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
    Route::prefix('tests')->group(function () {

        Route::get('/', [TestController::class, 'getAllTests']);
        Route::get('/{testId}', [TestController::class, 'getTestDetails']);
        Route::post('/store', [TestController::class, 'storeTestDetails']);
        Route::get('/{testId}/results', [TestController::class, 'showTestResults']);
        Route::put('/{testId}/update', [TestController::class, 'updateTestDetails']);
        Route::delete('/{testId}/delete', [TestController::class, 'destroyTestDetails']);

        Route::get('/availability/{subjectId}', [TestController::class, 'checkAvailability']);
    });

    //Assessment Questions Routes
    Route::prefix('tests-questions')->group(function () {
        Route::get('/', [TestQuestionController::class, 'getAllTestQuestions']);
        Route::post('/store', [TestQuestionController::class, 'store']);
        Route::get('/{TestQuestionId}/show', [TestQuestionController::class, 'getTestQuestionDetails']);
        Route::put('/{TestQuestionId}/update', [TestQuestionController::class, 'update']);
        Route::delete('/{TestQuestionId}/delete', [TestQuestionController::class, 'delete']);
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
        Route::get('/', [PlacementController::class, 'getJobList']);
        Route::post('/', [PlacementController::class, 'storeJobDetails']);
        Route::get('/get-job-test-results', [PlacementController::class, 'getStudentJobTestDetailsByJobApplicationId']);
        Route::get('/{jobId}', [PlacementController::class, 'getJobDetails']);
        Route::post('/{jobId}/update', [PlacementController::class, 'updateJobDetails']);
        Route::delete('/{jobId}', [PlacementController::class, 'deleteJobDetails']);
        Route::get('/{jobId}/applications', [PlacementController::class, 'getStudentJobApplications']);
        // Route::get('/{jobId}/applications', [PlacementController::class, 'getStudentJobApplications']);

        Route::get('/get-job-test-results', [PlacementController::class, 'getStudentJobTestDetailsByJobApplicationId']);
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
        Route::get('/{recruiterId}/assign', [RecruiterController::class, 'getTrainerSubjectsAndCourses']);
        Route::post('/{recruiterId}/assign', [RecruiterController::class, 'storeOrUpdateTrainerSubjectsAndCourses']);
        Route::put('/{recruiterId}/update', [RecruiterController::class, 'updateRecruiterDetails']);
        Route::delete('/{recruiterId}/delete', [RecruiterController::class, 'deleteTrainerDetails']);
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
        Route::post('/update-status', [ForumController::class, 'updateStatus']);
        Route::post('/answer/update-status', [ForumController::class, 'updateAnswerStatus']);
    });
    Route::get('/transactions', [FeesController::class, 'getTransactions']);

    Route::post('students/update-status', [StudentController::class, 'updateStatus']);
    Route::get('/students/get-public-students', [StudentController::class, 'getPublicStudentDetailsFromStudent']);


    Route::prefix('students')->group(function () {

    Route::put('/{studentId}/update', [StudentController::class, 'updateStudentDetails']);
    Route::get('/get-student-details/{studentId}', [StudentController::class, 'getStudentDetailsFromStudent']);
    });
});
