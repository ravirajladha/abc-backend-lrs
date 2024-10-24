<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\Student;
use App\Models\Subject;
use App\Models\ChapterLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Services\Admin\ResultService;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use App\Services\Student\ResultService as StudentResultService;
use App\Models\RatingReview;
use App\Models\Trainer;
use App\Models\ZoomCallUrl;

//changed
//error in $resultsService->getCourseResults
class CourseController extends BaseController
{
    /**
     * Display a listing of the subjects.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCoursesList()
    {
        $courses = Course::get();
        return $this->sendResponse(['courses' => $courses]);
    }

    public function getMyCourses()
    {
        $studentId = Student::where('auth_id', $this->getLoggedUserId())->value('auth_id');

        if (!$studentId) {
            return $this->sendResponse(['courses' => []], 'No courses found.');
        }
        // Subquery to get the average rating and total ratings for each course
        $ratingsSubquery = RatingReview::select('course_id')
        ->selectRaw('AVG(rating) as average_rating')
        ->selectRaw('COUNT(rating) as total_ratings')
        ->groupBy('course_id');

        $courses = Course::join('subjects', 'courses.subject_id', '=', 'subjects.id')
            ->join('chapters', 'courses.id', '=', 'chapters.course_id')
            ->join('chapter_logs', 'chapters.id', '=', 'chapter_logs.chapter_id')
            ->leftJoinSub($ratingsSubquery, 'ratings', function ($join) {
                $join->on('courses.id', '=', 'ratings.course_id');
            })
            ->leftJoin('auth', 'auth.id', '=', 'courses.trainer_id')
            ->select('courses.id', 'courses.name', 'courses.image','courses.access_validity', 'subjects.name as subject_name', DB::raw('IFNULL(ratings.average_rating, 0) as average_rating'), // Handle null ratings
                DB::raw('IFNULL(ratings.total_ratings, 0) as total_ratings'),
                'auth.username as trainer_name',
                DB::raw('MIN(chapter_logs.created_at) as start_date'))
            ->where('chapter_logs.video_complete_status', 1)
            ->where('chapter_logs.student_id', $this->getLoggedUserId())
            ->groupBy('courses.id', 'courses.name','trainer_name', 'courses.image','courses.access_validity', 'subjects.name', 'average_rating', 'total_ratings')
            ->get();

        foreach ($courses as $course) {

            $chapterIds = DB::table('chapters')->where('course_id', $course->id)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('videos')
                        ->whereRaw('videos.chapter_id = chapters.id');
                })
                ->pluck('id')->toArray();

            $completedChaptersCount = 0;
            if (!empty($chapterIds)) {
                $completedChaptersCount = ChapterLog::where('student_id', $this->getLoggedUserId())
                    ->whereIn('chapter_id', $chapterIds)
                    ->where('video_complete_status', 1)
                    ->where('assessment_complete_status', 1)
                    ->count();

                $allChaptersCompleted = $completedChaptersCount == count($chapterIds);
                $course->chapter_completed = $allChaptersCompleted;
            } else {
                $course->chapter_completed = false;
            }

            $course->completePercentage = !empty($chapterIds) ? ($completedChaptersCount / count($chapterIds)) * 100 : 0;

            $latestTest = DB::table('tests')
                ->where('course_id', $course->id)
                ->where('status', 1)
                ->select('id', 'description')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestTest) {
                $latestTestId = $latestTest->id;
                $testDescription = $latestTest->description;

                $latestTestResults = DB::table('test_results as results')
                    ->where('results.student_id', $studentId)
                    ->where('results.test_id', $latestTestId)
                    ->exists();

                if (!$latestTestResults) {
                    $course->latest_test_id = $latestTestId;
                    $course->latest_test = $latestTest;
                    $course->testDescription = $testDescription;
                } else {
                    $course->latest_test_id = null;
                    $course->latest_test = null;
                    $course->testDescription = null;
                }
            } else {
                $course->latest_test_id = null;
                $course->latest_test = null;
                $course->testDescription = null;
            }

            $studentResult = DB::table('test_results as results')
                ->select('results.*')
                ->leftJoin('tests as test', 'test.id', 'results.test_id')
                ->where('results.student_id', $studentId)
                ->where('test.course_id', $course->id)
                ->orderBy('created_at', 'desc')
                ->get();


            $course->results = $studentResult;
            $today = date('Y-m-d');
            $currentTime = date('H:i');
            $liveSessions = ZoomCallUrl::where('date', $today)
            // ->where('time', '>=', $currentTime)
            ->where('course_id', $course->id)
            ->get();
            $course->liveSessions = $liveSessions;

            // Trainer by course
            // $trainer = DB::table('trainer_courses as ts')
            //     ->where('ts.course_id', $course->id)
            //     ->leftJoin('trainers as t', 't.id', 'ts.trainer_id')
            //     ->first();

            // if ($trainer) {
            //     $course->trainer_name = $trainer->name;
            // } else {
            //     $course->trainer_name = 'No trainer assigned';
            // }
        }

        return $this->sendResponse(['courses' => $courses]);
    }

    //still in progress
    /**
     * Display the student report card.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function getStudentReportCard(Request $request)
    {
        // Log::info(['student', $request->studentId]);

        Log::info('Received subjectId:', ['studentUd' => $request->studentId]);


        $studentId = $request->studentId; //StudentId from students table
        // $subjectId = $request->subjectId;
        // $sectionId = $request->sectionId;
        $report_card = [];

        if (!($studentId)) {
            return $this->sendError('Failed to fetch student results.');
        }

        $resultsService = new StudentResultService();

        // $report_card['course_results'] = $resultsService->getCourseResults($studentId, $subjectId);

        $report_card['total_marks'] = $resultsService->getTotalMarks($studentId);
        $report_card['base_total_marks'] = $resultsService->getTestTotalMarks($studentId);
        //class rank = subject rank
        $report_card['subject_rank'] = $resultsService->getSubjectRank($studentId);
        // $report_card['section_rank'] = $resultsService->getSectionRank($studentId, $sectionId);
        $report_card['assessment_results'] = $resultsService->getAverageAssessmentScore($studentId);

        return $this->sendResponse(['report_card' => $report_card], 'Report card fetched successfully.');
    }

    /**
     * Display a listing of the subjects with ClassId.
     *
     * @param $classId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCourseListBySubjectId($subjectId)
    {
        // Log the received subjectId
        Log::info('Received subjectId:', ['subjectId' => $subjectId]);

        $res = [];

        // Validate subjectId
        $validator = Validator::make(['subjectId' => $subjectId], [
            'subjectId' => 'required',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return $this->sendValidationError($validator);
        } else {
            $subject = Subject::where('id', $subjectId)->first();
            // Log the subject data retrieved
            Log::info('Retrieved subject:', ['subject' => $subject]);

            $courses = DB::table('courses as cou')
                ->select('cou.id', 'cou.name', 'cou.image', )
                ->where('cou.subject_id', $subjectId)
                ->get();

            // Log the courses retrieved
            Log::info('Retrieved courses:', ['courses' => $courses]);

            if ($subject) {
                $res = [
                    'subject_id' => $subjectId,
                    'subject' => $subject->name,
                    'courses' => $courses,
                ];

                // Log the final response data
                Log::info('Final response:', $res);
            } else {
                Log::error('Subject not found:', ['subjectId' => $subjectId]);
                return $this->sendError('Course not found!');
            }
        }

        // Log the response before sending it back
        Log::info('Sending response:', ['response' => $res]);

        return $this->sendResponse($res);
    }


    /**
     * Display a listing of the subjects with results for students by subjectId.
     *
     * @param $subjectId
     * @return \Illuminate\Http\JsonResponse
     */


