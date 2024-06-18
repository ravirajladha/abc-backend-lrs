<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\Subject;
use App\Models\TermTest;
use Illuminate\Support\Str;

use Illuminate\Http\Request;

use App\Models\TermTestResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

use App\Services\Admin\ResultService;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Api\BaseController;
use App\Services\Student\ResultService as StudentResultService;

use App\Http\Constants\SubjectTypeConstants;

class CourseController extends BaseController
{
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
            $courses = DB::table('courses as c')
                ->select('c.id', 'c.name', 'c.image')
                ->where('c.subject_id', $subjectId)
                ->get();

            if ($subject) {
                $res = [
                    'subject_id' => $subjectId,
                    'subject' => $subject->name,
                    'courses' => $courses,
                ];
            } else {
                return $this->sendError('Subject not found!');
            }
        }

        return $this->sendResponse($res);
    }
    public function storeCourseDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_name' => 'required',
            'course_name' => 'required|max:75|unique:courses,name',
            'course_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $course = new Course();
            $course->name = $request->course_name;
            $course->subject_id = $request->subject_id;

            if (!empty($request->file('course_image'))) {
                $extension = $request->file('course_image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $course->image = $request->file('course_image')->move(('uploads/images/course'), $filename);
            } else {
                $course->image = null;
            }
            if ($course->save()) {
                return $this->sendResponse([], 'course created successfully.');
            } else {
                return $this->sendResponse([], 'Failed to create course.');
            }
        }
    }





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

    /**
     * Display the student report card.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentReportCard(Request $request)
    {
        $studentId = $request->studentId; //StudentId from students table
        $classId = $request->classId;
        $sectionId = $request->sectionId;
        $report_card = [];

        if (!($studentId && $classId && $sectionId)) {
            return $this->sendError('Failed to fetch student results.');
        }

        $resultsService = new StudentResultService();

        $report_card['subject_results'] = $resultsService->getSubjectResults($studentId, $classId);
        $report_card['total_marks'] = $resultsService->getTotalMarks($studentId);
        $report_card['base_total_marks'] = $resultsService->getTermTestTotalMarks($classId);
        $report_card['class_rank'] = $resultsService->getClassRank($studentId, $classId);
        $report_card['section_rank'] = $resultsService->getSectionRank($studentId, $sectionId);
        $report_card['assessment_results'] = $resultsService->getAverageAssessmentScore($studentId);

        return $this->sendResponse(['report_card' => $report_card], 'Report card fetched successfully.');
    }



    /**
     * Display a listing of the subjects with results for students by classId.
     *
     * @param $classId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentSubjectsWithResults(Request $request)
    {
        $res = [];

        $classId = $request->classId;

        $studentId = $request->studentId;

        $validator = Validator::make($request->all(), [
            'classId' => 'required',
            'studentId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $subjects = DB::table('subjects as s')
                ->select('s.id', 's.name', 's.image', 's.subject_type', 's.super_subject_id')
                ->where('s.class_id', $classId)
                ->whereIn('s.subject_type', [SubjectTypeConstants::TYPE_DEFAULT_SUBJECT,SubjectTypeConstants::TYPE_SUB_SUBJECT])
                ->get();

            foreach ($subjects as $subject) {

                if ($subject->subject_type == 3 && $subject->super_subject_id) {
                    $superSubject = Subject::select('name')->find($subject->super_subject_id);
                    $subject->super_subject_name = $superSubject ? $superSubject->name : null;
                } else {
                    $subject->super_subject_name = null;
                }

                $latestTest = DB::table('term_tests')
                    ->where('subject_id', $subject->id)
                    ->select('id', 'term_type', 'description')
                    ->orderBy('created_at', 'desc')
                    ->first();
                // Log::info('Subjects data:', ['subjects' => $latestTest]);
                if ($latestTest) {
                    $latestTestId = $latestTest->id;
                    $testDescription = $latestTest->description;
                    $latestTerm = $latestTest->term_type;

                    $latestTestResults = DB::table('term_test_results as results')
                        ->where('results.student_id', $studentId)
                        ->where('results.test_id', $latestTestId)
                        ->exists();

                    if (!$latestTestResults) {
                        $subject->latest_test_id = $latestTestId;
                        $subject->latest_term = $latestTerm;
                        $subject->testDescription = $testDescription;
                    } else {
                        $subject->latest_test_id = false;
                        $subject->latest_term = false;
                        $subject->testDescription = false;
                    }
                }

                $studentResult = [];

                $studentResult = DB::table('term_test_results as results')
                    ->select('results.*', 'test.term_type')
                    ->leftJoin('term_tests as test', 'test.id', 'results.test_id')
                    ->where('results.student_id', $studentId)
                    ->where('test.subject_id', $subject->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $subject->results = $studentResult;
            }

            if ($subjects) {
                return $this->sendResponse(['subjects' => $subjects]);
            } else {
                return $this->sendError('Subject not found!');
            }
        }
    }



    public function getSubjectDetails($subjectId)
    {
        $validator = Validator::make(['subjectId' => $subjectId], [
            'subjectId' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $subject = Subject::where('id', $subjectId)->first();
            return $this->sendResponse(['subject' => $subject]);
        }
    }
    public function updateSubjectDetails(Request $request, $subjectId)
    {
        $validator = Validator::make($request->all(), [
            'subject_name' => 'required|max:75|unique:subjects,name',

            'subject_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $subject = Subject::find($subjectId);

        if (!$request->has('subject_name')) {
            return $this->sendError('Subject name is required.');
        }

        $subject->name = $request->subject_name;

        if ($request->hasFile('subject_image')) {
            $formData = $request->all();
            // $formData['image'] = $request->file('subject_image');

            if ($request->hasFile('subject_image')) {
                if ($subject->logo) {
                    File::delete(public_path($subject->logo));
                }
                $extension = $request->file('subject_image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $subject->image = $request->file('subject_image')->move(('uploads/images/subject'), $filename);
            }

            // $image = $formData['image'];
            // $imageName = time() . '.' . $image->getClientOriginalExtension();
            // $image->move(public_path('uploads/images/subject'), $imageName);

            // $subject->image = 'uploads/images/subject/' . $imageName;
        }

        $subject->save();

        return $this->sendResponse(['subject' => $subject], 'Subject updated successfully');
    }




    public function deleteSubjectDetails(Request $request, $subjectId)
    {
        $validator = Validator::make(array_merge($request->all(), ['subjectId' => $subjectId]), [
            'subjectId' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $subject = Subject::find($subjectId);
            $subject->delete();
        }

        return $this->sendResponse([], 'Subject deleted successfully');
    }

    public function getSubjectResults(Request $request, $subjectId)
    {
        $resultService = new ResultService();

        $results = $resultService->getSubjectResults($subjectId, $request->term);

        return $this->sendResponse(['results' => $results], '');
    }

    public function getSuperSubjects()
    {
        $superSubjects = Subject::where('subject_type', 2)->get(['id', 'name']);
        if($superSubjects) {
            return $this->sendResponse(['superSubjects' => $superSubjects], '');
        } else {
            return $this->sendError('Failed to fetch super subjects.');
        }
    }

}