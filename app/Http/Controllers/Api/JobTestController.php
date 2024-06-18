<?php

namespace App\Http\Controllers\Api;

use App\Models\JobTest;
use App\Models\JobQuestion;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Models\JobTestResult;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class JobTestController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllTermTests(Request $request)
    {
        $term_tests = DB::table('job_tests as t')
            ->select('t.*', 'c.name as class_name')
            ->leftJoin('classes as c', 'c.id', '=', DB::raw("substring_index(t.class_id, ',', 1)")); // Adjust if needed based on how class names are stored or matched

        if (!empty($request->classId) && $request->classId !== 'undefined') {
            // Use FIND_IN_SET to look for a specific class ID within the comma-separated list
            $term_tests->whereRaw("FIND_IN_SET(?, t.class_id)", [$request->classId]);
        }

        $term_tests = $term_tests->get();

        return $this->sendResponse(['term_tests' => $term_tests]);
    }



    public function showTermTestResults(Request $request, $testId)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            $results = DB::table('students as s')
                ->select('r.*', 's.name as student_name', 's.section_id')
                ->leftJoin('term_test_results as r', 'r.student_id', 's.id')
                ->where('r.test_id', $testId)
                ->orderBy('s.name', 'asc')
                ->get();
            return $this->sendResponse(['results' => $results], '');
        } else {
            return $this->sendAuthError("Not authorized fetch term test results.");
        }
    }

    public function storeTermTestDetails(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'testTitle' => 'required',
                'duration' => 'required',
                'selectedQuestions' => 'required',
                'selectedClass' => 'required|string|max:255',
                'instruction' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $test = new JobTest;
            $test->title = $request->testTitle;
            $test->class_id = $request->selectedClass;
            $test->description = $request->description;
            $test->question_ids = implode(',', $request->selectedQuestions);
            $test->no_of_questions = count($request->selectedQuestions);
            // $test->no_of_questions = $request->no_of_questions;
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

    public function getTermTestDetails(Request $request, $testId)
    {
        $term_test = DB::table('job_tests as a')
            ->select('a.id', 'a.class_id', 'a.title', 'a.description', 'a.total_score', 'a.time_limit', 'a.no_of_questions',  'a.question_ids', 'a.instruction')
            ->where('a.id', $testId)
            ->first();

        if ($term_test && $term_test->question_ids) {
            $questionIds = explode(',', $term_test->question_ids);
            $term_test->questions = DB::table('job_questions')
                ->whereIn('id', $questionIds)
                ->get();
        }
        return $this->sendResponse(['term_test' => $term_test]);
    }

    //new test method to retirve the test details with the token
    //update the token status also so that no one can take the test again

    public function getTermTestDetailsByToken(Request $request, $token, $testId)
    {
        // Find the test result entry using the token
        $testResult = DB::table('term_test_results')
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
        DB::table('term_test_results')
            ->where('id', $testResult->id)
            ->update(['token_status' => 1]);

        // Fetch the term test details
        $term_test = DB::table('term_tests as a')
            ->select('a.id', 'a.class_id', 'a.subject_id', 'a.title', 'a.term_type', 'a.description', 'a.total_score', 'a.time_limit', 'a.no_of_questions', 'c.name as class', 's.name as subject', 'a.question_ids')
            ->leftJoin('classes as c', 'c.id', '=', 'a.class_id')
            ->leftJoin('subjects as s', 's.id', '=', 'a.subject_id')
            ->where('a.id', $testId)
            ->first();

        if ($term_test && $term_test->question_ids) {
            $questionIds = explode(',', $term_test->question_ids);
            $term_test->questions = DB::table('term_test_questions')
                ->whereIn('id', $questionIds)
                ->get();
        }

        // Return the test details along with the test_result_id
        return $this->sendResponse(['term_test' => $term_test, 'test_result_id' => $testResult->id], "Test details retrieved successfully", true);
    }


    public function getTermTestResuts($test_id)
    {
        $result = JobTestResult::with('test', 'user')->where('test_id', $test_id)->get();
        return $result;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TermTest  $termTest
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTermTestDetails(Request $request, $testId)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'testTitle' => 'required',
                'duration' => 'required',
                'selectedQuestions' => 'required',
                'selectedClass' => 'required|max:255',
                'instruction' => 'required',
            ]
        );

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $test = JobTest::find($testId);

            if (!$test) {
                return $this->sendError('Test not found');
            }

            $test->title = $request->testTitle;
            $test->class_id = $request->selectedClass;

            // $test->term_type = $request->testTerm;
            $test->description = $request->description;
            $test->question_ids = implode(',', $request->selectedQuestions);
            $test->no_of_questions = count($request->selectedQuestions);
            // $test->no_of_questions = $request->numberOfQuestions;
            $test->total_score = $request->totalMarks;
            $test->time_limit = $request->duration;
            $test->start_date = $request->start_date;
            $test->end_date = $request->end_date;
            $test->description = $request->description;
            $test->instruction = $request->instruction;
            $test->class_id = $request->selectedClass;

            if (!empty($request->file('image'))) {
                if ($test->image) {
                    Storage::delete($test->image);
                }
                $extension1 = $request->file('image')->extension();
                $filename = Str::random(4) . time() . '.' . $extension1;
                $test->image = $request->file('image')->move(('uploads/images/test'), $filename);
            }

            $test->save();
            return $this->sendResponse(['test' => $test],  "Test updated successfully!");
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TermTest  $termTest
     * @return \Illuminate\Http\Response
     */
    public function destroyTermTestDetails(Request $request, $testId)
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
            $test = JobTest::find($testId);

            $test->delete();
        }

        return $this->sendResponse([], 'Test deleted successfully');
    }


    public function storeTermTestResponse(Request $request)
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
            $test = JobTest::find($request->testId);
            $test_questions = explode(',', $test->question_ids);
            $test_question_answers = JobQuestion::whereIn('id', $test_questions)->get();

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

            $test_result = new JobTestResult;
            $test_result->test_id = $request->testId;
            $test_result->student_id  = $request->studentId;
            // $test_result->subject_id  = $request->subject_id;
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
    public function storeJobTestResponseWithToken(Request $request)
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
            $testResult = JobApplication::where('token', $request->token)->first();

            if (!$testResult) {
                return $this->sendError('Test result not found or token is invalid.', [], 404);
            }
            $test = JobTest::find($request->testId);
            $test_questions = explode(',', $test->question_ids);
            $test_question_answers = JobQuestion::whereIn('id', $test_questions)->get();

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

            // Log::info("request", $request->all());

            //Calculating the percentage
            $totalScore = count($test_questions) * 1;

            if ($score !== 0) {
                $score_percentage = ($score / $totalScore) * 100;
            } else {
                $score_percentage = 0;
            }
            if ($score_percentage >= $request->passingPercentage) {
                // log::info("percentage",$score_percentage, $request->passingPercentage);
                $testResult->is_pass = true;
            }
            $testResult->score = $score;
            $testResult->percentage = $score_percentage;
            $testResult->response_answers = implode(',', $selectedAnswers);
            $testResult->response_questions = implode(',', $selectedQuestionIds);
            $testResult->is_completed = true; // Assuming there's a field to mark completion
            $testResult->save();
            // Log::info("request", $testResult);


            return $this->sendResponse(['test_result' => $testResult], 'Test completed successfully');
        }
    }
    public function storeJobTestResponseWithoutToken(Request $request)
    {
        Log::info("jobs without token", $request->all());
        $validator = Validator::make($request->all(), [
            'studentId' => 'required',
            'schoolId' => 'required',
            'testId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            //Get Test and Test Questions Data
            $testResult = JobApplication::where('token', $request->token)->first();

            if (!$testResult) {
                return $this->sendError('Test result not found or token is invalid.', [], 404);
            }
            $test = JobTest::find($request->testId);
            $test_questions = explode(',', $test->question_ids);
            $test_question_answers = JobQuestion::whereIn('id', $test_questions)->get();

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

            // Log::info("request", $request->all());

            //Calculating the percentage
            $totalScore = count($test_questions) * 1;

            if ($score !== 0) {
                $score_percentage = ($score / $totalScore) * 100;
            } else {
                $score_percentage = 0;
            }
            if ($score_percentage >= $request->passingPercentage) {
                // log::info("percentage",$score_percentage, $request->passingPercentage);
                $testResult->is_pass = true;
            }
            $testResult->score = $score;
            $testResult->percentage = $score_percentage;
            $testResult->response_answers = implode(',', $selectedAnswers);
            $testResult->response_questions = implode(',', $selectedQuestionIds);
            $testResult->is_completed = true; // Assuming there's a field to mark completion
            $testResult->save();
            // Log::info("request", $testResult);


            return $this->sendResponse(['test_result' => $testResult], 'Test completed successfully');
        }
    }

    /**
     * Check if term test is created for that subject
     *
     * @param  \App\Models\TermTest  $termTest
     * @return \Illuminate\Http\Response
     */
    public function checkTermAvailability($subjectId)
    {
        $terms = JobTest::where('subject_id', $subjectId)
            ->pluck('term_type')
            ->toArray();

        return $this->sendResponse(['terms' => $terms]);
    }
    public function startTest(Request $request)
    {
        $data = $request->validate([
            'studentId' => 'required|integer',
            'schoolId' => 'required|integer',
            'subjectId' => 'required|integer',
            'latestTestId' => 'required|integer',
        ]);

        // Check if the test session already exists to prevent duplicates


        $existingSession = JobTestResult::where('student_id', $data['studentId'])
            ->where('test_id', $data['latestTestId'])
            ->first();

        if ($existingSession) {
            return response()->json(['message' => 'Test already taken'], 409); // 409 Conflict
        }
        $token = Str::random(32);
        // Create a new test session
        $testSession = JobTestResult::create([
            'student_id' => $data['studentId'],
            'school_id' => $data['schoolId'],
            // 'subject_id' => $data['subjectId'],
            'test_id' => $data['latestTestId'],
            // Set additional fields as necessary
            'token' => $token,
        ]);

        return $this->sendResponse(['token' => $token, 'status' => 200], 200);
    }
}