    public function getStudentCoursesWithResults(Request $request)
    {
        // Enable query log if you need it for debugging (optional)
        // DB::enableQueryLog();

        // Subquery to get the average rating and total ratings for each course
        $ratingsSubquery = RatingReview::select('course_id')
            ->selectRaw('AVG(rating) as average_rating')
            ->selectRaw('COUNT(rating) as total_ratings')
            ->groupBy('course_id');

        // Fetch the courses with their respective ratings
        $courses = DB::table('courses as cou')
            ->join('subjects', 'cou.subject_id', '=', 'subjects.id')
            ->leftJoin('auth as trainer', 'cou.trainer_id', '=', 'trainer.id')
            ->leftJoinSub($ratingsSubquery, 'ratings', function ($join) {
                $join->on('cou.id', '=', 'ratings.course_id');
            })
            ->select(
                'cou.id',
                'cou.name',
                'cou.image',
                'subjects.name as subject_name',
                'trainer.username as trainer_name',
                DB::raw('IFNULL(ratings.average_rating, 0) as average_rating'), // Handle null ratings
                DB::raw('IFNULL(ratings.total_ratings, 0) as total_ratings')
            )
            ->get();

        // Check if courses are found
        if ($courses->isNotEmpty()) {
            return $this->sendResponse(['courses' => $courses], 'Courses fetched successfully.');
        } else {
            return $this->sendError('No courses found!', 404);
        }
    }



