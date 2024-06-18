<?php

namespace App\Http\Controllers\Api;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentResult;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Video;

class AssessmentController extends BaseController
{
    /**
     * Display a listing of the assessements.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAssessments(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            $assessment = DB::table('assessments as a')
                ->select('a.*', 'c.name as class', 's.name as subject')
                ->leftJoin('classes as c', 'c.id', '=', 'a.class_id')
                ->leftJoin('subjects as s', 's.id', '=', 'a.subject_id');

            if ($request->classId !== null && $request->classId !== 'undefined') {
                $assessment->where('a.class_id', $request->classId);
            }

            if ($request->subjectId !== null && $request->subjectId !== 'undefined') {
                $assessment->where('a.subject_id', $request->subjectId);
            }

            $res = $assessment->get();



            return $this->sendResponse(['assessments' => $res]);
        } else {
            return $this->sendAuthError("Not authorized fetch assessments list.");
        }
    }

    public function showAssessmentsResults(Request $request, $assessmentId)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            $results = DB::table('students as s')
                ->select(
                    's.name as student_name',
                    's.section_id',
                    DB::raw('COUNT(r.id) as result_count'),
                    DB::raw('AVG(r.score) as average_score'),
                    DB::raw('AVG(r.percentage) as average_percentage')
                )
                ->leftJoin('assessment_results as r', 'r.student_id', 's.id')
                ->where('r.assessment_id', $assessmentId)
                ->groupBy('s.id', 's.name', 's.section_id')
                ->orderBy('s.name', 'asc')
                ->get();

            return $this->sendResponse(['results' => $results], '');
        } else {
            return $this->sendAuthError("Not authorized fetch assessment results.");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAssessmentDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assessmentName' => 'required|string|max:255',
            'noOfQuestions' => 'required|integer',
            'selectedQuestions' => 'required',
            'selectedClass' => 'required|exists:classes,id',
            'selectedSubject' => 'required|exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $assessment = new Assessment();
            $assessment->title = $request->assessmentName;
            $assessment->no_of_questions = $request->noOfQuestions;
            $assessment->class_id = $request->selectedClass;
            $assessment->subject_id = $request->selectedSubject;
            $assessment->time_limit = $request->duration;
            $assessment->passing_percentage = $request->passingPercentage;
            $assessment->description = $request->description;
            $assessment->question_ids = implode(',', $request->selectedQuestions);
            $assessment->save();
        }

        return $this->sendResponse([], 'Assessment added successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $assessmentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAssessmentDetails(Request $request, $assessmentId)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['assessment_id' => $assessmentId]),
            [
                'assessment_id' => 'required',
                'assessmentName' => 'required|string|max:255',
                // 'noOfQuestions' => 'required|integer',
                'selectedQuestions' => 'required',
                // 'selectedClass' => 'required|exists:classes,id',
                // 'selectedSubject' => 'required|exists:subjects,id',
            ]
        );

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $assessment = Assessment::find($assessmentId);

            if (!$assessment) {
                return $this->sendError('Assessment not found');
            }

            $assessment->title =  $request->assessmentName;
            $assessment->no_of_questions = count($request->selectedQuestions);
            $assessment->class_id = $request->selectedClass;
            $assessment->subject_id = $request->selectedSubject;
            $assessment->time_limit = $request->duration;
            $assessment->passing_percentage = $request->passingPercentage;
            $assessment->description = $request->description;
            $assessment->question_ids = implode(',', $request->selectedQuestions);
            $assessment->save();
        }

        return $this->sendResponse([], 'Assessment updated successfully');
    }

    /**
     * Get individual assessment details for update
     *
     * @param  int  $assessmentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssessmentDetails($assessmentId)
    {
        $assessment = DB::table('assessments as a')
            ->select('a.id', 'a.*')
            ->where('a.id', $assessmentId)
            ->first();

        return $this->sendResponse(['assessment' => $assessment]);
    }

    /**
     * Get individual assessment details including questions and options.
     *
     * @param  int  $assessmentId
     * @return \Illuminate\Http\JsonResponse
     */ public function getAssessmentDetailsWithQuestions($assessmentId)
    {
        $assessment = DB::table('assessments as a')
            ->select('a.id', 'a.class_id', 'a.subject_id', 'a.title', 'a.description', 'a.total_score', 'a.time_limit', 'a.passing_percentage', 'a.no_of_questions', 'c.name as class', 's.name as subject', 'a.question_ids')
            ->leftJoin('classes as c', 'c.id', '=', 'a.class_id')
            ->leftJoin('subjects as s', 's.id', '=', 'a.subject_id')
            ->where('a.id', $assessmentId)
            ->first();

        if ($assessment && $assessment->question_ids) {
            $questionIds = explode(',', $assessment->question_ids);

            $assessment->questions = DB::table('assessment_questions')
                ->whereIn('id', $questionIds)
                ->get();
        }

        return $this->sendResponse(['assessment' => $assessment]);
    }


