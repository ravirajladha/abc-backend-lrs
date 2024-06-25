<?php

namespace App\Http\Controllers\Api;

use App\Models\Classes;
use App\Models\Subject;
use App\Models\TermTest;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\TermTestResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

use App\Services\Admin\ResultService;
// use Illuminate\Validation\Rule as Enter;

// use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Http\Constants\SubjectTypeConstants;
use App\Http\Controllers\Api\BaseController;
use App\Services\Student\ResultService as StudentResultService;

class SubjectController extends BaseController
{
    /**
     * Display a listing of the subjects.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubjectsList()
    {
        $subjects = Subject::get();
        return $this->sendResponse(['subjects' => $subjects]);
    }

    public function getMyCourses()
    {
        $subjects = Subject::join('classes', 'subjects.class_id', '=', 'classes.id')
            ->join('chapters', 'subjects.id', '=', 'chapters.subject_id')
            ->join('chapter_logs', 'chapters.id', '=', 'chapter_logs.chapter_id')
            ->select('subjects.id', 'subjects.name','subjects.image', 'classes.name as class_name')
            ->where('chapter_logs.video_complete_status', 1)
            ->where('chapter_logs.student_id', $this->getLoggedUserId())
            ->groupBy('subjects.id', 'subjects.name','subjects.image', 'classes.name')
            ->get();
        return $this->sendResponse(['subjects' => $subjects]);
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
     * Display a listing of the subjects with ClassId.
     *
     * @param $classId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubjectListByClassId($classId)
    {
        $res = [];

        $validator = Validator::make(['classId' => $classId], [
            'classId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $class = Classes::where('id', $classId)->first();
            $subjects = DB::table('subjects as s')
                ->select('s.id', 's.name', 's.image', 'subject_type', 'super_subject_id')
                ->where('s.class_id', $classId)
                ->whereIn('s.subject_type', [SubjectTypeConstants::TYPE_DEFAULT_SUBJECT,SubjectTypeConstants::TYPE_SUB_SUBJECT])
                ->get();

            // Fetch super subject name
            foreach ($subjects as $subject) {
                if ($subject->subject_type == 3 && $subject->super_subject_id) {
                    $superSubject = Subject::select('name')->find($subject->super_subject_id);
                    $subject->super_subject_name = $superSubject ? $superSubject->name : null;
                } else {
                    $subject->super_subject_name = null;
                }
            }
            if ($class) {
                $res = [
                    'class_id' => $classId,
                    'class' => $class->name,
                    'subjects' => $subjects,
                ];
            } else {
                return $this->sendError('Subject not found!');
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
                ->select('s.id', 's.name', 's.image','s.subject_type', 's.super_subject_id')
                // ->where('s.class_id', $classId)
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

    public function storeSubjectDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required',
            'subject_name' => 'required|max:75|unique:subjects,name',
            'subject_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'subject_type' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $subject = new Subject();
            $subject->name = $request->subject_name;
            $subject->class_id = $request->class_id;
            $subject->subject_type = $request->subject_type;
            if ($request->subject_type == 3) {
                $subject->super_subject_id = $request->super_subject_id;
            }
            if (!empty($request->file('subject_image'))) {
                $extension = $request->file('subject_image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension;
                $subject->image = $request->file('subject_image')->move(('uploads/images/subject'), $filename);
            } else {
                $subject->image = null;
            }
            if ($subject->save()) {
                return $this->sendResponse([], 'Subject created successfully.');
            } else {
                return $this->sendResponse([], 'Failed to create subject.');
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
        $subject = Subject::find($subjectId);

        if (!$subject) {
            return $this->sendError('Subject not found.');
        }
        $validator = Validator::make($request->all(), [
            'subject_name' => [
                'required',
                'max:75',
                Rule::unique('subjects', 'name')->ignore($subjectId),
            ],
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