    public function storeCourseDetails(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'subject_id' => 'required',
            'trainer_id' => 'required|exists:auth,id',
            'course_name' => 'required|max:75|unique:courses,name',
            'course_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'course_video' => 'required|mimes:mp4,mov,avi|max:10000',
            'access_validity' => 'required',
            'benefits' => 'required|string',
            'description' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $loggedUserId = $this->getLoggedUserId();

            $course = new Course();
            $course->name = $request->course_name;
            $course->subject_id = $request->subject_id;
            $course->trainer_id = $request->trainer_id;
            $course->access_validity = $request->access_validity;

            // if ($request->course_type == 3) {
            //     $subject->super_subject_id = $request->super_subject_id;
            // }
            if (!empty($request->file('course_image'))) {
                $extension = $request->file('course_image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $course->image = $request->file('course_image')->move(('uploads/images/course'), $filename);
            } else {
                $course->image = null;
            }

            if (!empty($request->file('course_video'))) {
                $videoExtension = $request->file('course_video')->extension();
                $videoFilename = Str::random(4) . time() . '.' . $videoExtension;
                $course->video = $request->file('course_video')->move(('uploads/videos/course'), $videoFilename);
            } else {
                $course->video = null;
            }

            $course->benefits = $request->benefits;
            $course->description = $request->description;
            $course->created_by = $loggedUserId;
            if ($course->save()) {
                return $this->sendResponse([], 'Course created successfully.');
            } else {
                return $this->sendResponse([], 'Failed to create subject.');
            }
        }
    }

