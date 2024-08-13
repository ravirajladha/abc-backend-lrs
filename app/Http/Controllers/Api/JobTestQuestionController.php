<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\JobQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JobTestQuestionController extends BaseController
{
    public function getTestQuestionDetails($TestQuestionId)
    {
        $TestQuestion = JobQuestion::find($TestQuestionId);

        if (!$TestQuestion) {
            return $this->sendError('Test question not found');
        }

        return $this->sendResponse(['test_question' => $TestQuestion], '');
    }

    public function getAllTestQuestions(Request $request)
    {

        $testQuestions = DB::table('job_questions as q')
            ->select('q.*', 's.name as subject')
            ->leftJoin('subjects as s', 's.id', '=', 'q.subject_id');

        if ($request->subjectId !== null && $request->subjectId !== 'undefined') {
            $testQuestions->where('q.subject_id', $request->subjectId);
        }     

        $test_questions = $testQuestions->get();

        if (!$testQuestions) {
            return $this->sendResponse([], 'Test questions not found');
        }

        return $this->sendResponse(['test_questions' => $test_questions], '');
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
            'selectedSubject' => 'required|exists:subjects,id',
            // 'selectedSubject' => 'required|exists:subjects,id',
        ], [
            'answer_key' => 'Please select a valid option as the answer key.',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $test = new JobQuestion();
        // $test->subject_id = $request->selectedSubject;
        $test->subject_id = $request->selectedSubject;
        $test->question = $request->question;
        $test->explanation = $request->explanation;
        $test->code = $request->code;
        $test->image = $request->image;
        $test->option_one = $request->option_one;
        $test->option_two = $request->option_two;
        $test->option_three = $request->option_three;
        $test->option_four = $request->option_four;
        $test->answer_key = $request->answer_key;
        $test->save();

        return $this->sendResponse(['test' => $test], 'Test question created successfully');
    }

    public function update(Request $request, $testQuestionId)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'option_one' => 'required|string|max:255',
            'option_two' => 'required|string|max:255',
            'option_three' => 'required|string|max:255',
            'option_four' => 'required|string|max:255',
            'answer_key' => 'required|in:option_one,option_two,option_three,option_four',
            'selectedSubject' => 'required|exists:subjects,id',
        ], [
            'answer_key' => 'Please select a valid option as the answer key.',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $test = JobQuestion::find($testQuestionId);

        if (!$test) {
            return $this->sendError('Term test question not found');
        }

        $test->subject_id = $request->selectedSubject;
        $test->question = $request->question;
        $test->explanation = $request->explanation;
        $test->code = $request->code;
        $test->image = $request->image;
        $test->option_one = $request->option_one;
        $test->option_two = $request->option_two;
        $test->option_three = $request->option_three;
        $test->option_four = $request->option_four;
        $test->answer_key = $request->answer_key;
        $test->save();

        return $this->sendResponse($test, 'Job test question updated successfully');
    }

    public function delete($testQuestionId)
    {

        $test = DB::table('job_tests as t')
            ->select('t.id', 't.question_ids')
            ->whereIn('t.question_ids', [$testQuestionId])
            ->first();

        if ($test) {
            return $this->sendError('Test question cannot be deleted, since it is used in a test.');
        }

        $testQuestion = JobQuestion::find($testQuestionId);

        if (!$testQuestion) {
            return $this->sendError('Test question not found');
        }

        $testQuestion->delete();

        return $this->sendResponse([], 'Test question deleted successfully');
    }
}
