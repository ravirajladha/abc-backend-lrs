<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Api\BaseController;
use App\Models\AssessmentQuestion;

class AssessmentQuestionController extends BaseController
{
    /**
     * Display a assessment question.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssessmentQuestionById($assessmentQuestionId)
    {
        $assessmentQuestion = DB::table('assessment_questions as q')
            ->where('q.id', $assessmentQuestionId)
            ->first();

        return $this->sendResponse(['assessment_question' => $assessmentQuestion]);
    }


    /**
     * Display a listing of the assessment questions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAssessmentQuestions(Request $request)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            $assessmentQuestions = DB::table('assessment_questions as q')
                ->select('q.*', 's.name as subject', 'c.name as course')
                ->leftJoin('subjects as s', 's.id', '=', 'q.class_id')
                ->leftJoin('courses as c', 'c.id', '=', 'q.course_id');

            if ($request->classId !== null && $request->classId !== 'undefined') {
                $assessmentQuestions->where('q.class_id', $request->classId);
            }

            if ($request->subjectId !== null && $request->subjectId !== 'undefined') {
                $assessmentQuestions->where('q.subject_id', $request->subjectId);
            }
            $res = $assessmentQuestions->get();
            return $this->sendResponse(['assessment_questions' => $res]);
        } else {
            return $this->sendAuthError("Not authorized to fetch assessment questions list.");
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAssessmentQuestionDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'option_one' => 'required|string',
            'option_two' => 'required|string',
            'option_three' => 'required|string',
            'option_four' => 'required|string',
            'answer_key' => 'required|in:option_one,option_two,option_three,option_four',
            'selectedClass' => 'required|exists:classes,id',
            'selectedSubject' => 'required|exists:subjects,id',
        ], [
            'answer_key' => 'Please select a valid option as the answer key.',
        ]);


        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $assessmentQuestion = new AssessmentQuestion;
            $assessmentQuestion->text = $request->question;
            $assessmentQuestion->code = $request->code;
            $assessmentQuestion->option_one = $request->option_one;
            $assessmentQuestion->option_two = $request->option_two;
            $assessmentQuestion->option_three = $request->option_three;
            $assessmentQuestion->option_four = $request->option_four;
            $assessmentQuestion->answer_key = $request->answer_key;
            $assessmentQuestion->class_id = $request->selectedClass;
            $assessmentQuestion->subject_id = $request->selectedSubject;
            $assessmentQuestion->save();
        }

        return $this->sendResponse([], 'Assessment added successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $assessmentQuestionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAssessmentQuestionDetails(Request $request, $assessmentQuestionId)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'option_one' => 'required|string',
            'option_two' => 'required|string',
            'option_three' => 'required|string',
            'option_four' => 'required|string',
            'answer_key' => 'required|in:option_one,option_two,option_three,option_four',
            'selectedClass' => 'required|exists:classes,id',
            'selectedSubject' => 'required|exists:subjects,id',
        ], [
            'answer_key' => 'Please select a valid option as the answer key.',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $assessmentQuestion = AssessmentQuestion::find($assessmentQuestionId);

            if (!$assessmentQuestion) {
                return $this->sendResponse([], 'Assessment question not found');
            }

            $assessmentQuestion->text = $request->question;
            $assessmentQuestion->code = $request->code;
            $assessmentQuestion->option_one = $request->option_one;
            $assessmentQuestion->option_two = $request->option_two;
            $assessmentQuestion->option_three = $request->option_three;
            $assessmentQuestion->option_four = $request->option_four;
            $assessmentQuestion->answer_key = $request->answer_key;
            $assessmentQuestion->class_id = $request->selectedClass;
            $assessmentQuestion->subject_id = $request->selectedSubject;
            $assessmentQuestion->save();
        }

        return $this->sendResponse([], 'Assessment question updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $assessmentQuestionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAssessmentQuestionDetails($assessmentQuestionId)
    {
        $assessmentQuestion = AssessmentQuestion::find($assessmentQuestionId);

        $assessment = DB::table('assessments as a')
            ->select('a.id', 'a.question_ids')
            ->whereIn('a.question_ids', [$assessmentQuestionId])
            ->first();

        if ($assessment) {
            return $this->sendError('Assessment question cannot be deleted, since it is used.');
        }

        if (!$assessmentQuestion) {
            return $this->sendResponse([], 'Assessment question not found');
        }

        $assessmentQuestion->delete();

        return $this->sendResponse([], 'Assessment question deleted successfully');
    }
}
