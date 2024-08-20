<?php

namespace App\Http\Controllers\Api;

use App\Models\Test;
use App\Models\TestQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TestQuestionController extends BaseController
{
    public function getTestQuestionDetails($testQuestionId)
    {
        $testQuestion = TestQuestion::find($testQuestionId);

        if (!$testQuestion) {
            return $this->sendError('Test question not found');
        }

        return $this->sendResponse(['test_question' => $testQuestion], '');
    }



    public function getAllTestQuestions(Request $request)
    {
        // Start building the query
        $testQuestions = DB::table('test_questions as q')
            ->select('q.*', 's.name as subject', 'cou.name as course')
            ->leftJoin('subjects as s', 's.id', '=', 'q.subject_id')
            ->leftJoin('courses as cou', 'cou.id', '=', 'q.course_id');
    
        // Apply filters if available
        if ($request->subjectId !== null && $request->subjectId !== 'undefined') {
            $testQuestions->where('q.subject_id', $request->subjectId);
        }
    
        if ($request->courseId !== null && $request->courseId !== 'undefined') {
            $testQuestions->where('q.course_id', $request->courseId);
        }
    
        // Log the SQL query before execution
        Log::info('SQL Query:', ['query' => $testQuestions->toSql(), 'bindings' => $testQuestions->getBindings()]);
    
        // Execute the query
        $test_questions = $testQuestions->get();
    
        // Log the result
        Log::info('Query Result:', ['test_questions' => $test_questions]);
    
        // Check if data was retrieved
        if ($test_questions->isEmpty()) {
            Log::warning('No test questions found');
            return $this->sendResponse([], 'Test questions not found');
        }
    
        // Return the response with data
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
            'selectedCourse' => 'required|exists:courses,id',
        ], [
            'answer_key' => 'Please select a valid option as the answer key.',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $test = new TestQuestion();
        $test->course_id = $request->selectedCourse;
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

        return $this->sendResponse($test, 'Test question created successfully');
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
            'selectedCourse' => 'required|exists:courses,id',
        ], [
            'answer_key' => 'Please select a valid option as the answer key.',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $test = TestQuestion::find($testQuestionId);

        if (!$test) {
            return $this->sendError('Test question not found');
        }

        $test->course_id = $request->selectedCourse;
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

        return $this->sendResponse($test, 'Test question updated successfully');
    }

    public function delete($testQuestionId)
    {

        $test = DB::table('tests as t')
            ->select('t.id', 't.question_ids')
            ->whereIn('t.question_ids', [$testQuestionId])
            ->first();

        if ($test) {
            return $this->sendError('Test question cannot be deleted, since it is used in a test.');
        }

        $testQuestion = TestQuestion::find($testQuestionId);

        if (!$testQuestion) {
            return $this->sendError('Test question not found');
        }

        $testQuestion->delete();

        return $this->sendResponse([], 'Test question deleted successfully');
    }
}
