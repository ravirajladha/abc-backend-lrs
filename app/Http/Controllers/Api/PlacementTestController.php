<?php

namespace App\Http\Controllers\Api;

use App\Models\PlacementTest;
use App\Models\PlacementQuestion;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Models\PlacementTestResult;
use App\Models\PlacementApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PlacementTestController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllTests(Request $request)
    {
        $tests = DB::table('placement_tests as t')
            ->select('t.*', 's.name as subject_name')
            ->leftJoin('subjects as s', 's.id', '=', DB::raw("substring_index(t.subject_id, ',', 1)")); // Adjust if needed based on how class names are stored or matched

        if (!empty($request->subjectId) && $request->subjectId !== 'undefined') {
            // Use FIND_IN_SET to look for a specific subjectId within the comma-separated list
            $tests->whereRaw("FIND_IN_SET(?, t.subject_id)", [$request->subjectId]);
        }

        $tests = $tests->get();

        return $this->sendResponse(['tests' => $tests]);
    }



    public function showTestResults(Request $request, $testId)
    {
        $userType = $request->attributes->get('type');
        if ($userType === 'admin') {
            $results = DB::table('students as s')
                ->select('r.*', 's.name as student_name')
                ->leftJoin('test_results as r', 'r.student_id', 's.id')
                ->where('r.test_id', $testId)
                ->orderBy('s.name', 'asc')
                ->get();
            return $this->sendResponse(['results' => $results], '');
        } else {
            return $this->sendAuthError("Not authorized fetch test results.");
        }
    }

    public function storeTestDetails(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'testTitle' => 'required',
                'duration' => 'required',
                'selectedQuestions' => 'required',
                'selectedSubject' => 'required|string|max:255',
                'instruction' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $loggedUserId = $this->getLoggedUserId();
        
            $test = new PlacementTest;
            $test->title = $request->testTitle;
            $test->subject_id = $request->selectedSubject;
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
            $test->created_by = $loggedUserId;
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

    public function getTestDetails(Request $request, $testId)
    {
        $test = DB::table('placement_tests as a')
            ->select('a.id', 'a.subject_id', 'a.title', 'a.description', 'a.total_score', 'a.time_limit', 'a.no_of_questions',  'a.question_ids', 'a.instruction')
            ->where('a.id', $testId)
            ->first();

        if ($test && $test->question_ids) {
            $questionIds = explode(',', $test->question_ids);
            $test->questions = DB::table('placement_questions')
                ->whereIn('id', $questionIds)
                ->get();
        }
        
        return $this->sendResponse(['test' => $test]);
    }

    //new test method to retrieve the test details with the token
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
            ->select('a.id', 'a.course_id', 'a.subject_id', 'a.title',  'a.description', 'a.total_score', 'a.time_limit', 'a.no_of_questions', 'c.name as course', 's.name as subject', 'a.question_ids')
            ->leftJoin('courses as c', 'c.id', '=', 'a.course_id')
            ->leftJoin('subjects as s', 's.id', '=', 'a.subject_id')
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


    public function getTestResults($test_id)
    {
        $result = PlacementTestResult::with('test', 'user')->where('test_id', $test_id)->get();
        return $result;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Test  $Test
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
                'instruction' => 'required',
            ]
        );

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            $test = PlacementTest::find($testId);

            if (!$test) {
                return $this->sendError('Test not found');
            }
            $loggedUserId = $this->getLoggedUserId();
         
    
            $test->title = $request->testTitle;
            $test->subject_id = $request->selectedSubject;

        
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
            // $test->class_id = $request->selectedClass;
            $test->updated_by = $loggedUserId;
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
     * @param  \App\Models\Test  $Test
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
            $test = PlacementTest::find($testId);

            $test->delete();
        }

        return $this->sendResponse([], 'Test deleted successfully');
    }


    public function storeTestResponse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'studentId' => 'required',
     
            'testId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            //Get Test and Test Questions Data
            $test = PlacementTest::find($request->testId);
            $test_questions = explode(',', $test->question_ids);
            $test_question_answers = PlacementQuestion::whereIn('id', $test_questions)->get();

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

            $test_result = new PlacementTestResult;
            $test_result->test_id = $request->testId;
            $test_result->student_id  = $request->studentId;
            // $test_result->subject_id  = $request->subject_id;
          
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
            'testId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            //Get Test and Test Questions Data
            $testResult = PlacementApplication::where('token', $request->token)->first();

            if (!$testResult) {
                return $this->sendError('Test result not found or token is invalid.', [], 404);
            }
            $test = PlacementTest::find($request->testId);
            $test_questions = explode(',', $test->question_ids);
            $test_question_answers = PlacementQuestion::whereIn('id', $test_questions)->get();

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
            'testId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        } else {
            //Get Test and Test Questions Data
            $testResult = PlacementApplication::where('token', $request->token)->first();

            if (!$testResult) {
                return $this->sendError('Test result not found or token is invalid.', [], 404);
            }
            $test = PlacementTest::find($request->testId);
            $test_questions = explode(',', $test->question_ids);
            $test_question_answers = PlacementQuestion::whereIn('id', $test_questions)->get();

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
     * Check if  test is created for that subject
     *
     * @param  \App\Models\Test  $Test
     * @return \Illuminate\Http\Response
     */

    public function startTest(Request $request)
    {
        $data = $request->validate([
            'studentId' => 'required|integer',
            'subjectId' => 'required|integer',
            'latestTestId' => 'required|integer',
        ]);

        // Check if the test session already exists to prevent duplicates


        $existingSession = PlacementTestResult::where('student_id', $data['studentId'])
            ->where('test_id', $data['latestTestId'])
            ->first();

        if ($existingSession) {
            return response()->json(['message' => 'Test already taken'], 409); // 409 Conflict
        }
        $token = Str::random(32);
        // Create a new test session
        $testSession = PlacementTestResult::create([
            'student_id' => $data['studentId'],
            // 'subject_id' => $data['subjectId'],
            'test_id' => $data['latestTestId'],
            // Set additional fields as necessary
            'token' => $token,
        ]);

        return $this->sendResponse(['token' => $token, 'status' => 200], 200);
    }
}