    /**
     * Store the assessment response into database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAssessmentResponse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'studentId' => 'required',
            'assessmentId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            //Get Test and Test Questions Data
            $assessment = Assessment::find($request->assessmentId);
            $assessment_questions = explode(',', $assessment->question_ids);
            $assessment_question_answers = AssessmentQuestion::whereIn('id', $assessment_questions)->get();

            //Process Student Answers
            $selectedQuestionIds = explode(',', $request->selectedQuestionIds);
            $selectedAnswers = explode(',', $request->selectedAnswers);

            $studentResponse = array_map(function ($q, $a) {
                return ['qns' => $q, 'ans' => $a];
            }, $selectedQuestionIds, $selectedAnswers);

            $score = 0;

            //Calculating the score
            foreach ($assessment_question_answers as $question) {
                foreach ($studentResponse as $response) {
                    if ($question->id == $response['qns']) {
                        if ($question->answer_key == $response['ans']) {
                            $score = $score + 1;
                        }
                    }
                }
            }


            //Calculating the percentage
            $totalScore = count($assessment_questions) * 1;

            if ($score !== 0) {
                $score_percentage = ($score / $totalScore) * 100;
            } else {
                $score_percentage = 0;
            }

            $assessment_result = new AssessmentResult();
            $assessment_result->video_id = $request->videoId;
            $assessment_result->assessment_id = $request->assessmentId;
            $assessment_result->student_id  = $request->studentId;
            $assessment_result->score = $score;
            $assessment_result->percentage = $score_percentage;
            if ($score_percentage >= $assessment->passing_percentage) {
                $assessment_result->is_passed = 1;
            } else {
                $assessment_result->is_passed = 0;
            }
            $selectedAnswersArray = is_array($request->selectedAnswers) ? $request->selectedAnswers : explode(',', $request->selectedAnswers);
            $assessment_result->response = implode(',', $selectedAnswersArray);
            $assessment_result->save();

            $studentAuthId = $this->getLoggedUserId();

            // check if all the assessment of one chapter is completed then update the status in chapter log
            $chapterId = DB::table('videos')->where('id', $request->videoId)->value('chapter_id');
            $videoIds = DB::table('videos')->where('chapter_id', $chapterId)->whereNotNull('assessment_id')->pluck('id');
            $allAssessmentCompleted = AssessmentResult::where('student_id', $request->studentId)
                ->whereIn('video_id', $videoIds)
                ->where('is_passed', 1)
                ->count() == count($videoIds);
            if ($allAssessmentCompleted) {
                // Update the chapter completion status to indicate that the chapter is complete
                DB::table('chapter_logs')
                    ->updateOrInsert(
                        ['student_id' => $studentAuthId, 'chapter_id' => $chapterId,],
                        ['assessment_complete_status' => 1, 'updated_at' => now(), 'created_at' => DB::raw('IFNULL(created_at, NOW())')]
                    );
            } else {
                // If no row exists for the chapter in chapter_logs table, create a new row with status 0
                DB::table('chapter_logs')
                    ->updateOrInsert(
                        ['student_id' => $studentAuthId, 'chapter_id' => $chapterId,],
                        ['assessment_complete_status' => 0, 'updated_at' => now(), 'created_at' => DB::raw('IFNULL(created_at, NOW())')]
                    );
            }

            return $this->sendResponse(['assessment_result' => $assessment_result], 'Assessment completed successfully');
        }
    }
    // return $assessment_result;
    // $assessment_result->response = $request->selectedAnswers;
    // $assessment_result->response_questions = implode(',', $request->selectedQuestionIds);
    // $assessment_result->response_answers = implode(',', $request->selectedAnswers);

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Assessment  $assessment
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAssessmentDetails(Request $request, $assessmentId)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['assessment_id' => $assessmentId]),
            [
                'assessment_id' => 'required',
            ]
        );
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $assessment = Assessment::find($assessmentId);
            $assessment->delete();
        }

        return $this->sendResponse([], 'Assessment deleted successfully');
    }



    /**
     * Get the assessment result video wise for a chapter for a student
     *
     */
    public function getAssessmentResultsWithVideo($chapterId, $studentId)
    {
        $studentId = DB::table('students')->where('auth_id', $studentId)->value('id');
        // List of Chapters from the Subject
        $videos = Video::where('chapter_id', $chapterId)
            ->select('id', 'title')
            ->get();

        // Include Assessment Results for each Video in the Chapter
        foreach ($videos as $video) {
            $video->assessment_results = DB::table('assessment_results as a')
                ->select('a.*')
                ->where('a.student_id', $studentId)
                ->where('a.video_id', $video->id)
                ->select('id', 'assessment_id', 'score', 'percentage', 'created_at')
                ->get();
        }
        return $this->sendResponse(['videos' => $videos]);
    }

    public function getAssessmentResults(Request $request)
    {
        $studentId = $request->studentId;
        $chapterId = $request->chapterId || null;
        $studentId = DB::table('students')->where('auth_id', $studentId)->value('id');

        $query = DB::table('videos as v')
        ->leftJoin('assessment_results as ar', function ($join) use ($studentId) {
            $join->on('v.id', '=', 'ar.video_id')
                 ->where('ar.student_id', '=', $studentId);
        })
        ->select('v.id', 'v.title', DB::raw('AVG(ar.score) as avg_score'))
        ->groupBy('v.id', 'v.title');

        // Conditionally add chapter_id condition
        if ($chapterId !== null) {
            $query->where('v.chapter_id', $chapterId);
        }

        $results = $query->get();
        return $this->sendResponse(['results' => $results]);
    }
    public function getAssessmentResultsByStudentId(Request $request)
    {
        $studentId = $request->studentId;
        $chapterId = $request->chapterId || null;
        $query = DB::table('videos as v')
        ->leftJoin('assessment_results as ar', function ($join) use ($studentId) {
            $join->on('v.id', '=', 'ar.video_id')
                 ->where('ar.student_id', '=', $studentId);
        })
        ->select('v.id', 'v.title', DB::raw('AVG(ar.score) as avg_score'))
        ->groupBy('v.id', 'v.title');

        // Conditionally add chapter_id condition
        if ($chapterId !== null) {
            $query->where('v.chapter_id', $chapterId);
        }

        $results = $query->get();
        return $this->sendResponse(['results' => $results]);
    }


}