    public function getCourseDetails($courseId)
    {
        $validator = Validator::make(['courseId' => $courseId], [
            'courseId' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $course = Course::where('id', $courseId)->first();
            return $this->sendResponse(['course' => $course]);
        }
    }
    public function updateCourseDetails(Request $request, $courseId)
    {
        $course = Course::find($courseId);

        if (!$course) {
            return $this->sendError('Course not found.');
        }
        $validator = Validator::make($request->all(), [
            'course_name' => [
                'required',
                'max:75',
                Rule::unique('courses', 'name')->ignore($courseId),
            ],
            'course_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'trainer_id' => 'required|exists:auth,id',
            'course_video' => 'video|mimes:mp4,mov,avi|max:10000',
            'access_validity' => 'required',
            'benefits' => 'required|string',
            'description' => 'required|string',
        ]);


        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $course = Course::find($courseId);

        if (!$request->has('course_name')) {
            return $this->sendError('Course name is required.');
        }
        $loggedUserId = $this->getLoggedUserId();
        $course->updated_by = $loggedUserId;
        // Update course details
        $course->name = $request->course_name;
        $course->subject_id = $request->subject_id;
        $course->trainer_id = $request->trainer_id;
        $course->access_validity = $request->access_validity;
        $course->benefits = $request->benefits;
        $course->description = $request->description;

        // Handle course image update
        if ($request->hasFile('course_image')) {
            if ($course->image) {
                File::delete(public_path($course->image));
            }
            $extension = $request->file('course_image')->extension();
            $filename = Str::random(4) . time() . '.' . $extension;
            $course->image = $request->file('course_image')->move(('uploads/images/course'), $filename);
        }

        // Handle course video update
        if ($request->hasFile('course_video')) {
            if ($course->video) {
                File::delete(public_path($course->video)); // Delete old video if exists
            }
            $videoExtension = $request->file('course_video')->extension();
            $videoFilename = Str::random(4) . time() . '.' . $videoExtension;
            $course->video = $request->file('course_video')->move(('uploads/videos/course'), $videoFilename);
        }

        // Save updated course details
        if ($course->save()) {
            return $this->sendResponse(['course' => $course], 'Course updated successfully');
        } else {
            return $this->sendResponse([], 'Failed to update course.');
        }
    }




    public function deleteCourseDetails(Request $request, $courseId)
    {
        $validator = Validator::make(array_merge($request->all(), ['courseId' => $courseId]), [
            'courseId' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $course = Course::find($courseId);
            $course->delete();
        }

        return $this->sendResponse([], 'Course deleted successfully');
    }

    public function getCourseResults(Request $request, $courseId)
    {
        $resultService = new ResultService();

        $results = $resultService->getCourseResults($courseId);

        return $this->sendResponse(['results' => $results], '');
    }

    // public function getSuperSubjects()
    // {
    //     $superSubjects = Subject::where('subject_type', 2)->get(['id', 'name']);
    //     if($superSubjects) {
    //         return $this->sendResponse(['superSubjects' => $superSubjects], '');
    //     } else {
    //         return $this->sendError('Failed to fetch super subjects.');
    //     }
    // }

    public function getCoursePreview($courseId)
    {
        $validator = Validator::make(['courseId' => $courseId], [
            'courseId' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $course = Course::join('subjects', 'courses.subject_id', '=', 'subjects.id')
            ->select('courses.*', 'subjects.name as subject_name')
            ->where('courses.id', $courseId)->first();
            $chapters = DB::table('chapters as c')
            ->select('c.id', 'c.title', 'c.image', 'c.lock_status')
            ->where('c.course_id', $courseId)
            ->get();
            // List of Videos from the Course for each Chapter
            foreach ($chapters as $chapter) {
                $chapter->videos = DB::table('videos as v')
                    ->select('v.*')
                    ->leftJoin('chapters as c', 'c.id', 'v.chapter_id')
                    ->where('v.course_id', $courseId)
                    ->where('v.chapter_id', $chapter->id)
                    ->orderBy('v.id')
                    ->get();
            }

            $trainer = Trainer::where('auth_id', $course->trainer_id)->first();

            return $this->sendResponse(['course' => $course,'chapters' => $chapters,'trainer' => $trainer]);
        }
    }

    // public function generateCertificate(){
    //     $userId = $this->getLoggedUserId();

    //     // Unique date and time for file naming
    //     $unqdate = date("Ymd");
    //     $unqtime = time();
    //     $courseId = 1;
    //     $new_file_name = $userId . "-" . $courseId . "-" . $unqdate . "" . $unqtime . ".jpg";

    //     // Prepare the text content
    //     // $name = $student->name;
    //     $name = 'Ashutosh';

    //     $today = date("Y-m-d");
    //     $formattedDate = date("d-m-y", strtotime($today));

    //     $data_and_place = $formattedDate;
    //     $courseName = "asdf fas";

    //     // Load the base image
    //     $file_name = 'pass/certificate.jpg';
    //     $img_source = imagecreatefromjpeg($file_name);

    //     // Font and color settings
    //     $font = 'fonts/ARIBL0.ttf';
    //     $text_color = imagecolorallocate($img_source, 0, 0, 0);

    //     // Place the student name onto the image
    //     // Calculate the width of the text
    //     $nameBoundingBox = imagettfbbox(30, 0, $font, $name);
    //     $nameWidth = $nameBoundingBox[4] - $nameBoundingBox[0];
    //     // Adjust the x-coordinate to center horizontally
    //     $nameX = (2000 - $nameWidth) / 2;
    //     // Place the text
    //     imagettftext($img_source,42, 0, $nameX, 635, $text_color, $font, $name);

    //     // Place the course name onto the image
    //     // Calculate the width of the text
    //     $courseNameBoundingBox = imagettfbbox(30, 0, $font, $courseName);
    //     $courseNameWidth = $courseNameBoundingBox[4] - $courseNameBoundingBox[0];
    //     // Adjust the x-coordinate to center horizontally
    //     $courseNameX = (2000 - $courseNameWidth) / 2;
    //     // Place the text
    //     imagettftext($img_source, 30, 0, $courseNameX, 920, $text_color, $font, $courseName);
    //     // Place the date and place onto the image
    //     imagettftext($img_source, 20, 0, 1295, 1067, $text_color, $font, $data_and_place);

    //     // Save the new image
    //     ImageJpeg($img_source, 'uploads/pass/' . $new_file_name);
    //     // imagedestroy($img_source); // Free up memory

    //     $filePath = 'uploads/pass/' . $new_file_name;

    //     return $this->sendResponse(['filePath'=>$filePath], 'Certificate created successfully.');

    // }
    public function generateCertificate($courseId){
        $userId = $this->getLoggedUserId();

        // Check if certificate already exists in the certificates table
        $certificate = DB::table('course_certificates')
            ->where('student_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($certificate) {
            // Return existing certificate URL
            return $this->sendResponse(['filePath' => $certificate->certificate_url], 'Certificate already exists.');
        }
        $student = Student::where('auth_id',$userId)->first();
        $course = Course::find($courseId);

        // Proceed to generate certificate if it doesn't exist
        // Unique date and time for file naming
        $unqdate = date("Ymd");
        $unqtime = time();
        $new_file_name = $userId . "-" . $courseId . "-" . $unqdate . "" . $unqtime . ".jpg";

        // Prepare the text content
        $name = $student->name;
        $today = date("Y-m-d");
        $formattedDate = date("d-m-y", strtotime($today));
        $data_and_place = $formattedDate;
        $courseName = $course->name;

        // Load and modify the certificate image
        $file_name = 'pass/certificate.jpg';
        $img_source = imagecreatefromjpeg($file_name);
        $font = 'fonts/ARIBL0.ttf';
        $text_color = imagecolorallocate($img_source, 0, 0, 0);
        $nameBoundingBox = imagettfbbox(30, 0, $font, $name);
        $nameWidth = $nameBoundingBox[4] - $nameBoundingBox[0];
        $nameX = (2000 - $nameWidth) / 2;

        $courseNameBoundingBox = imagettfbbox(30, 0, $font, $courseName);
        $courseNameWidth = $courseNameBoundingBox[4] - $courseNameBoundingBox[0];
        $courseNameX = (2000 - $courseNameWidth) / 2;

        imagettftext($img_source, 42, 0, $nameX, 635, $text_color, $font, $name);
        imagettftext($img_source, 30, 0, (2000 - $courseNameWidth) / 2, 920, $text_color, $font, $courseName);
        imagettftext($img_source, 20, 0, 1295, 1067, $text_color, $font, $data_and_place);

        // Save the new image
        $filePath = 'uploads/certificates/courses/' . $new_file_name;
        ImageJpeg($img_source, $filePath);

        // Store certificate in the database
        DB::table('course_certificates')->insert([
            'student_id' => $userId,
            'course_id' => $courseId,
            'certificate_url' => $filePath,
            'generated_at' => now(),
        ]);

        return $this->sendResponse(['filePath' => $filePath], 'Certificate created successfully.');
    }

}
