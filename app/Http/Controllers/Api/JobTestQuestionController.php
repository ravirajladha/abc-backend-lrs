<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\JobQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JobTestQuestionController extends BaseController
{
    public function getTermTestQuestionDetails($termTestQuestionId)
    {
        $termTestQuestion = JobQuestion::find($termTestQuestionId);

        if (!$termTestQuestion) {
            return $this->sendError('Term test question not found');
        }

        return $this->sendResponse(['term_test_question' => $termTestQuestion], '');
    }

    public function getAllTermTestQuestions(Request $request)
    {

        $termTestQuestions = DB::table('job_questions as q')
            ->select('q.*', 'c.name as class')
            ->leftJoin('classes as c', 'c.id', '=', 'q.class_id');

        if ($request->classId !== null && $request->classId !== 'undefined') {
            $termTestQuestions->where('q.class_id', $request->classId);
        }     

        $term_test_questions = $termTestQuestions->get();

        if (!$termTestQuestions) {
            return $this->sendResponse([], 'Term test questions not found');
        }

        return $this->sendResponse(['term_test_questions' => $term_test_questions], '');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'option_one' => 'required|string',
            'option_two' => 'required|string',
            'option_three' => 'required|string',
            'option_four' => 'required|string',
            'answer_key' => 'required|in:option_one,option_two,option_three,option_four',
            'selectedClass' => 'required|exists:classes,id',
            // 'selectedSubject' => 'required|exists:subjects,id',
        ], [
            'answer_key' => 'Please select a valid option as the answer key.',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $termTest = new JobQuestion();
        // $termTest->subject_id = $request->selectedSubject;
        $termTest->class_id = $request->selectedClass;
        $termTest->question = $request->question;
        $termTest->explanation = $request->explanation;
        $termTest->code = $request->code;
        $termTest->image = $request->image;
        $termTest->option_one = $request->option_one;
        $termTest->option_two = $request->option_two;
        $termTest->option_three = $request->option_three;
        $termTest->option_four = $request->option_four;
        $termTest->answer_key = $request->answer_key;
        $termTest->save();

        return $this->sendResponse(['termTest' => $termTest], 'Term test question created successfully');
    }

    public function update(Request $request, $termTestQuestionId)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'option_one' => 'required|string|max:255',
            'option_two' => 'required|string|max:255',
            'option_three' => 'required|string|max:255',
            'option_four' => 'required|string|max:255',
            'answer_key' => 'required|in:option_one,option_two,option_three,option_four',
            'selectedClass' => 'required|exists:classes,id',
        ], [
            'answer_key' => 'Please select a valid option as the answer key.',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $termTest = JobQuestion::find($termTestQuestionId);

        if (!$termTest) {
            return $this->sendError('Term test question not found');
        }

        $termTest->class_id = $request->selectedClass;
        $termTest->question = $request->question;
        $termTest->explanation = $request->explanation;
        $termTest->code = $request->code;
        $termTest->image = $request->image;
        $termTest->option_one = $request->option_one;
        $termTest->option_two = $request->option_two;
        $termTest->option_three = $request->option_three;
        $termTest->option_four = $request->option_four;
        $termTest->answer_key = $request->answer_key;
        $termTest->save();

        return $this->sendResponse($termTest, 'Job test question updated successfully');
    }

    public function delete($termTestQuestionId)
    {

        $termTest = DB::table('job_tests as t')
            ->select('t.id', 't.question_ids')
            ->whereIn('t.question_ids', [$termTestQuestionId])
            ->first();

        if ($termTest) {
            return $this->sendError('Term test question cannot be deleted, since it is used in a test.');
        }

        $termTestQuestion = JobQuestion::find($termTestQuestionId);

        if (!$termTestQuestion) {
            return $this->sendError('Term test question not found');
        }

        $termTestQuestion->delete();

        return $this->sendResponse([], 'Term test question deleted successfully');
    }
}
