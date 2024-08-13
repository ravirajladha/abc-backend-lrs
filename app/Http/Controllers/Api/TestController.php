<?php

namespace App\Http\Controllers\Api;

use App\Models\Test;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Models\TestResult;
use App\Models\TestQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TestController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllTests(Request $request)
    {
        $userType = $request->attributes->get('type');
        // if ($userType === 'admin') {
        $tests = DB::table('tests as t')
            ->select('t.*', 's.name as subject', 'cou.name as course')
            ->leftJoin('subject as s', 's.id', '=', 't.subject_id')
            ->leftJoin('courses as cou', 'cou.id', '=', 't.course_id');

        if ($request->subjectId !== null && $request->subjectId !== 'undefined') {
            $tests->where('t.subject_id', $request->subjectId);
        }

        if ($request->courseId !== null && $request->courseId !== 'undefined') {
            $tests->where('t.course_id', $request->courseId);
        }

        $tests = $tests->get();

        return $this->sendResponse(['tests' => $tests]);
        // } else {
        //     return $this->sendAuthError("Not authorized fetch tests list.");
        // }
    }


    public function showTestResults(Request $request, $testId)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            $results = DB::table('students as s')
                ->select('r.*', 's.name as student_name', 's.section_id')
                ->leftJoin('test_results as r', 'r.student_id', 's.id')
                ->where('r.test_id', $testId)
                ->orderBy('s.name', 'asc')
                ->get();
            return $this->sendResponse(['results' => $results], '');
        } else {
            return $this->sendAuthError("Not authorized fetch  test results.");
        }
    }

    public function storeTestDetails1(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'testTitle' => 'required',
                // 'testTerm' => 'required',
                'duration' => 'required',
                'selectedQuestions' => 'required',
                'selectedSubject' => 'required|string|max:255',
                'selectedCourse' => 'required|string|max:255',
                'instruction' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $test = new Test;
            $test->title = $request->testTitle;
            $test->subject_id = $request->selectedSubject;
            $test->course_id = $request->selectedCourse;
            // $test->term_type = $request->testTerm;
            $test->description = $request->description;
            $test->question_ids = implode(',', $request->selectedQuestions);
            $test->no_of_questions = $request->no_of_questions;
            $test->total_score = $request->totalMarks;
            $test->time_limit = $request->duration;
            $test->description = $request->description;
            $test->start_date = $request->start_date;
            $test->end_date = $request->end_date;
            $test->instruction = $request->instruction;

            if (!empty($request->file('image'))) {
                $extension1 = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension1;
                $test->image = $request->file('image')->move(('uploads/images/test'), $filename);
            } else {
                $test->image = null;
            }
            $test->save();
            return $this->sendResponse(['test' => $test],  "Test created successfully!");
        }
    }

    public function storeTestDetails(Request $request)
    {
        Log::info(['test' => $request->all()]);
        $validator = Validator::make(
            $request->all(),
            [
                'testTitle' => 'required',
                'duration' => 'required',
                'selectedQuestions' => 'required',
                'selectedSubject' => 'required|string|max:255',
                'selectedCourse' => 'required|string|max:255',
                'instruction' => 'required|string',
                // 'status' => 'required|boolean', // Add validation for status
            ]
        );

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // Check if there is an existing active  test for the selected course
        $existingActiveTest = Test::where('course_id', $request->selectedCourse)
            ->where('status', 1)
            ->first();

        if ($existingActiveTest) {
            // return $this->sendError('Already test assigned with same course, need to disable the test first.', [], 404);
            return $this->sendResponse([], "Test created Already test assigned with same course, need to disable the test first.!", false);
        }

        $test = new Test;
        $test->title = $request->testTitle;
        $test->subject_id = $request->selectedSubject;
        $test->course_id = $request->selectedCourse;
        $test->description = $request->description;
        $test->question_ids = implode(',', $request->selectedQuestions);
        $test->no_of_questions = $request->no_of_questions;
        $test->total_score = $request->totalMarks;
        $test->time_limit = $request->duration;
        $test->description = $request->description;
        $test->start_date = $request->start_date;
        $test->end_date = $request->end_date;
        $test->instruction = $request->instruction;
        // $test->status = $request->status; // Save the status

        if (!empty($request->file('image'))) {
            $extension1 = $request->file('image')->extension();
            $filename = Str::random(4) . time() . '.' . $extension1;
            $test->image = $request->file('image')->move(('uploads/images/test'), $filename);
        } else {
            $test->image = null;
        }

        $test->save();

        return $this->sendResponse(['test' => $test], "Test created successfully!");
    }


    public function getTestDetails(Request $request, $testId)
    {
        $result = DB::table('test_results as r')
            ->leftJoin('students as s', 's.id', '=', 'r.student_id')
            ->where('s.auth_id', $this->getLoggedUserId())
            ->where('r.test_id', $testId)
            ->first();

        if ($result) {
            return $this->sendResponse([], "Test Taken Successfully!");
        }

        $test = DB::table('tests as a')
            ->select('a.id', 'a.subject_id', 'a.course_id', 'a.title', 'a.description', 'a.total_score', 'a.time_limit', 'a.no_of_questions', 's.name as subject', 'cou.name as course', 'a.question_ids', 'a.instruction', 'a.status')
            ->leftJoin('subjects as s', 's.id', '=', 'a.subject_id')
            ->leftJoin('courses as cou', 'cou.id', '=', 'a.course_id')
            ->where('a.id', $testId)
            ->first();

        if ($test && $test->question_ids) {
            $questionIds = explode(',', $test->question_ids);

            $test->questions = DB::table('test_questions')
                ->whereIn('id', $questionIds)
                ->get();
        }

        return $this->sendResponse(['test' => $test]);
    }
    //new test method to retirve the test details with the token
    //update the token status also so that no one can take the test again

    public function getTestDetailsByToken(Request $request, $token, $testId)
    {
        // Find the test result entry using the token
        $testResult = DB::table('test_results')
            ->where('token', $token)
            ->where('test_id', $testId)
            ->first();

        // Check if the test result does not exist, which indicates an invalid token or test ID
        if (!$testResult) {
            return $this->sendError("Test or token not found", [], 404); // Use HTTP 404 Not Found for resource not found
        }

        // Check if the test has already been taken
        if ($testResult->token_status == 1) {
            return $this->sendResponse([], "Test has already been taken", false);
        }

        // Update the status to 1 to mark as taken
        DB::table('test_results')
            ->where('id', $testResult->id)
            ->update(['token_status' => 1]);

        // Fetch the  test details
        $test = DB::table('tests as a')
            ->select('a.id', 'a.subject_id', 'a.course_id', 'a.title', 'a.description', 'a.total_score', 'a.time_limit', 'a.no_of_questions', 's.name as subject', 'cou.name as course', 'a.question_ids')
            ->leftJoin('subjects as s', 's.id', '=', 'a.subject_id')
            ->leftJoin('courses as cou', 'cou.id', '=', 'a.course_id')
            ->where('a.id', $testId)
            ->first();

        if ($test && $test->question_ids) {
            $questionIds = explode(',', $test->question_ids);
            $test->questions = DB::table('test_questions')
                ->whereIn('id', $questionIds)
                ->get();
        }

        // Return the test details along with the test_result_id
        return $this->sendResponse(['test' => $test, 'test_result_id' => $testResult->id], "Test details retrieved successfully", true);
    }


    public function getTestResuts($test_id)
    {
        $result = TestResult::with('test', 'user')->where('test_id', $test_id)->get();
        return $result;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Test  $test
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTestDetails(Request $request, $testId)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'testTitle' => 'required',
                'duration' => 'required',
                'selectedQuestions' => 'required',
                'selectedSubject' => 'required|max:255',
                'selectedCourse' => 'required|max:255',
                'instruction' => 'required',
                'status' => 'required',
            ]
        );

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $test = Test::find($testId);

        if (!$test) {
            return $this->sendError('Test not found');
        }

        // Check if there is another active  test for the selected course
        $existingActiveTest = Test::where('course_id', $request->selectedCourse)
            ->where('status', 1)
            ->where('id', '!=', $testId)
            ->where('status', 1) // Exclude the current test being updated
            ->first();

        if ($existingActiveTest) {
            // return $this->sendError('Another active test already assigned with the same course, need to disable the other test first.', [], 400);
            return $this->sendResponse([], "Another active test already assigned with the same course, need to disable the other test first.", false);
        }

        $test->title = $request->testTitle;
        $test->subject_id = $request->selectedSubject;
        $test->course_id = $request->selectedCourse;
        $test->description = $request->description;
        $test->question_ids = implode(',', $request->selectedQuestions);
        $test->no_of_questions = $request->numberOfQuestions;
        $test->total_score = $request->totalMarks;
        $test->time_limit = $request->duration;
        $test->start_date = $request->start_date;
        $test->end_date = $request->end_date;
        $test->description = $request->description;
        $test->instruction = $request->instruction;
        $test->status = $request->status;

        if (!empty($request->file('image'))) {
            if ($test->image) {
                Storage::delete($test->image);
            }
            $extension1 = $request->file('image')->extension();
            $filename = Str::random(4) . time() . '.' . $extension1;
            $test->image = $request->file('image')->move(('uploads/images/test'), $filename);
        }

        $test->save();

        return $this->sendResponse(['test' => $test], "Test updated successfully!");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Test  $test
     * @return \Illuminate\Http\Response
     */
    public function destroyTestDetails(Request $request, $testId)
    {

        $validator = Validator::make(
            array_merge($request->all(), ['test_id' => $testId]),
            [
                'test_id' => 'required',
            ]
        );
        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $test = Test::find($testId);

            $test->delete();
        }

        return $this->sendResponse([], 'Test deleted successfully');
    }

    public function storeTestResponse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'studentId' => 'required',
            'schoolId' => 'required',
            'testId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            //Get Test and Test Questions Data
            $test = Test::find($request->testId);
            $test_questions = explode(',', $test->question_ids);
            $test_question_answers = TestQuestion::whereIn('id', $test_questions)->get();

            //Process Student Answers
            $selectedQuestionIds = explode(',', $request->selectedQuestionIds);
            $selectedAnswers = explode(',', $request->selectedAnswers);

            $studentResponse = array_map(function ($q, $a) {
                return ['qns' => $q, 'ans' => $a];
            }, $selectedQuestionIds, $selectedAnswers);

            $score = 0;

            //Calculating the score
            foreach ($test_question_answers as $question) {
                foreach ($studentResponse as $response) {
                    if ($question->id == $response['qns']) {
                        if ($question->answer_key == $response['ans']) {
                            $score = $score + 1;
                        }
                    }
                }
            }

            //Calculating the percentage
            $totalScore = count($test_questions) * 1;

            if ($score !== 0) {
                $score_percentage = ($score / $totalScore) * 100;
            } else {
                $score_percentage = 0;
            }

            $test_result = new TestResult;
            $test_result->test_id = $request->testId;
            $test_result->student_id  = $request->studentId;
            // $test_result->course_id  = $request->course_id;
            $test_result->school_id = $request->schoolId;
            $test_result->score = $score;
            $test_result->percentage = $score_percentage;
            $selectedAnswersArray = is_array($request->selectedAnswers) ? $request->selectedAnswers : explode(',', $request->selectedAnswers);
            $test_result->response_answers = implode(',', $selectedAnswersArray);
            $test_result->response_questions = implode(',', $selectedQuestionIds);
            $test_result->save();

            return $this->sendResponse(['test_result' => $test_result], 'Test completed successfully');
        }
    }

    public function storeTestResponseWithToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'studentId' => 'required',
            'schoolId' => 'required',
            'testId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            //Get Test and Test Questions Data
            $testResult = TestResult::where('token', $request->token)->first();

            if (!$testResult) {
                return $this->sendError('Test result not found or token is invalid.', [], 404);
            }
            $test = Test::find($request->testId);
            $test_questions = explode(',', $test->question_ids);
            $test_question_answers = TestQuestion::whereIn('id', $test_questions)->get();

            //Process Student Answers
            $selectedQuestionIds = explode(',', $request->selectedQuestionIds);
            $selectedAnswers = explode(',', $request->selectedAnswers);

            $studentResponse = array_map(function ($q, $a) {
                return ['qns' => $q, 'ans' => $a];
            }, $selectedQuestionIds, $selectedAnswers);

            $score = 0;

            //Calculating the score
            foreach ($test_question_answers as $question) {
                foreach ($studentResponse as $response) {
                    if ($question->id == $response['qns']) {
                        if ($question->answer_key == $response['ans']) {
                            $score = $score + 1;
                        }
                    }
                }
            }


            //Calculating the percentage
            $totalScore = count($test_questions) * 1;

            if ($score !== 0) {
                $score_percentage = ($score / $totalScore) * 100;
            } else {
                $score_percentage = 0;
            }

            // $test_result = new TestResult;
            // $test_result->test_id = $request->testId;
            // $test_result->student_id  = $request->studentId;
            // $test_result->school_id = $request->schoolId;
            // $test_result->score = $score;
            // $test_result->percentage = $score_percentage;
            // $selectedAnswersArray = is_array($request->selectedAnswers) ? $request->selectedAnswers : explode(',', $request->selectedAnswers);
            // $test_result->response_answers = implode(',', $selectedAnswersArray);
            // $test_result->response_questions = implode(',', $selectedQuestionIds);
            // $test_result->save();


            $testResult->score = $score;
            $testResult->percentage = $score_percentage;
            $testResult->course_id = $request->courseId;
            $testResult->subject_id = $request->subjectId;

            $testResult->response_answers = implode(',', $selectedAnswers);
            $testResult->response_questions = implode(',', $selectedQuestionIds);
            $testResult->is_completed = true; // Assuming there's a field to mark completion
            $testResult->save();


            return $this->sendResponse(['test_result' => $testResult], 'Test completed successfully');
        }
    }

    /**
     * Check if  test is created for that course
     *
     * @param  \App\Models\Test  $test
     * @return \Illuminate\Http\Response
     */
    // public function checkTermAvailability($courseId)
    // {
    //     $terms = Test::where('course_id', $courseId)
    //         ->pluck('term_type')
    //         ->toArray();

    //     return $this->sendResponse(['terms' => $terms]);
    // }
    public function startTest(Request $request)
    {
        $data = $request->validate([
            'studentId' => 'required|integer',
            'schoolId' => 'required|integer',
            'courseId' => 'required|integer',
            'latestTestId' => 'required|integer',
        ]);

        // Check if the test session already exists to prevent duplicates


        $existingSession = TestResult::where('student_id', $data['studentId'])
            ->where('test_id', $data['latestTestId'])
            ->first();

        if ($existingSession) {
            return response()->json(['message' => 'Test already taken'], 409); // 409 Conflict
        }
        $token = Str::random(32);
        // Create a new test session
        $testSession = TestResult::create([
            'student_id' => $data['studentId'],
            'school_id' => $data['schoolId'],
            // 'course_id' => $data['courseId'],
            'test_id' => $data['latestTestId'],
            // Set additional fields as necessary
            'token' => $token,
        ]);

        return $this->sendResponse(['token' => $token, 'status' => 200], 200);
    }
}
