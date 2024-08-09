<?php

namespace App\Http\Controllers\Api;
use App\Models\Course;
use App\Models\Subject;
use App\Models\ChapterLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Services\Admin\ResultService;
use Illuminate\Support\Facades\Validator;
use App\Http\Constants\SubjectTypeConstants;
use App\Http\Controllers\Api\BaseController;
use App\Services\Student\ResultService as StudentResultService;
use App\Models\Student;
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
        $studentId = Student::where('auth_id',$this->getLoggedUserId())->value('id');

        $courses = Course::join('subjects', 'courses.subject_id', '=', 'subjects.id')
            ->join('chapters', 'courses.id', '=', 'chapters.subject_id')
            ->join('chapter_logs', 'chapters.id', '=', 'chapter_logs.chapter_id')
            ->select('courses.id', 'courses.name', 'courses.image', 'subjects.name as subject_name')
            ->where('chapter_logs.video_complete_status', 1)
            ->where('chapter_logs.student_id', $this->getLoggedUserId())
            ->groupBy('courses.id', 'courses.name', 'courses.image', 'subjects.name')
            ->get();
            foreach ($courses as $course) {

                // if ($course->subject_type == 3 && $course->super_subject_id) {
                //     $superSubject = Subject::select('name')->find($course->super_subject_id);
                //     $course->super_subject_name = $superSubject ? $superSubject->name : null;
                // } else {
                //     $course->super_subject_name = null;
                // }


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

                $course->completePercentage = ($completedChaptersCount/count($chapterIds)) * 100;

                $latestTest = DB::table('term_tests')
                    ->where('course_id', $course->id)
                    ->where('status', 1)
                    ->select('id', 'term_type', 'description')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($latestTest) {
                    $latestTestId = $latestTest->id;
                    $testDescription = $latestTest->description;
                    $latestTerm = $latestTest->term_type;

                    $latestTestResults = DB::table('term_test_results as results')
                        ->where('results.student_id', $studentId)
                        ->where('results.test_id', $latestTestId)
                        ->exists();

                    if (!$latestTestResults) {
                        $course->latest_test_id = $latestTestId;
                        $course->latest_term = $latestTerm;
                        $course->testDescription = $testDescription;
                    } else {
                        $course->latest_test_id = false;
                        $course->latest_term = false;
                        $course->testDescription = false;
                    }
                }

                $studentResult = [];

                $studentResult = DB::table('term_test_results as results')
                    ->select('results.*', 'test.term_type')
                    ->leftJoin('term_tests as test', 'test.id', 'results.test_id')
                    ->where('results.student_id', $studentId)
                    ->where('test.course_id', $course->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $course->results = $studentResult;

                // Trainer by course
                $teacher = DB::table('trainer_courses as ts')
                ->where('ts.course_id', $course->id)
                ->leftJoin('teachers as t', 't.id', 'ts.teacher_id')
                ->first();
                $course->teacher_name = $teacher->name;

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
        $studentId = $request->studentId; //StudentId from students table
        $subjectId = $request->subjectId;
        // $sectionId = $request->sectionId;
        $report_card = [];

        if (!($studentId && $subjectId )) {
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
        $res = [];

        $validator = Validator::make(['subjectId' => $subjectId], [
            'subjectId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $subject = Subject::where('id', $subjectId)->first();
            $courses = DB::table('courses as cou')
                ->select('cou.id', 'cou.name', 'cou.image', 'course_type')
                ->where('cou.subject_id', $subjectId)
                // ->whereIn('cou.course_type', [SubjectTypeConstants::TYPE_DEFAULT_SUBJECT,SubjectTypeConstants::TYPE_SUB_SUBJECT])
                ->get();

            // Fetch super subject name
            // foreach ($courses as $course) {
            //     if ($course->course_type == 3 ) {
            //         $superSubject = Course::select('name')->find($course->super_subject_id);
            //         $course->super_subject_name = $superSubject ? $superSubject->name : null;
            //     } else {
            //         $subject->super_subject_name = null;
            //     }
            // }
            if ($subject) {
                $res = [
                    'subject_id' => $subjectId,
                    'subject' => $subject->name,
                    'courses' => $courses,
                ];
            } else {
                return $this->sendError('Course not found!');
            }
        }

        return $this->sendResponse($res);
    }

    /**
     * Display a listing of the subjects with results for students by classId.
     *
     * @param $classId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentCoursesWithResults(Request $request)
    {
        $res = [];

        $subjectId = $request->subjectId;

        $studentId = $request->studentId;

        $validator = Validator::make($request->all(), [
            'subjectId' => 'required',
            'studentId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $courses = DB::table('courses as cou')
                ->join('subjects', 'cou.subject_id', '=', 'subjects.id')
                ->leftJoin('trainer_courses as tc', 'cou.id', '=', 'tc.subject_id')
                ->leftJoin('trainers as t', 'tc.trainer_id', '=', 't.id')
                ->select('cou.id', 'cou.name', 'cou.image', 'subjects.name as subject_name', 't.name as trainer_name')
                ->get();
            if ($courses) {
                return $this->sendResponse(['courses' => $courses]);
            } else {
                return $this->sendError('Course not found!');
            }
        }
    }

    public function storeCourseDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required',
            'course_name' => 'required|max:75|unique:courses,name',
            'course_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'course_type' => 'required',
            'benefits' => 'required|string',
            'description' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $course = new Course();
            $course->name = $request->course_name;
            $course->subject_id = $request->subject_id;
            $course->course_type = $request->course_type;
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
            $course->benefits = $request->benefits;
            $course->description = $request->description;

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
        ]);


        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $course = Course::find($courseId);

        if (!$request->has('course_name')) {
            return $this->sendError('Course name is required.');
        }

        $course->name = $request->course_name;

        if ($request->hasFile('course_image')) {
            $formData = $request->all();

            if ($request->hasFile('course_image')) {
                if ($course->logo) {
                    File::delete(public_path($course->logo));
                }
                $extension = $request->file('course_image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $course->image = $request->file('course_image')->move(('uploads/images/course'), $filename);
            }


        }

        $course->save();

        return $this->sendResponse(['course' => $course], 'Course updated successfully');
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

    // public function getCourseResults(Request $request, $courseId)
    // {
    //     $resultService = new ResultService();

    //     $results = $resultService->getCourseResults($courseId, $request->term);

    //     return $this->sendResponse(['results' => $results], '');
    // }

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
            ->select('courses.*','subjects.name as subject_name')
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

            $trainer = DB::table('trainer_courses as tc')
            ->where('ts.course_id', $courseId)
            ->leftJoin('trainers as t', 't.id', 'tc.trainer_id')
            ->first();

            return $this->sendResponse(['course' => $course,'chapters' => $chapters,'trainer'=> $trainer]);
        }
    }
}
